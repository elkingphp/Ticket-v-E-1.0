<?php

namespace Modules\Core\Application\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class FallbackLogger
{
    /**
     * تسجيل الإشعارات الفاشلة مع محاولة إرسال عبر قنوات بديلة
     */
    public function logFailedNotification(string $eventType, array $data, $notifiable, \Exception $e)
    {
        // 1. تسجيل في ملف منفصل
        Log::channel('notifications')->critical('Failed to send notification', [
            'event_type' => $eventType,
            'notifiable' => $this->getNotifiableIdentifier($notifiable),
            'data' => $data,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // 2. حفظ في ملف JSON للمعالجة اللاحقة
        $this->saveToFailedQueue($eventType, $data, $notifiable, $e);

        // 3. محاولة إرسال عبر قناة بديلة (Slack/Telegram)
        $this->tryFallbackChannels($eventType, $data, $e);
    }

    /**
     * حفظ الإشعار الفاشل في قائمة الانتظار
     */
    protected function saveToFailedQueue(string $eventType, array $data, $notifiable, \Exception $e): void
    {
        try {
            $failedNotifications = $this->getFailedNotifications();

            $failedNotifications[] = [
                'event_type' => $eventType,
                'data' => $data,
                'notifiable_id' => $this->getNotifiableIdentifier($notifiable),
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
                'retry_count' => 0,
            ];

            Storage::disk('local')->put(
                'failed_notifications.json',
                json_encode($failedNotifications, JSON_PRETTY_PRINT)
            );
        }
        catch (\Exception $storageError) {
            Log::emergency('Failed to save failed notification to storage', [
                'error' => $storageError->getMessage(),
            ]);
        }
    }

    /**
     * محاولة إرسال عبر قنوات بديلة
     */
    protected function tryFallbackChannels(string $eventType, array $data, \Exception $e): void
    {
        try {
            // محاولة Slack إذا كان مكوّن
            if (config('services.slack.webhook')) {
                Notification::route('slack', config('services.slack.webhook'))
                    ->notify(new \App\Notifications\FallbackNotification($eventType, $data, $e));

                Log::info('Fallback notification sent via Slack', [
                    'event_type' => $eventType,
                ]);
            }
        }
        catch (\Exception $slackError) {
            Log::emergency('All notification channels failed', [
                'original_error' => $e->getMessage(),
                'fallback_error' => $slackError->getMessage(),
            ]);
        }
    }

    /**
     * إعادة محاولة الإشعارات الفاشلة
     */
    public function retryFailedNotifications(): int
    {
        $failedNotifications = $this->getFailedNotifications();
        $retried = [];
        $successCount = 0;

        foreach ($failedNotifications as $notification) {
            // تجاوز الإشعارات التي تم محاولتها أكثر من 3 مرات
            if (($notification['retry_count'] ?? 0) >= 3) {
                $retried[] = $notification;
                continue;
            }

            try {
                // محاولة إعادة الإرسال
                $notifiable = $this->resolveNotifiable($notification['notifiable_id']);

                if ($notifiable) {
                    app(AlertService::class)->sendAlert(
                        $notification['event_type'],
                        $notification['data'],
                        $notifiable
                    );

                    $successCount++;
                    Log::info('Successfully retried failed notification', [
                        'event_type' => $notification['event_type'],
                    ]);
                }
                else {
                    // الاحتفاظ بالفاشلة مع زيادة العداد
                    $notification['retry_count'] = ($notification['retry_count'] ?? 0) + 1;
                    $retried[] = $notification;
                }
            }
            catch (\Exception $e) {
                // الاحتفاظ بالفاشلة مع زيادة العداد
                $notification['retry_count'] = ($notification['retry_count'] ?? 0) + 1;
                $notification['last_error'] = $e->getMessage();
                $retried[] = $notification;
            }
        }

        // تحديث الملف
        Storage::disk('local')->put(
            'failed_notifications.json',
            json_encode($retried, JSON_PRETTY_PRINT)
        );

        return $successCount;
    }

    /**
     * الحصول على الإشعارات الفاشلة
     */
    protected function getFailedNotifications(): array
    {
        if (!Storage::disk('local')->exists('failed_notifications.json')) {
            return [];
        }

        $content = Storage::disk('local')->get('failed_notifications.json');
        return json_decode($content, true) ?? [];
    }

    /**
     * الحصول على معرّف الـ Notifiable
     */
    protected function getNotifiableIdentifier($notifiable): string
    {
        if (is_object($notifiable)) {
            return $notifiable->id ?? 'broadcast';
        }

        if (is_iterable($notifiable)) {
            return 'collection';
        }

        return 'unknown';
    }

    /**
     * استرجاع الـ Notifiable من المعرّف
     */
    protected function resolveNotifiable(string $identifier)
    {
        if ($identifier === 'broadcast' || $identifier === 'collection' || $identifier === 'unknown') {
            return null;
        }

        try {
            return \Modules\Users\Domain\Models\User::find($identifier);
        }
        catch (\Exception $e) {
            return null;
        }
    }

    /**
     * حذف الإشعارات الفاشلة القديمة
     */
    public function cleanupOldFailedNotifications(int $days = 30): int
    {
        $failedNotifications = $this->getFailedNotifications();
        $cutoff = now()->subDays($days);
        $cleaned = 0;

        $remaining = array_filter($failedNotifications, function ($notification) use ($cutoff, &$cleaned) {
            $timestamp = \Carbon\Carbon::parse($notification['timestamp']);

            if ($timestamp->lt($cutoff)) {
                $cleaned++;
                return false;
            }

            return true;
        });

        Storage::disk('local')->put(
            'failed_notifications.json',
            json_encode(array_values($remaining), JSON_PRETTY_PRINT)
        );

        return $cleaned;
    }

    /**
     * الحصول على إحصائيات الإشعارات الفاشلة
     */
    public function getFailedNotificationsStats(): array
    {
        $failed = $this->getFailedNotifications();

        return [
            'total' => count($failed),
            'by_type' => array_count_values(array_column($failed, 'event_type')),
            'max_retries' => count(array_filter($failed, fn($n) => ($n['retry_count'] ?? 0) >= 3)),
        ];
    }
}