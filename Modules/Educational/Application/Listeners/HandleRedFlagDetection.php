<?php

namespace Modules\Educational\Application\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Modules\Educational\Domain\Events\RedFlagDetected;
use Modules\Educational\Application\Notifications\RedFlagAlertNotification;
use Modules\Users\Domain\Models\User;

class HandleRedFlagDetection implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(RedFlagDetected $event): void
    {
        $assignment = $event->assignment;

        // Ensure we only send this once
        if ($assignment->red_flag_alert_sent_at) {
            return;
        }

        // Identify managers/principals/admins to notify.
        // For now, we notify the user who assigned the form (admin/supervisor)
        // and potentially all users with 'manage educational' permissions.
        $recipients = User::whereHas('permissions', function ($q) {
            $q->where('name', 'manage educational'); // Example permission
        })->get();

        if ($recipients->isEmpty()) {
            $recipients = User::find($assignment->assigned_by);
        }

        if ($recipients) {
            Notification::send($recipients, new RedFlagAlertNotification($assignment, $event->flaggedQuestions));

            // Mark as sent
            $assignment->update(['red_flag_alert_sent_at' => now()]);
        }
    }
}
