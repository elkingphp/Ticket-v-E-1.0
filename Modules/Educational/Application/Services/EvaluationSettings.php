<?php

namespace Modules\Educational\Application\Services;

use Modules\Core\Application\Services\SettingsService;
use Modules\Core\Application\Services\AuditLoggerService;

class EvaluationSettings
{
    public function __construct(
        public SettingsService $baseSettings,
        protected AuditLoggerService $auditLogger
    ) {
    }

    // ─── Analytics Settings ──────────────────────────────────────────────────

    public function redFlagThreshold(): float
    {
        return (float) $this->baseSettings->get('evaluation_analytics_threshold', 3.0);
    }

    public function isRedFlagEnabled(): bool
    {
        return (bool) $this->baseSettings->get('evaluation_analytics_red_flag_enabled', true);
    }

    public function resultsCacheDuration(): int
    {
        return (int) $this->baseSettings->get('evaluation_analytics_cache_seconds', 600);
    }

    // ─── Notification Settings ────────────────────────────────────────────────

    public function notificationChannels(): array
    {
        $channel = $this->baseSettings->get('evaluation_notifications_channel', 'mail_and_database');

        return match ($channel) {
            'mail' => ['mail'],
            'database' => ['database'],
            'mail_and_database' => ['mail', 'database'],
            default => ['database'],
        };
    }

    public function isAssignmentNotificationEnabled(): bool
    {
        return (bool) $this->baseSettings->get('evaluation_notifications_assignment_enabled', true);
    }

    // ─── Scheduler Settings ──────────────────────────────────────────────────

    public function weeklyReportSchedule(): array
    {
        return [
            'day' => $this->baseSettings->get('evaluation_scheduler_report_day', 'fridays'),
            'time' => $this->baseSettings->get('evaluation_scheduler_report_time', '08:00'),
        ];
    }

    // ─── Management ──────────────────────────────────────────────────────────

    /**
     * Update multiple settings and log the changes.
     */
    public function updateSettings(array $newSettings): void
    {
        // Category Mapping for keys
        $mapping = [
            'red_flag_threshold' => 'evaluation_analytics_threshold',
            'red_flag_enabled' => 'evaluation_analytics_red_flag_enabled',
            'results_cache_seconds' => 'evaluation_analytics_cache_seconds',
            'notification_channel' => 'evaluation_notifications_channel',
            'assignment_notify_enabled' => 'evaluation_notifications_assignment_enabled',
            'weekly_report_day' => 'evaluation_scheduler_report_day',
            'weekly_report_time' => 'evaluation_scheduler_report_time',
        ];

        foreach ($newSettings as $key => $value) {
            $fullKey = $mapping[$key] ?? "evaluation_{$key}";
            $oldValue = $this->baseSettings->get($fullKey);

            if ($oldValue != $value) {
                $this->baseSettings->set($fullKey, $value, 'Educational');

                $this->auditLogger->log(
                    "updated_setting_{$fullKey}",
                    'evaluation_settings',
                    ['value' => $oldValue],
                    ['value' => $value]
                );
            }
        }
    }
}
