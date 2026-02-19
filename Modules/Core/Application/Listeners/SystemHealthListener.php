<?php

namespace Modules\Core\Application\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Core\Domain\Events\SystemHealthChecked;
use Modules\Core\Application\Services\AlertService;
use Modules\Users\Domain\Models\User;

class SystemHealthListener
{
    protected AlertService $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Handle the event.
     */
    public function handle(SystemHealthChecked $event): void
    {
        $health = $event->healthData;

        // فحص حجم قاعدة البيانات
        if (isset($health['database_size_mb']) && $health['database_size_mb'] > 500) {
            $this->sendDatabaseSizeAlert($health['database_size_mb']);
        }

        // فحص حالة النظام
        if (isset($health['status']) && $health['status'] === 'degraded') {
            $this->sendSystemDegradedAlert($health);
        }

        // فحص الذاكرة (إذا كانت متوفرة)
        if (isset($health['memory_usage_percent']) && $health['memory_usage_percent'] > 90) {
            $this->sendMemoryAlert($health['memory_usage_percent']);
        }
    }

    /**
     * إرسال تنبيه حجم قاعدة البيانات
     */
    protected function sendDatabaseSizeAlert(float $sizeInMB): void
    {
        Log::warning('Database size threshold exceeded', [
            'size_mb' => $sizeInMB,
        ]);

        $this->alertService->sendAlert('system_health', [
            'title' => 'Database Size Warning',
            'message' => "Database size has exceeded 500MB (Current: {$sizeInMB}MB)",
            'issue' => 'Database size exceeded threshold',
            'recommendation' => 'Consider archiving old data or increasing storage capacity',
            'severity' => 'warning',
        ], $this->getSuperAdmins());
    }

    /**
     * إرسال تنبيه تدهور النظام
     */
    protected function sendSystemDegradedAlert(array $health): void
    {
        Log::critical('System health degraded', $health);

        $issues = $this->identifyIssues($health);

        $this->alertService->sendAlert('system_health', [
            'title' => 'System Health Degraded',
            'message' => 'System health check detected performance issues',
            'issue' => implode(', ', $issues),
            'recommendation' => 'Please investigate system performance immediately',
            'severity' => 'critical',
        ], $this->getSuperAdmins());
    }

    /**
     * إرسال تنبيه الذاكرة
     */
    protected function sendMemoryAlert(float $memoryPercent): void
    {
        Log::warning('High memory usage detected', [
            'memory_percent' => $memoryPercent,
        ]);

        $this->alertService->sendAlert('system_health', [
            'title' => 'High Memory Usage',
            'message' => "Memory usage is at {$memoryPercent}%",
            'issue' => 'High memory consumption detected',
            'recommendation' => 'Consider restarting services or investigating memory leaks',
            'severity' => 'warning',
        ], $this->getSuperAdmins());
    }

    /**
     * تحديد المشاكل من بيانات الصحة
     */
    protected function identifyIssues(array $health): array
    {
        $issues = [];

        if (isset($health['database_size_mb']) && $health['database_size_mb'] > 500) {
            $issues[] = 'Large database size';
        }

        if (isset($health['memory_usage_percent']) && $health['memory_usage_percent'] > 80) {
            $issues[] = 'High memory usage';
        }

        if (isset($health['disk_usage_percent']) && $health['disk_usage_percent'] > 80) {
            $issues[] = 'High disk usage';
        }

        if (empty($issues)) {
            $issues[] = 'Unknown performance issue';
        }

        return $issues;
    }

    /**
     * الحصول على المسؤولين الرئيسيين
     */
    protected function getSuperAdmins()
    {
        return User::role('super-admin')->get();
    }
}