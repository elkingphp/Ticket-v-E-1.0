<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleNotificationFailure
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    //
    }

    /**
     * Handle the event.
     */
    public function handle(\Illuminate\Notifications\Events\NotificationFailed $event): void
    {
        \Illuminate\Support\Facades\Log::error('Real-Time: Notification failed on channel ' . $event->channel, [
            'notifiable_id' => $event->notifiable->id,
            'notification_id' => $event->notification->id ?? 'unknown',
        ]);

        \Illuminate\Support\Facades\DB::table('notification_retry_logs')->insert([
            'notification_id' => $event->notification->id ?? (\Illuminate\Support\Str::isUuid($event->notification->id ?? '') ? $event->notification->id : \Illuminate\Support\Str::uuid()),
            'notifiable_type' => get_class($event->notifiable),
            'notifiable_id' => $event->notifiable->id,
            'channel' => $event->channel,
            'error_message' => isset($event->data['exception']) ? $event->data['exception']->getMessage() : 'Unknown Exception',
            'payload' => json_encode($event->data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}