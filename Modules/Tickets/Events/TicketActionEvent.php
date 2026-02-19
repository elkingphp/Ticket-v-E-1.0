<?php

namespace Modules\Tickets\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Tickets\Domain\Models\Ticket;

class TicketActionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;
    public $action;
    public $meta;
    public $actor;

    /**
     * Create a new event instance.
     *
     * @param Ticket $ticket
     * @param string $action
     * @param array $meta
     * @param mixed $actor
     */
    public function __construct(Ticket $ticket, string $action, array $meta = [], $actor = null)
    {
        $this->ticket = $ticket;
        $this->action = $action;
        $this->meta = $meta;
        $this->actor = $actor ?: auth()->user();
    }
}
