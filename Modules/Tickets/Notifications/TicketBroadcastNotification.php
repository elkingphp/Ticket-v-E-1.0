<?php

namespace Modules\Tickets\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketBroadcastNotification extends TicketNotification
{
    public function __construct($ticket, $action, $meta = [])
    {
        parent::__construct($ticket, $action, $meta);
    }

    public function via($notifiable): array
    {
        return ['broadcast'];
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('Queued Broadcast Notification failed permanently for ticket: ' . $this->ticket->id, [
            'action' => $this->action,
            'error' => $exception->getMessage()
        ]);
    }
}
