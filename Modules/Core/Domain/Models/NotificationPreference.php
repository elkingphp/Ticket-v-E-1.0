<?php

namespace Modules\Core\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'channels',
        'sound_enabled',
        'enabled',
    ];

    protected $casts = [
        'channels' => 'array',
        'sound_enabled' => 'boolean',
        'enabled' => 'boolean',
    ];

    /**
     * Get the user that owns the preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Domain\Models\User::class);
    }

    /**
     * Check if a specific channel is enabled.
     */
    public function hasChannel(string $channel): bool
    {
        return in_array($channel, $this->channels ?? []);
    }

    /**
     * Get default preferences for a user.
     */
    public static function getDefaultPreferences(): array
    {
        return [
            'audit_critical' => ['database', 'mail', 'broadcast'],
            'system_health' => ['database', 'mail'],
            'user_registered' => ['database'],
            'threshold_exceeded' => ['database', 'mail', 'broadcast'],
            'ticket_created' => ['database', 'mail', 'broadcast'],
            'ticket_updated' => ['database', 'mail', 'broadcast'],
        ];
    }

    /**
     * Create default preferences for a user.
     */
    public static function createDefaultsForUser(int $userId): void
    {
        foreach (self::getDefaultPreferences() as $eventType => $channels) {
            self::create([
                'user_id' => $userId,
                'event_type' => $eventType,
                'channels' => $channels,
                'sound_enabled' => false,
                'enabled' => true,
            ]);
        }
    }
}