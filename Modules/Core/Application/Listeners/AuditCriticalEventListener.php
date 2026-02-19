<?php

namespace Modules\Core\Application\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Core\Domain\Events\AuditLogCreated;
use Modules\Core\Application\Services\AlertService;
use Modules\Users\Domain\Models\User;

class AuditCriticalEventListener
{
    protected AlertService $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Handle the event.
     */
    public function handle(AuditLogCreated $event): void
    {
        $audit = $event->auditLog;

        // تحقق من الأولوية - نرسل إشعار فقط للأحداث الحرجة
        if ($audit->log_level !== 'critical') {
            return;
        }

        Log::info('Critical audit event detected', [
            'audit_id' => $audit->id,
            'event' => $audit->event,
            'user_id' => $audit->user_id,
        ]);

        // Threshold Check: هل تجاوزنا الحد المسموح؟
        if ($this->exceedsThreshold('audit_critical', 5, 3600)) {
            // إرسال تنبيه مجمّع للمسؤولين
            $count = $this->getThresholdCount('audit_critical', 3600);

            $this->alertService->sendAlert('threshold_exceeded', [
                'title' => 'Critical Events Threshold Exceeded',
                'message' => "Detected {$count} critical events in the last hour",
                'event_type' => 'audit_critical',
                'count' => $count,
                'severity' => 'critical',
            ], $this->getSuperAdmins());

            // إعادة تعيين العداد
            Cache::forget('threshold:audit_critical');
        }
        else {
            // إرسال تنبيه فردي
            $this->alertService->sendAlert('audit_critical', [
                'title' => 'Critical Audit Event',
                'message' => "Critical event: {$audit->event}" .
                ($audit->user ? " by {$audit->user->first_name} {$audit->user->last_name}" : ''),
                'audit_id' => $audit->id,
                'event' => $audit->event,
                'action_url' => route('audit.index', ['id' => $audit->id]),
            ], $this->getSuperAdmins());
        }
    }

    /**
     * فحص تجاوز الحد
     */
    protected function exceedsThreshold(string $key, int $maxCount, int $timeWindow): bool
    {
        $cacheKey = "threshold:{$key}";
        $count = Cache::get($cacheKey, 0);

        Cache::put($cacheKey, $count + 1, $timeWindow);

        return $count >= $maxCount;
    }

    /**
     * الحصول على عدد الأحداث الحالي
     */
    protected function getThresholdCount(string $key, int $timeWindow): int
    {
        return Cache::get("threshold:{$key}", 0);
    }

    /**
     * الحصول على المسؤولين الرئيسيين
     */
    protected function getSuperAdmins()
    {
        return User::role('super-admin')->get();
    }
}