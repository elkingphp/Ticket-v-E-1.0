<?php

namespace Modules\Core\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class NotificationMonitor
{
    /**
     * فحص صحة جميع قنوات الإشعارات
     */
    public function checkChannelHealth(): array
    {
        $results = [
            'database' => $this->checkDatabaseHealth(),
            'mail' => $this->checkMailHealth(),
            'redis' => $this->checkRedisHealth(),
            'reverb' => $this->checkReverbHealth(),
        ];

        // حفظ النتائج في الـ Cache
        Cache::put('notification_channels_health', $results, 300); // 5 دقائق

        // تسجيل الحالة
        $this->logHealthStatus($results);

        return $results;
    }

    /**
     * فحص صحة قاعدة البيانات
     */
    protected function checkDatabaseHealth(): array
    {
        $start = microtime(true);

        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');

            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'healthy',
                'latency_ms' => $latency,
                'message' => 'Database connection is working',
            ];
        }
        catch (\Exception $e) {
            return [
                'status' => 'failed',
                'latency_ms' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * فحص صحة البريد الإلكتروني
     */
    protected function checkMailHealth(): array
    {
        $start = microtime(true);
        
        try {
            // فحص تكوين البريد الإلكتروني
            $mailer = config('mail.default');
            $config = config("mail.mailers.{$mailer}");
            
            if (!$config) {
                throw new \Exception("Mail configuration not found for driver: {$mailer}");
            }
            
            $latency = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'status' => 'healthy',
                'latency_ms' => $latency,
                'message' => "Mail driver '{$mailer}' is configured",
            ];
        }
        catch (\Exception $e) {
            return [
                'status' => 'failed',
                'latency_ms' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * فحص صحة Redis
     */
    protected function checkRedisHealth(): array
    {
        $start = microtime(true);

        try {
            Redis::ping();

            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'healthy',
                'latency_ms' => $latency,
                'message' => 'Redis is responding',
            ];
        }
        catch (\Exception $e) {
            return [
                'status' => 'failed',
                'latency_ms' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * فحص صحة Laravel Reverb
     */
    protected function checkReverbHealth(): array
    {
        $start = microtime(true);

        try {
            $host = env('REVERB_HOST', 'localhost');
            $port = env('REVERB_PORT', 8080);

            // محاولة الاتصال بـ Reverb health endpoint
            $response = Http::timeout(3)->get("http://{$host}:{$port}/health");

            $latency = round((microtime(true) - $start) * 1000, 2);

            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'latency_ms' => $latency,
                    'message' => 'Reverb WebSocket server is running',
                ];
            }

            return [
                'status' => 'degraded',
                'latency_ms' => $latency,
                'message' => 'Reverb responded with status: ' . $response->status(),
            ];
        }
        catch (\Exception $e) {
            return [
                'status' => 'failed',
                'latency_ms' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * تسجيل حالة الصحة
     */
    protected function logHealthStatus(array $results): void
    {
        $failedChannels = array_filter($results, fn($r) => $r['status'] === 'failed');

        if (!empty($failedChannels)) {
            \Log::warning('Some notification channels are unhealthy', [
                'failed_channels' => array_keys($failedChannels),
                'details' => $failedChannels,
            ]);
        }
    }

    /**
     * الحصول على حالة الصحة من الـ Cache
     */
    public function getCachedHealth(): ?array
    {
        return Cache::get('notification_channels_health');
    }

    /**
     * فحص ما إذا كانت جميع القنوات صحية
     */
    public function areAllChannelsHealthy(): bool
    {
        $health = $this->getCachedHealth() ?? $this->checkChannelHealth();

        foreach ($health as $channel => $status) {
            if ($status['status'] !== 'healthy') {
                return false;
            }
        }

        return true;
    }

    /**
     * الحصول على القنوات الفاشلة
     */
    public function getFailedChannels(): array
    {
        $health = $this->getCachedHealth() ?? $this->checkChannelHealth();

        return array_filter($health, fn($r) => $r['status'] === 'failed');
    }

    /**
     * الحصول على متوسط زمن الاستجابة
     */
    public function getAverageLatency(): float
    {
        $health = $this->getCachedHealth() ?? $this->checkChannelHealth();

        $latencies = array_filter(
            array_column($health, 'latency_ms'),
        fn($l) => $l !== null
        );

        if (empty($latencies)) {
            return 0;
        }

        return round(array_sum($latencies) / count($latencies), 2);
    }

    /**
     * الحصول على تقرير مفصل
     */
    public function getDetailedReport(): array
    {
        $health = $this->checkChannelHealth();

        return [
            'timestamp' => now()->toIso8601String(),
            'overall_status' => $this->areAllChannelsHealthy() ? 'healthy' : 'degraded',
            'channels' => $health,
            'average_latency_ms' => $this->getAverageLatency(),
            'failed_count' => count($this->getFailedChannels()),
        ];
    }
}