<?php

namespace Modules\Core\Application\Services\Dashboard;

use Modules\Core\Application\Services\Dashboard\Metrics\UserMetricsService;
use Modules\Core\Application\Services\Dashboard\Metrics\AuditMetricsService;
use Modules\Core\Application\Services\Dashboard\Metrics\SystemHealthService;
use Illuminate\Support\Facades\Log;

class DashboardOrchestrator
{
    protected UserMetricsService $userMetrics;
    protected AuditMetricsService $auditMetrics;
    protected SystemHealthService $systemHealth;

    public function __construct(
        UserMetricsService $userMetrics,
        AuditMetricsService $auditMetrics,
        SystemHealthService $systemHealth
        )
    {
        $this->userMetrics = $userMetrics;
        $this->auditMetrics = $auditMetrics;
        $this->systemHealth = $systemHealth;
    }

    /**
     * Get all dashboard metrics with fallback handling.
     */
    public function getAllMetrics(array $options = []): array
    {
        $metrics = [];
        $user = auth()->user();
        $cacheKey = "dashboard_metrics_" . ($user ? $user->id : 'guest');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($user, $options) {
            $metrics = [];

            // User Metrics - Basic visibility for all authorized users
            $metrics['users'] = $this->getMetricSafely('users', function () use ($options) {
                    return $this->userMetrics->getMetrics($options);
                }
                );

                // Audit Metrics - Requires 'audit.view' permission
                if ($user && $user->can('audit.view')) {
                    $metrics['audit'] = $this->getMetricSafely('audit', function () use ($options) {
                            return $this->auditMetrics->getMetrics($options);
                        }
                        );
                    }

                    // System Health - Requires 'settings.view' (admin level)
                    if ($user && $user->can('settings.view')) {
                        $metrics['system'] = $this->getMetricSafely('system', function () use ($options) {
                            return $this->systemHealth->getMetrics($options);
                        }
                        );
                    }

                    // Advanced Analytics (Phase 2 Implementation) - Requires 'analytics.view'
                    if ($user && $user->can('analytics.view')) {
                        $analytics = app(\App\Services\AnalyticsService::class);
                        $metrics['analytics'] = $this->getMetricSafely('analytics', function () use ($analytics) {
                            return [
                            'profile_completion' => $analytics->getGlobalProfileCompletion(),
                            'growth_trends' => $analytics->getUserGrowthTrends(),
                            ];
                        }
                        );
                    }

                    return $metrics;
                });
    }

    /**
     * Execute metric retrieval with error handling.
     */
    protected function getMetricSafely(string $name, callable $callback): array
    {
        try {
            $data = $callback();
            return [
                'status' => 'success',
                'data' => $data,
                'cached' => true,
                'last_updated' => now()->toIso8601String(),
            ];
        }
        catch (\Throwable $e) {
            Log::error("Dashboard metric '{$name}' failed: " . $e->getMessage());
            return [
                'status' => 'error',
                'data' => [],
                'error' => 'Failed to load metric',
                'last_updated' => now()->toIso8601String(),
            ];
        }
    }
}