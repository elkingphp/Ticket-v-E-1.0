<?php

namespace Modules\Core\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Domain\Interfaces\ModuleManagerInterface;
use Modules\Core\Domain\Models\Module;

class ERMOController extends Controller
{
    protected ModuleManagerInterface $moduleManager;

    public function __construct(ModuleManagerInterface $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * GET /api/v1/ermo/events
     * Recent module events.
     */
    public function events(): JsonResponse
    {
        $events = \Modules\Core\Domain\Models\AuditLog::where('category', 'Modules')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($log) => [
                'event' => str_replace('module.', '', $log->event),
                'details' => $log->new_values['details'] ?? [],
                'module' => $log->new_values['slug'] ?? 'Unknown',
                'time' => $log->created_at->diffForHumans(),
                'level' => $log->log_level
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $events
        ]);
    }

    /**
     * GET /api/v1/ermo/registry
     * List all modules and their current states.
     */
    public function registry(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->moduleManager->getSortedModules()
        ]);
    }

    /**
     * GET /api/v1/ermo/metrics
     * Unified operational metrics endpoint.
     */
    public function metrics(): JsonResponse
    {
        $modules = Module::all();
        $metrics = $modules->mapWithKeys(function ($m) {
            return [
                $m->slug => [
                    'status' => $m->status,
                    'active_requests' => (int) \Illuminate\Support\Facades\Cache::get("ermo:active_requests:{$m->slug}", 0),
                    'state_version' => $m->state_version,
                    'last_transition_at' => $m->updated_at?->toIso8601String(),
                    'health_status' => $m->health_status,
                    'failure_count' => (int) \Illuminate\Support\Facades\Cache::get("ermo:health_failures:{$m->slug}", 0),
                    'is_core' => $m->is_core
                ]
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $metrics
        ]);
    }

    /**
     * GET /api/v1/ermo/prometheus-metrics
     * Prometheus/OpenMetrics compatible export.
     */
    public function prometheus(): \Illuminate\Http\Response
    {
        $modules = Module::all();
        $output = "# HELP ermo_module_active_requests Number of current active requests per module\n";
        $output .= "# TYPE ermo_module_active_requests gauge\n";

        $stateOutput = "# HELP ermo_module_state Current status (0=disabled, 1=active, 2=maintenance, 3=degraded)\n";
        $stateOutput .= "# TYPE ermo_module_state gauge\n";

        // SLA & Performance Metrics
        $performanceOutput = "# HELP ermo_module_latency_ms Average processing latency in ms\n";
        $performanceOutput .= "# TYPE ermo_module_latency_ms gauge\n";

        $performanceOutput .= "# HELP ermo_module_uptime_ratio Historical uptime percentage (SLA tracking)\n";
        $performanceOutput .= "# TYPE ermo_module_uptime_ratio gauge\n";

        $performanceOutput .= "# HELP ermo_module_degradation_frequency_total Total historical degradation events\n";
        $performanceOutput .= "# TYPE ermo_module_degradation_frequency_total counter\n";

        $performanceOutput .= "# HELP ermo_module_requests_total Total requests processed by the module\n";
        $performanceOutput .= "# TYPE ermo_module_requests_total counter\n";

        // Emergency Metrics
        $bypass = config('ermo.emergency_bypass') ? 1 : 0;
        $redisDegraded = \Illuminate\Support\Facades\Cache::store('file')->has('ermo:redis_degraded') ? 1 : 0;

        $stateOutput .= "ermo_emergency_mode_active {$bypass}\n";
        $stateOutput .= "ermo_redis_degraded_active {$redisDegraded}\n";

        foreach ($modules as $m) {
            $slug = $m->slug;
            $prefix = config('cache.prefix', 'laravel_cache');

            $activeRequests = 0;
            try {
                $activeRequests = (int) \Illuminate\Support\Facades\Redis::get("{$prefix}:ermo:active_requests:{$slug}");
            } catch (\Exception $e) {
                // Fallback to 0 if Redis is down
            }

            $stateMap = ['disabled' => 0, 'active' => 1, 'maintenance' => 2, 'degraded' => 3];

            $state = $stateMap[$m->status] ?? 0;

            $health = $m->health_status === 'healthy' ? 1 : 0;
            $failures = (int) \Illuminate\Support\Facades\Cache::get("ermo:health_failures:{$slug}", 0);
            $max = $m->max_concurrent_requests;
            $saturation = $max > 0 ? ($activeRequests / $max) : 0;

            $output .= "ermo_module_active_requests{module=\"{$slug}\"} {$activeRequests}\n";
            $stateOutput .= "ermo_module_state{module=\"{$slug}\"} {$state}\n";
            $stateOutput .= "ermo_module_health_status{module=\"{$slug}\"} {$health}\n";
            $stateOutput .= "ermo_module_failures_total{module=\"{$slug}\"} {$failures}\n";
            $stateOutput .= "ermo_module_max_concurrent{module=\"{$slug}\"} {$max}\n";
            $stateOutput .= "ermo_module_saturation_ratio{module=\"{$slug}\"} {$saturation}\n";

            // Observability Data
            $uptime = $m->getUptimePercentage();
            $latency = $m->getAvgLatency();

            $performanceOutput .= "ermo_module_latency_ms{module=\"{$slug}\"} {$latency}\n";
            $performanceOutput .= "ermo_module_uptime_ratio{module=\"{$slug}\"} {$uptime}\n";
            $performanceOutput .= "ermo_module_degradation_frequency_total{module=\"{$slug}\"} {$m->degradation_count}\n";
            $performanceOutput .= "ermo_module_requests_total{module=\"{$slug}\"} {$m->total_requests}\n";
        }

        $trips = (int) \Illuminate\Support\Facades\Cache::get('ermo:metrics:circuit_trips_total', 0);
        $stateOutput .= "ermo_circuit_trip_total {$trips}\n";

        return response($output . $stateOutput . $performanceOutput, 200)->header('Content-Type', 'text/plain; version=0.0.4');
    }

    /**
     * GET /api/v1/ermo/graph
     * Get dependency graph.
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
     * POST /api/v1/ermo/transition
     * Transition a module state (Sudo Protected via middleware).
     */
    public function transition(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => 'required|string',
            'status' => 'required|string|in:registered,installed,active,degraded,maintenance,disabled',
            'reason' => 'nullable|string'
        ]);

        try {
            $this->moduleManager->transitionState(
                $request->slug,
                $request->status,
                $request->reason ?? 'API Request'
            );

            return response()->json([
                'status' => 'success',
                'message' => "Module {$request->slug} transitioned to {$request->status}."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * POST /api/v1/ermo/sync
     * Sync filesystem.
     */
    public function sync(): JsonResponse
    {
        $this->moduleManager->syncFromFilesystem();
        return response()->json(['status' => 'success', 'message' => 'Filesystem synced.']);
    }

    /**
     * GET /api/v1/ermo/suggestions
     * Real-time scaling and recovery guidance.
     */
    public function suggestions(): JsonResponse
    {
        $modules = Module::all();
        $suggestions = [];

        foreach ($modules as $m) {
            $slug = $m->slug;
            $active = (int) \Illuminate\Support\Facades\Cache::get("ermo:active_requests:{$slug}", 0);
            $max = $m->max_concurrent_requests;
            $saturation = $max > 0 ? ($active / $max) : 0;
            $avgLatency = $m->getAvgLatency();

            if ($saturation > 0.8) {
                $suggestions[] = [
                    'module' => $slug,
                    'type' => 'scale_up',
                    'priority' => 'high',
                    'message' => "Module {$slug} is at " . round($saturation * 100) . "% capacity. Consider increasing max_concurrent_requests.",
                    'action' => 'increase_capacity'
                ];
            }

            if ($m->status === 'degraded' || $m->status === 'maintenance') {
                $failures = (int) \Illuminate\Support\Facades\Cache::get("ermo:health_failures:{$slug}", 0);
                if ($failures === 0 && $m->health_status === 'healthy') {
                    $suggestions[] = [
                        'module' => $slug,
                        'type' => 'recovery',
                        'priority' => 'medium',
                        'message' => "Module {$slug} appears healthy. Safe to attempt reactivation.",
                        'action' => 'reactivate'
                    ];
                }
            }

            if ($avgLatency > 500) {
                $suggestions[] = [
                    'module' => $slug,
                    'type' => 'optimize',
                    'priority' => 'medium',
                    'message' => "Module {$slug} average latency is high ({$avgLatency}ms). Check performance or downstream dependencies.",
                    'action' => 'inspect_performance'
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $suggestions
        ]);
    }
}