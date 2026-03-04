<?php

namespace Modules\Tickets\Services;

use Illuminate\Support\Facades\Log;
use Modules\Tickets\Domain\Models\Ticket;
use Modules\Tickets\Notifications\TicketDatabaseNotification;
use Modules\Tickets\Notifications\TicketBroadcastNotification;
use Modules\Tickets\Notifications\TicketMailNotification;

class TicketNotificationDispatcher
{
    /**
     * Dispatch notifications to a collection of recipients through their preferred channels.
     * Extracts latency out of the synchronous event cycle.
     */
    public function dispatchToRecipients($recipients, Ticket $ticket, string $action, array $meta = [])
    {
        // Chunk processing to avoid memory issues for large number of recipients
        $recipients->chunk(50)->each(function ($chunk) use ($ticket, $action, $meta) {
            foreach ($chunk as $recipient) {
                $this->dispatch($recipient, $ticket, $action, $meta);
            }
        });
    }

    /**
     * Dispatch channel-specific notification jobs to a single recipient.
     */
    public function dispatch($recipient, Ticket $ticket, string $action, array $meta = [])
    {
        $eventType = ($action === 'created') ? 'ticket_created' : 'ticket_updated';

        // Respect user preferences
        $userChannels = method_exists($recipient, 'getEnabledChannels')
            ? $recipient->getEnabledChannels($eventType)
            : ['database', 'broadcast', 'mail'];

        // Ensure core channels (DB, Broadcast) are always responsive despite preferences
        $channels = ['database', 'broadcast'];
        foreach ($userChannels as $channel) {
            if (!in_array($channel, $channels)) {
                $channels[] = $channel;
            }
        }

        // 1. Database Channel
        if (in_array('database', $channels)) {
            try {
                // Sent to notifications-db queue
                $recipient->notify(new TicketDatabaseNotification($ticket, $action, $meta));
            } catch (\Throwable $e) {
                Log::error('Ticket Database Notification dispatch failed', ['user_id' => $recipient->id, 'error' => $e->getMessage()]);
            }
        }

        // 2. Broadcast Channel
        if (in_array('broadcast', $channels)) {
            try {
                // Sent to notifications-bcast queue
                $recipient->notify(new TicketBroadcastNotification($ticket, $action, $meta));
            } catch (\Throwable $e) {
                Log::error('Ticket Broadcast Notification dispatch failed', ['user_id' => $recipient->id, 'error' => $e->getMessage()]);
            }
        }

        // 3. Mail Channel
        if (in_array('mail', $channels)) {
            try {
                // Sent to notifications-mail queue with a slight delay to prioritize DB/Broadcast workers
                $mailNotification = (new TicketMailNotification($ticket, $action, $meta))
                    ->delay(now()->addSeconds(5));
                $recipient->notify($mailNotification);
            } catch (\Throwable $e) {
                Log::error('Ticket Mail Notification dispatch failed', ['user_id' => $recipient->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
