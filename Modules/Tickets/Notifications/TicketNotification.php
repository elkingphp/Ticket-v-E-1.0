<?php

namespace Modules\Tickets\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Modules\Tickets\Domain\Models\Ticket;

class TicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $ticket;
    public $action;
    public $meta;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, string $action, array $meta = [])
    {
        $this->ticket = $ticket;
        $this->action = $action;
        $this->meta = $meta;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $eventType = ($this->action === 'created') ? 'ticket_created' : 'ticket_updated';

        // Get user preferences
        $channels = $notifiable->getEnabledChannels($eventType);

        // Always ensure broadcast and database are available for UI reactivity if preferred
        if (!in_array('database', $channels))
            $channels[] = 'database';
        if (!in_array('broadcast', $channels))
            $channels[] = 'broadcast';

        return $channels;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_uuid' => $this->ticket->uuid,
            'ticket_number' => $this->ticket->ticket_number,
            'action' => $this->action,
            'title' => $this->resolveTitle(),
            'message' => $this->resolveMessage(),
            'icon' => $this->resolveIcon(),
            'category' => $this->resolveCategory(),
            'module' => 'Tickets',
            'color' => $this->resolveColor(),
            'url' => route('agent.tickets.show', $this->ticket->uuid),
        ];
    }

    protected function resolveCategory(): string
    {
        $actor = $this->meta['actor_name'] ?? 'System';
        if ($actor === 'System' || $actor === __('tickets::messages.system')) {
            return 'system';
        }

        return 'user';
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $title = $this->resolveTitle();
        $message = $this->resolveMessage();
        $url = route('agent.tickets.show', $this->ticket->uuid);

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject($title)
            ->greeting(app()->getLocale() == 'ar' ? 'مرحبا!' : 'Hello!')
            ->line($message)
            ->action(app()->getLocale() == 'ar' ? 'عرض التذكرة' : 'View Ticket', $url)
            ->line(app()->getLocale() == 'ar' ? 'شكرا لاستخدامك نظامنا!' : 'Thank you for using our system!');
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastType(): string
    {
        return 'notification.new';
    }

    protected function resolveTitle(): string
    {
        return match ($this->action) {
            'created' => __('tickets::messages.create_ticket'),
            'status_changed' => __('tickets::messages.status'),
            'assigned' => __('tickets::messages.lookups.assigned_to'),
            'replied' => __('tickets::messages.add_reply'),
            'closed' => __('tickets::messages.close_ticket'),
            default => 'Notification',
        };
    }

    protected function resolveMessage(): string
    {
        $actor = $this->meta['actor_name'] ?? __('tickets::messages.system');
        $params = ['actor' => $actor];

        if (isset($this->meta['new_status_name'])) {
            $params['status'] = $this->meta['new_status_name'];
        }

        return __('tickets::messages.notifications.' . $this->action, $params);
    }

    protected function resolveIcon(): string
    {
        return match ($this->action) {
            'created' => 'ri-add-circle-line',
            'status_changed' => 'ri-checkbox-circle-line',
            'assigned' => 'ri-user-received-line',
            'replied' => 'ri-chat-1-line',
            'closed' => 'ri-lock-line',
            default => 'ri-notification-3-line',
        };
    }

    protected function resolveColor(): string
    {
        return match ($this->action) {
            'created' => 'success',
            'status_changed' => 'primary',
            'assigned' => 'info',
            'replied' => 'warning',
            'closed' => 'danger',
            default => 'primary',
        };
    }
}
