<?php

namespace Modules\Tickets\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Events\Dispatcher;
use Modules\Tickets\Events\TicketCreated;
use Modules\Tickets\Events\TicketReplyCreated;
use Modules\Tickets\Events\TicketStatusChanged;
use Modules\Tickets\Application\Services\Email\TicketEmailService;

class SendTicketNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    protected $emailService;

    /**
     * Create the event listener.
     */
    public function __construct(TicketEmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Handle Ticket Created.
     */
    public function handleTicketCreated(TicketCreated $event): void
    {
        // Notify Customer (Confirmation)
        $this->emailService->notifyCustomer('ticket_created', $event->ticket);

        // Notify Agents (New Assignment)
        $this->emailService->notifyAgents('agent_ticket_assigned', $event->ticket);
    }

    /**
     * Handle Ticket Reply.
     */
    public function handleTicketReplyCreated(TicketReplyCreated $event): void
    {
        // Only notify if the reply is not internal
        if (!$event->thread->is_internal) {

            // If the replier is the customer, notify agents
            if ($event->thread->user_id === $event->ticket->user_id) {
                $this->emailService->notifyAgents('agent_customer_reply', $event->ticket, $event->thread);
            }
            // If the replier is someone else (Agent), notify the customer
            else {
                $this->emailService->notifyCustomer('ticket_replied', $event->ticket, $event->thread);
            }
        }
    }

    /**
     * Handle Status Changed.
     */
    public function handleTicketStatusChanged(TicketStatusChanged $event): void
    {
        $this->emailService->notifyCustomer('ticket_status_changed', $event->ticket);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            TicketCreated::class => 'handleTicketCreated',
            TicketReplyCreated::class => 'handleTicketReplyCreated',
            TicketStatusChanged::class => 'handleTicketStatusChanged',
        ];

        // Alternatively, define individual listen methods if not using subscribe.
        // But subscribe is cleaner here.
    }
}
