<?php

namespace Modules\Tickets\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\Tickets\Events\TicketActionEvent;
use Modules\Tickets\Services\TicketNotificationDispatcher;
use Modules\Tickets\Services\TicketNotificationResolver;

class TicketActionSubscriber
{
    protected $resolver;
    protected $dispatcher;

    public function __construct(TicketNotificationResolver $resolver, TicketNotificationDispatcher $dispatcher)
    {
        $this->resolver = $resolver;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handle ticket action events.
     */
    public function handleTicketAction(TicketActionEvent $event)
    {
        // 1. Resolve who should receive notifications
        $meta = array_merge($event->meta, [
            'actor_id' => $event->actor ? $event->actor->id : null,
            'actor_name' => $event->actor ? $event->actor->full_name : __('tickets::messages.system')
        ]);

        $recipients = $this->resolver->resolve($event->ticket, $event->action, $meta);

        // 2. Dispatch notifications appropriately respecting channels and error isolation
        if ($recipients->isNotEmpty()) {
            $this->dispatcher->dispatchToRecipients($recipients, $event->ticket, $event->action, $meta);
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            TicketActionEvent::class,
            [TicketActionSubscriber::class, 'handleTicketAction']
        );
    }
}
