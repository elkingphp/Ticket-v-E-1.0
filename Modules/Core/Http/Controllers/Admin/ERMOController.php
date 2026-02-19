<?php

namespace Modules\Core\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Domain\Interfaces\ModuleManagerInterface;
use Modules\Core\Domain\Models\Module;
use Modules\Core\Domain\Models\ChaosReport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ERMOController extends Controller
{
    protected ModuleManagerInterface $moduleManager;

    public function __construct(ModuleManagerInterface $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Display the Mission Control Interface (Main Stats).
     */
    public function index(): View
    {
        return view('core::admin.ermo.index');
    }

    /**
     * Display the Module Management Interface (CRUD + Lazy Loading Table).
     */
    public function modules(): View
    {
        $allModules = Module::all(); // For dependency selection in modals
        return view('core::admin.ermo.modules', [
            'availableModules' => $allModules
        ]);
    }

    /**
     * GET /admin/ermo/metrics
     */
    public function metrics(): JsonResponse
    {
        $modules = Module::all();
        $metrics = $modules->map(function ($m) {
            $slug = $m->slug;
            $prefix = config('cache.prefix', 'laravel_cache');
            $activeRequests = (int) Redis::get("{$prefix}:ermo:active_requests:{$slug}") ?? 0;
            
            return [
                'id' => $m->id,
                'slug' => $slug,
                'name' => $m->name,
                'version' => $m->version,
                'status' => $m->status,
                'active_requests' => $activeRequests,
                'max_concurrent' => $m->max_concurrent_requests,
                'health_status' => $m->health_status,
                'last_failure' => Cache::get("ermo:last_failure:{$slug}", 'Never'),
                'uptime' => $m->getUptimePercentage(),
                'latency' => $m->getAvgLatency(),
                'last_reason' => $m->last_transition_reason ?? 'System sync',
                'updated_at' => $m->updated_at?->diffForHumans(),
                'is_core' => $m->is_core,
                'dep_count' => $m->dependencies()->count(),
                'feature_flags' => $m->feature_flags,
            ];
        });

        $emergency = [
            'bypass' => config('ermo.emergency_bypass', false),
            'redis_degraded' => Cache::store('file')->has('ermo:redis_degraded')
        ];

        return response()->json([
            'status' => 'success',
            'data' => $metrics,
            'emergency' => $emergency
        ]);
    }

    /**
     * GET /admin/ermo/graph
     */
    public function graph(): JsonResponse
    {
        $modules = Module::with('dependencies')->get();

        $nodes = $modules->map(fn($m) => [
            'id' => $m->slug,
            'label' => $m->name,
            'status' => $m->status
        ]);

        $edges = [];
        foreach ($modules as $module) {
            foreach ($module->dependencies as $dep) {
                $edges[] = ['from' => $module->slug, 'to' => $dep->slug];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'nodes' => $nodes,
                'edges' => $edges
            ]
        ]);
    }

    /**
     * POST /admin/ermo/transition
     */
    public function transition(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => 'required|string',
            'status' => 'required|string|in:active,degraded,maintenance,disabled',
            'reason' => 'nullable|string'
        ]);

        try {
            $this->moduleManager->transitionState(
                $request->slug,
                $request->status,
                $request->reason ?? 'Admin Dashboard Override'
            );

            return response()->json([
                'status' => 'success',
                'message' => "Module {$request->slug} successfully transitioned to {$request->status}."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * POST /admin/ermo/modules
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:modules,slug|max:255',
            'version' => 'required|string|max:50',
            'max_concurrent_requests' => 'required|integer|min:0',
            'feature_flags' => 'nullable|array',
            'is_core' => 'boolean'
        ]);

        $module = Module::create(array_merge($validated, [
            'status' => 'registered',
            'health_status' => 'healthy'
        ]));

        $this->moduleManager->refreshCache();

        broadcast(new \Modules\Core\Infrastructure\Events\ERMOClusterUpdated('module_created', [
            'slug' => $module->slug,
            'name' => $module->name
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Module created successfully.',
            'data' => $module
        ]);
    }

    /**
     * PUT /admin/ermo/modules/{module}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $module = Module::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'version' => 'string|max:50',
            'max_concurrent_requests' => 'integer|min:0',
            'feature_flags' => 'nullable|array',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:modules,id'
        ]);

        $module->update(collect($validated)->except('dependencies')->toArray());

        if (isset($validated['dependencies'])) {
            $module->dependencies()->sync($validated['dependencies']);
        }

        $this->moduleManager->refreshCache();

        broadcast(new \Modules\Core\Infrastructure\Events\ERMOClusterUpdated('module_updated', [
            'slug' => $module->slug,
            'id' => $id
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Module updated successfully.'
        ]);
    }

    /**
     * DELETE /admin/ermo/modules/{module}
     */
    public function destroy(string $id): JsonResponse
    {
        $module = Module::findOrFail($id);

        if ($module->is_core) {
            return response()->json([
                'status' => 'error',
                'message' => 'CRITICAL: Core modules cannot be deleted via UI.'
            ], 403);
        }

        $slug = $module->slug;
        $module->delete();

        app(\Modules\Core\Application\Services\AuditLoggerService::class)->log(
            "module.deleted",
            "Modules",
            ['slug' => $slug],
            null,
            'critical'
        );

        $this->moduleManager->refreshCache();

        broadcast(new \Modules\Core\Infrastructure\Events\ERMOClusterUpdated('module_deleted', [
            'slug' => $slug
        ]));

        return response()->json([
            'status' => 'success',
            'message' => "Module {$slug} permanently removed from registry."
        ]);
    }

    /**
     * POST /admin/ermo/chaos/simulate
     */
    public function simulateChaos(): JsonResponse
    {
        try {
            Log::info("Admin triggered manual chaos simulation.");
            
            // Execute command
            Artisan::call('ermo:chaos-simulate', ['--type' => 'all']);
            $output = Artisan::output();

            $latestReport = ChaosReport::latest()->first();

            // Log administrative chaos event
            app(\Modules\Core\Application\Services\AuditLoggerService::class)->log(
                "cluster.chaos_injected",
                "Infrastructure",
                [
                    'type' => 'all',
                    'outcome' => $latestReport?->result ?? 'N/A',
                    'report_id' => $latestReport?->id
                ],
                auth()->user(),
                'critical'
            );

            broadcast(new \Modules\Core\Infrastructure\Events\ERMOClusterUpdated('chaos_injected', [
                'outcome' => $latestReport?->result ?? 'N/A',
                'summary' => $latestReport?->summary ?? '',
                'timestamp' => now()->toDateTimeString()
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Chaos simulation completed. Background audit initiated.',
                'data' => [
                    'outcome' => $latestReport?->result ?? 'N/A',
                    'report' => $latestReport?->summary ?? 'Check logs for details.',
                    'timestamp' => now()->toDateTimeString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Simulation failed to initiate: ' . $e->getMessage()
            ], 500);
        }
    }
}