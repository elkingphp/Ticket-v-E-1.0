<?php

namespace Modules\Core\Application\Services\Dashboard\Metrics;

use Illuminate\Support\Facades\DB;
use Modules\Core\Application\Services\Dashboard\BaseMetricService;

class SystemHealthService extends BaseMetricService
{
    protected function calculate(array $options): array
    {
        // Database size
        $dbSize = DB::select("SELECT pg_database_size(current_database()) as size")[0]->size ?? 0;
        $dbSizeMB = round($dbSize / 1024 / 1024, 2);

        // Table counts
        $tables = DB::select("SELECT count(*) as count FROM information_schema.tables WHERE table_schema = 'public'")[0]->count ?? 0;

        // Laravel version
        $laravelVersion = app()->version();

        // PHP version
        $phpVersion = PHP_VERSION;

        return [
            'database_size_mb' => $dbSizeMB,
            'total_tables' => $tables,
            'laravel_version' => $laravelVersion,
            'php_version' => $phpVersion,
            'status' => 'healthy',
        ];
    }
}