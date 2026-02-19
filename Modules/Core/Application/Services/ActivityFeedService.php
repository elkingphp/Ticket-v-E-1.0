<?php

namespace Modules\Core\Application\Services;

use Modules\Core\Domain\Models\AuditLog;
use Illuminate\Support\Collection;

class ActivityFeedService
{
    /**
     * Get transformed activity logs for a user timeline.
     *
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUserTimeline(int $userId, int $perPage = 10)
    {
        return AuditLog::where('user_id', $userId)
            ->latest()
            ->paginate($perPage)
            ->through(function ($log) {
                return $this->transformLog($log);
            });
    }

    /**
     * Transform raw log into a UI-friendly activity item.
     *
     * @param AuditLog $log
     * @return array
     */
    protected function transformLog(AuditLog $log): array
    {
        $eventData = $this->parseEvent($log);

        return [
            'id' => $log->id,
            'event' => $log->event,
            'title' => $eventData['title'],
            'description' => $eventData['description'],
            'icon' => $eventData['icon'],
            'color' => $eventData['color'],
            'time' => $log->created_at->diffForHumans(),
            'formatted_time' => $log->created_at->isoFormat('D MMMM YYYY, h:mm a'),
            'timestamp' => $log->created_at->toDateTimeString(),
            'ip_address' => $log->ip_address,
            'category' => $log->category,
            'raw_fields' => ($log->event === 'updated' && !empty($log->new_values)) ? array_keys($log->new_values) : [],
        ];
    }

    /**
     * Parse the audit event into readable UI components.
     *
     * @param AuditLog $log
     * @return array
     */
    protected function parseEvent(AuditLog $log): array
    {
        $type = strtolower(class_basename($log->auditable_type));

        $mapping = [
            'created' => [
                'title' => __('core::profile.new_type_created', ['type' => __('core::profile.' . $type)]),
                'icon' => 'ri-add-circle-line',
                'color' => 'success',
            ],
            'updated' => [
                'title' => __('core::profile.type_updated', ['type' => __('core::profile.' . $type)]),
                'icon' => 'ri-edit-2-line',
                'color' => 'info',
            ],
            'deleted' => [
                'title' => __('core::profile.type_deleted', ['type' => __('core::profile.' . $type)]),
                'icon' => 'ri-delete-bin-line',
                'color' => 'danger',
            ],
            'login' => [
                'title' => __('core::profile.login_successful'),
                'icon' => 'ri-login-box-line',
                'color' => 'primary',
            ],
            'failed_login' => [
                'title' => __('core::profile.failed_login_attempt'),
                'icon' => 'ri-alert-line',
                'color' => 'warning',
            ],
            'password_changed' => [
                'title' => __('core::profile.password_changed_activity'),
                'icon' => 'ri-lock-password-line',
                'color' => 'secondary',
            ],
            '2fa_enabled' => [
                'title' => __('core::profile.2fa_enabled_activity'),
                'icon' => 'ri-shield-check-line',
                'color' => 'success',
            ],
            '2fa_disabled' => [
                'title' => __('core::profile.2fa_disabled_activity'),
                'icon' => 'ri-shield-flash-line',
                'color' => 'danger',
            ],
        ];

        $data = $mapping[$log->event] ?? [
            'title' => __('core::profile.system_event', ['event' => $log->event]),
            'icon' => 'ri-record-circle-line',
            'color' => 'dark',
        ];

        // Custom descriptions based on event data if needed
        $data['description'] = $this->generateDescription($log);

        return $data;
    }

    /**
     * Generate a human-readable description for the log.
     *
     * @param AuditLog $log
     * @return string
     */
    protected function generateDescription(AuditLog $log): string
    {
        if ($log->event === 'updated' && !empty($log->new_values)) {
            $changes = [];
            $skipped = ['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'];

            foreach ($log->new_values as $key => $newValue) {
                if (in_array($key, $skipped))
                    continue;

                $fieldLabel = __('core::profile.' . $key);
                if ($fieldLabel === 'core::profile.' . $key)
                    $fieldLabel = $key;

                // For simple strings/numbers, show the change
                if (is_scalar($newValue)) {
                    $oldValue = $log->old_values[$key] ?? 'N/A';
                    if (is_bool($newValue)) {
                        $newValue = $newValue ? 'Yes' : 'No';
                        $oldValue = $oldValue ? 'Yes' : 'No';
                    }
                    $changes[] = "<strong>{$fieldLabel}</strong>: <span class='text-muted'>{$oldValue}</span> → <span class='text-success'>{$newValue}</span>";
                } else {
                    $changes[] = "<strong>{$fieldLabel}</strong>";
                }
            }

            return implode('<br>', $changes);
        }

        return __('core::profile.no_details');
    }

}