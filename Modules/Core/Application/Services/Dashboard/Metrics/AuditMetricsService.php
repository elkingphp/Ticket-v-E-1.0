<?php

namespace Modules\Core\Application\Services\Dashboard\Metrics;

use Illuminate\Support\Facades\DB;
use Modules\Core\Application\Services\Dashboard\BaseMetricService;
use Modules\Core\Domain\Models\AuditLog;
use Carbon\Carbon;

class AuditMetricsService extends BaseMetricService
{
    protected function calculate(array $options): array
    {
        $days = $options['days'] ?? 7;

        // 1. Critical Events Count
        $criticalCount = AuditLog::where('log_level', 'critical')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        // 2. Trend Data (SQL Aggregate)
        $trend = AuditLog::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Fill missing dates with 0
        $formattedTrend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $formattedTrend[$date] = $trend[$date] ?? 0;
        }

        // 3. Category Distribution
        $distribution = AuditLog::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderBy('count', 'DESC')
            ->take(5)
            ->get()
            ->pluck('count', 'category')
            ->toArray();

        return [
            'critical_events_24h' => $criticalCount,
            'total_logs_last_7d' => array_sum($formattedTrend),
            'trend' => [
                'labels' => array_keys($formattedTrend),
                'series' => array_values($formattedTrend),
            ],
            'distribution' => [
                'labels' => array_keys($distribution),
                'series' => array_values($distribution),
            ]
        ];
    }
}