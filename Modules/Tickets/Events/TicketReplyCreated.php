<?php

namespace Modules\Tickets\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Tickets\Domain\Models\Ticket;
use Modules\Tickets\Domain\Models\TicketThread;

class TicketReplyCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;
    public $thread;

    /**
     * Create a new event instance.
     */
    public function __construct(Ticket $ticket, TicketThread $thread)
    {
        $this->ticket = $ticket;
        $this->thread = $thread;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
