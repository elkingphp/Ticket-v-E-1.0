<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Application\Services\Dashboard\DashboardOrchestrator;

class DashboardController extends Controller
{
    protected DashboardOrchestrator $orchestrator;

    public function __construct(DashboardOrchestrator $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    /**
     * Get all dashboard metrics.
     */
    public function metrics(Request $request)
    {
        $options = [
            'days' => $request->input('days', 7),
        ];

        $metrics = $this->orchestrator->getAllMetrics($options);

        return response()->json($metrics);
    }
}