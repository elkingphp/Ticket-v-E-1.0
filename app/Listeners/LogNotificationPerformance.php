<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Queue\InteractsWithQueue;

class LogNotificationPerformance
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
    public function handle(\Illuminate\Notifications\Events\NotificationSent $event): void
    {
        // For sync sending, we don't have job start time easily, 
        // but for queued ones we could track it.
        // For simplicity, we just log that it was sent successfully.

        \Illuminate\Support\Facades\Log::info("Notification Performance: Sent notification via {$event->channel}", [
            'notifiable_id' => $event->notifiable->id,
            'channel' => $event->channel,
            'notification' => get_class($event->notification),
        ]);
    }
}