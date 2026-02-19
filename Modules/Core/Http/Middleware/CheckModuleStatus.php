<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Application\Services\ModuleManagerService;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleStatus
{
    protected ModuleManagerService $moduleManager;

    public function __construct(ModuleManagerService $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        if (config('ermo.emergency_bypass')) {
            return $next($request);
        }

        $statuses = $this->moduleManager->getCachedStatuses();
        $module = $statuses[$moduleSlug] ?? null;

        // 1. Module doesn't exist or is disabled
        if (!$module || $module['status'] === 'disabled') {
            return $this->handleDeactivated($request, $moduleSlug);
        }

        // 2. Module is in maintenance
        if ($module['status'] === 'maintenance') {
            return $this->handleMaintenance($request, $moduleSlug);
        }

        return $next($request);
    }

    protected function handleDeactivated(Request $request, string $slug): Response
    {
        $message = "الموديول {$slug} غير مفعل حالياً.";

        if ($request->expectsJson()) {
            return response()->json(['status' => 'error', 'message' => $message], 403);
        }

        abort(403, $message);
        return response('', 403); // Satisfy linter
    }

    protected function handleMaintenance(Request $request, string $slug): Response
    {
        $message = "الموديول {$slug} تحت الصيانة حالياً. يرجى المحاولة لاحقاً.";

        if ($request->expectsJson()) {
            return response()->json(['status' => 'maintenance', 'message' => $message], 503);
        }

        // Potential: Redirect to a custom maintenance page for the module
        abort(503, $message);
        return response('', 503); // Satisfy linter
    }
}