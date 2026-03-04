<?php

namespace Modules\Tickets\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketMailNotification extends TicketNotification implements ShouldQueue
{
    use Queueable;

    // Retry configuration
    public $tries = 3;
    public $maxExceptions = 2;
    public $backoff = [30, 60, 120];

    public function __construct($ticket, $action, $meta = [])
    {
        parent::__construct($ticket, $action, $meta);
        $this->queue = 'notifications-mail';
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('Queued Mail Notification failed permanently for ticket: ' . $this->ticket->id, [
            'action' => $this->action,
            'error' => $exception->getMessage()
        ]);
    }
}
