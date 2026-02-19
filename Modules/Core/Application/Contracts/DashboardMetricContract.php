<?php

namespace Modules\Core\Application\Contracts;

interface DashboardMetricContract
{
    /**
     * Get the metric data.
     * 
     * @param array $options Filters like 'range', etc.
     * @return array
     */
    public function getMetrics(array $options = []): array;

    /**
     * Get the cache key for this metric.
     * 
     * @param array $options
     * @return string
     */
    public function getCacheKey(array $options = []): string;
}