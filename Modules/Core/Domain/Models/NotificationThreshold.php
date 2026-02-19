<?php

namespace Modules\Core\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationThreshold extends Model
{
    protected $fillable = [
        'event_type',
        'max_count',
        'time_window',
        'severity',
        'enabled',
        'description',
    ];

    protected $casts = [
        'max_count' => 'integer',
        'time_window' => 'integer',
        'enabled' => 'boolean',
    ];

    /**
     * Get human-readable time window.
     */
    public function getTimeWindowHumanAttribute(): string
    {
        $seconds = $this->time_window;

        if ($seconds < 60) {
            return "{$seconds} seconds";
        }

        if ($seconds < 3600) {
            return round($seconds / 60) . " minutes";
        }

        if ($seconds < 86400) {
            return round($seconds / 3600) . " hours";
        }

        return round($seconds / 86400) . " days";
    }

    /**
     * Check if threshold is exceeded.
     */
    public function isExceeded(int $currentCount): bool
    {
        return $this->enabled && $currentCount >= $this->max_count;
    }

    /**
     * Get severity badge color.
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
                'critical' => 'danger',
                'warning' => 'warning',
                'info' => 'info',
                default => 'secondary',
            };
    }
}