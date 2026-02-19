<?php

namespace App\Observers;

use Illuminate\Notifications\DatabaseNotification;
use App\Events\NewNotification;

class NotificationObserver
{
    /**
     * Handle events after the database transaction is committed.
     * 
     * @var bool
     */
    public $afterCommit = true;

    /**
     * Handle the DatabaseNotification "created" event.
     */
    public function created(DatabaseNotification $notification): void
    {
        // Broadcast new notification to user via WebSocket
        // Check for both direct class name and morph map 'user'
        if ($notification->notifiable_type === 'Modules\\Users\\Domain\\Models\\User' || $notification->notifiable_type === 'user') {
            broadcast(new NewNotification($notification, $notification->notifiable_id));

            // Increment unread counter
            $notification->notifiable()->increment('unread_notifications_count');
        }
    }

    /**
     * Handle the DatabaseNotification "updated" event.
     */
    public function updated(DatabaseNotification $notification): void
    {
        // If notification marked as read (read_at changed from null to timestamp)
        if ($notification->wasChanged('read_at') && $notification->read_at !== null) {
            $user = $notification->notifiable;
            if ($user && $user->unread_notifications_count > 0) {
                $user->decrement('unread_notifications_count');
            }
        }
    }

    /**
     * Handle the DatabaseNotification "deleted" event.
     */
    public function deleted(DatabaseNotification $notification): void
    {
        // If an unread notification is deleted, decrement the counter
        if ($notification->read_at === null) {
            $user = $notification->notifiable;
            if ($user && $user->unread_notifications_count > 0) {
                $user->decrement('unread_notifications_count');
            }
        }
    }
}