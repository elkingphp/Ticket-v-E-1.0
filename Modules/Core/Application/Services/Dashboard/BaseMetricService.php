<?php

namespace Modules\Core\Application\Services\Dashboard;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Application\Contracts\DashboardMetricContract;

abstract class BaseMetricService implements DashboardMetricContract
{
    protected int $cacheTTL = 600; // 10 minutes default

    /**
     * Get metrics with caching.
     */
    public function getMetrics(array $options = []): array
    {
        $cacheKey = $this->getCacheKey($options);

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($options) {
            return $this->calculate($options);
        });
    }

    /**
     * The actual calculation logic must be implemented by child classes.
     */
    abstract protected function calculate(array $options): array;

    /**
     * Generate a unique cache key based on service name and options.
     */
    public function getCacheKey(array $options = []): string
    {
        $name = class_basename($this);
        $optionString = md5(serialize($options));
        return "dashboard_metric_{$name}_{$optionString}";
    }

    /**
     * Force refresh the cache for this metric.
     */
    public function refresh(array $options = []): void
    {
        Cache::forget($this->getCacheKey($options));
    }
}