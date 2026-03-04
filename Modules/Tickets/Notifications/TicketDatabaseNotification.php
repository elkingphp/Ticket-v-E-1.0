<?php

namespace Modules\Tickets\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketDatabaseNotification extends TicketNotification
{
    public function __construct($ticket, $action, $meta = [])
    {
        parent::__construct($ticket, $action, $meta);
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('Queued Database Notification failed permanently for ticket: ' . $this->ticket->id, [
            'action' => $this->action,
            'error' => $exception->getMessage()
        ]);
    }
}
