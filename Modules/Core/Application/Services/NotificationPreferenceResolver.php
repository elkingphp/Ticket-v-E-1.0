<?php

namespace Modules\Core\Application\Services;

use Modules\Core\Domain\Models\NotificationPreference;
use Modules\Core\Domain\Models\NotificationEventType;

class NotificationPreferenceResolver
{
    /**
     * Resolve the notification channels for a given event and user.
     *
     * @param mixed $notifiable
     * @param string $eventKey
     * @param array $defaultChannels
     * @return array
     */
    public function resolve($notifiable, string $eventKey, array $defaultChannels = ['database']): array
    {
        // 1. Check if event is mandatory
        $eventType = NotificationEventType::where('key', $eventKey)->first();

        if ($eventType && $eventType->is_mandatory) {
            // Mandatory events ignore user disabling the event entirely,
            // but we still check for specific channel preferences if they exist.
            $preference = NotificationPreference::where('user_id', $notifiable->id)
                ->where('event_type', $eventKey)
                ->first();

            if ($preference && !empty($preference->channels)) {
                // Ensure mandatory channels (like database) are always included if they are in available_channels
                $channels = $preference->channels;
                if (!in_array('database', $channels)) {
                    $channels[] = 'database';
                }
                return array_unique($channels);
            }

            return array_unique(array_merge($defaultChannels, ['database']));
        }

        // 2. Non-mandatory events: Check user preferences
        $preference = NotificationPreference::where('user_id', $notifiable->id)
            ->where('event_type', $eventKey)
            ->first();

        if ($preference) {
            if (!$preference->enabled) {
                return []; // User disabled this notification type
            }
            return $preference->channels ?: $defaultChannels;
        }

        // 3. Fallback to default
        return $defaultChannels;
    }

    /**
     * Update user notification preferences.
     *
     * @param int $userId
     * @param array $preferencesData
     * @return void
     */
    public function updateUserPreferences(int $userId, array $preferencesData): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($userId, $preferencesData) {
            foreach (NotificationEventType::all() as $type) {
                $channels = $preferencesData[$type->key] ?? [];

                // Mandatory logic enforcement: Database channel is always mandatory for mandatory events
                if ($type->is_mandatory && !in_array('database', $channels)) {
                    $channels[] = 'database';
                }

                NotificationPreference::updateOrCreate(
                ['user_id' => $userId, 'event_type' => $type->key],
                [
                    'channels' => array_intersect($channels, $type->available_channels),
                    'enabled' => !empty($channels) || $type->is_mandatory
                ]
                );
            }
        });
    }
}