<?php

namespace Modules\Core\Application\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Notification;
use Modules\Core\Domain\Models\NotificationThreshold;

class AlertService
{
    protected FallbackLogger $fallbackLogger;

    public function __construct(FallbackLogger $fallbackLogger)
    {
        $this->fallbackLogger = $fallbackLogger;
    }

    /**
     * إرسال تنبيه مع Rate Limiting متعدد المستويات
     */
    public function sendAlert(string $eventType, array $data, $notifiable)
    {
        // Multi-Level Rate Limiting
        if (!$this->checkRateLimits($eventType, $notifiable)) {
            Log::warning("Rate limit exceeded", [
                'event_type' => $eventType,
                'notifiable' => is_object($notifiable) ? ($notifiable->id ?? 'broadcast') : 'collection',
            ]);
            return false;
        }

        // Threshold Check (Dynamic)
        if ($this->shouldSendAggregatedAlert($eventType)) {
            return $this->sendAggregatedAlert($eventType, $notifiable);
        }

        try {
            $notification = $this->createNotification($eventType, $data);

            // إرسال للمستخدمين أو Broadcast
            if (is_iterable($notifiable)) {
                Notification::send($notifiable, $notification);
            }
            else {
                $notifiable->notify($notification);
            }

            // تسجيل النجاح
            $this->recordSuccess($eventType, $notifiable);

            return true;
        }
        catch (\Exception $e) {
            // Fallback Mechanism
            $this->fallbackLogger->logFailedNotification($eventType, $data, $notifiable, $e);
            return false;
        }
    }

    /**
     * فحص Rate Limits متعدد المستويات
     */
    protected function checkRateLimits(string $eventType, $notifiable): bool
    {
        $userId = is_object($notifiable) ? ($notifiable->id ?? 'broadcast') : 'collection';

        // 1. User-Specific Limit (3 notifications per minute)
        $userKey = "alert:user:{$userId}:{$eventType}";
        if (RateLimiter::tooManyAttempts($userKey, 3)) {
            return false;
        }

        // 2. Event-Specific Limit (10 notifications per 5 minutes)
        $eventKey = "alert:event:{$eventType}";
        if (RateLimiter::tooManyAttempts($eventKey, 10)) {
            return false;
        }

        // 3. Global Limit (50 notifications per minute)
        $globalKey = "alert:global";
        if (RateLimiter::tooManyAttempts($globalKey, 50)) {
            return false;
        }

        // تسجيل المحاولات
        RateLimiter::hit($userKey, 60); // 1 دقيقة
        RateLimiter::hit($eventKey, 300); // 5 دقائق
        RateLimiter::hit($globalKey, 60); // 1 دقيقة

        return true;
    }

    /**
     * فحص Threshold الديناميكي
     */
    protected function shouldSendAggregatedAlert(string $eventType): bool
    {
        $threshold = NotificationThreshold::where('event_type', $eventType)
            ->where('enabled', true)
            ->first();

        if (!$threshold) {
            return false;
        }

        $cacheKey = "threshold:{$eventType}";
        $count = Cache::get($cacheKey, 0);

        Cache::put($cacheKey, $count + 1, $threshold->time_window);

        return $count >= $threshold->max_count;
    }

    /**
     * إرسال تنبيه مجمّع
     */
    protected function sendAggregatedAlert(string $eventType, $notifiable)
    {
        $threshold = NotificationThreshold::where('event_type', $eventType)->first();
        $count = Cache::get("threshold:{$eventType}", 0);

        $data = [
            'title' => 'Threshold Exceeded',
            'message' => "Detected {$count} {$eventType} events in the last " .
            ($threshold->time_window / 60) . " minutes",
            'severity' => $threshold->severity,
            'count' => $count,
            'event_type' => $eventType,
        ];

        // Reset counter after sending aggregated alert
        Cache::forget("threshold:{$eventType}");

        return $this->sendAlert('threshold_exceeded', $data, $notifiable);
    }

    /**
     * إنشاء الإشعار المناسب
     */
    protected function createNotification(string $eventType, array $data)
    {
        return match ($eventType) {
                'audit_critical' => new \App\Notifications\CriticalAuditAlert($data),
                'system_health' => new \App\Notifications\SystemHealthAlert($data),
                'user_registered' => new \App\Notifications\UserRegisteredAlert($data),
                'threshold_exceeded' => new \App\Notifications\ThresholdExceededAlert($data),
                default => new \App\Notifications\GenericAlert($eventType, $data),
            };
    }

    /**
     * تسجيل النجاح
     */
    protected function recordSuccess(string $eventType, $notifiable)
    {
        Cache::increment("stats:notifications:{$eventType}:sent");

        Log::info("Notification sent successfully", [
            'event_type' => $eventType,
            'notifiable' => is_object($notifiable) ? ($notifiable->id ?? 'broadcast') : 'collection',
        ]);
    }

    /**
     * الحصول على إحصائيات الإشعارات
     */
    public function getStats(): array
    {
        $eventTypes = ['audit_critical', 'system_health', 'user_registered', 'threshold_exceeded'];
        $stats = [];

        foreach ($eventTypes as $type) {
            $stats[$type] = Cache::get("stats:notifications:{$type}:sent", 0);
        }

        return $stats;
    }

    /**
     * إعادة تعيين الإحصائيات
     */
    public function resetStats(): void
    {
        $eventTypes = ['audit_critical', 'system_health', 'user_registered', 'threshold_exceeded'];

        foreach ($eventTypes as $type) {
            Cache::forget("stats:notifications:{$type}:sent");
        }
    }
}