<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class HealthCheckController extends Controller
{
    /**
     * Check the health of the application services.
     */
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'queue' => $this->checkQueue(),
            'reverb' => $this->checkReverb(),
            'storage' => $this->checkStorage(),
        ];

        // Overall status
        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');
        $hasWarnings = collect($checks)->contains(fn($check) => $check['status'] === 'warning');

        return response()->json([
            'status' => $allHealthy ? 'healthy' : ($hasWarnings ? 'degraded' : 'unhealthy'),
            'timestamp' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'checks' => $checks,
        ], $allHealthy ? 200 : ($hasWarnings ? 200 : 503));
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $responseTime = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => $responseTime < 100 ? 'ok' : 'warning',
                'message' => 'Database connection successful',
                'response_time_ms' => $responseTime,
            ];
        }
        catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => app()->environment('production') ? 'Connection error' : $e->getMessage(),
            ];
        }
    }

    private function checkRedis(): array
    {
        try {
            $start = microtime(true);
            Redis::ping();
            $responseTime = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => $responseTime < 50 ? 'ok' : 'warning',
                'message' => 'Redis connection successful',
                'response_time_ms' => $responseTime,
            ];
        }
        catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Redis connection failed',
                'error' => app()->environment('production') ? 'Connection error' : $e->getMessage(),
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            $start = microtime(true);
            // In a real scenario, you might want to check the queue length or heartbeat
            // Here we just check if the failed_jobs table is accessible
            $failed = DB::table('failed_jobs')->count();
            $responseTime = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => $failed > 100 ? 'warning' : 'ok',
                'message' => 'Queue is operational',
                'failed_jobs_count' => $failed,
                'response_time_ms' => $responseTime,
            ];
        }
        catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue check failed',
                'error' => app()->environment('production') ? 'Check error' : $e->getMessage(),
            ];
        }
    }

    private function checkReverb(): array
    {
        try {
            $start = microtime(true);
            $port = env('REVERB_PORT', 8080);
            $connection = @fsockopen('localhost', (int)$port, $errno, $errstr, 1);
            $responseTime = round((microtime(true) - $start) * 1000, 2);

            if ($connection) {
                fclose($connection);
                return [
                    'status' => 'ok',
                    'message' => 'Reverb is running',
                    'port' => $port,
                    'response_time_ms' => $responseTime,
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Reverb is not responding',
                'port' => $port,
            ];
        }
        catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Reverb check failed',
                'error' => app()->environment('production') ? 'Check error' : $e->getMessage(),
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $start = microtime(true);
            $storagePath = storage_path();
            $writable = is_writable($storagePath);

            // Check disk space if supported on the system
            $freeSpace = @disk_free_space($storagePath);
            $totalSpace = @disk_total_space($storagePath);
            $responseTime = round((microtime(true) - $start) * 1000, 2);

            $usedPercent = 0;
            if ($totalSpace && $freeSpace) {
                $usedPercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
            }

            return [
                'status' => ($usedPercent > 90 || !$writable) ? 'warning' : 'ok',
                'message' => 'Storage is accessible',
                'writable' => $writable,
                'used_percent' => round($usedPercent, 2),
                'free_space_gb' => $freeSpace ? round($freeSpace / 1024 / 1024 / 1024, 2) : 'unknown',
                'response_time_ms' => $responseTime,
            ];
        }
        catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check failed',
                'error' => app()->environment('production') ? 'Check error' : $e->getMessage(),
            ];
        }
    }
}