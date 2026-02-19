<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class GenericAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $eventType;
    protected array $data;

    public function __construct(string $eventType, array $data)
    {
        $this->eventType = $eventType;
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        if (method_exists($notifiable, 'getEnabledChannels')) {
            return $notifiable->getEnabledChannels($this->eventType);
        }

        return ['database'];
    }


    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->data['title'] ?? 'System Notification')
            ->line($this->data['message'] ?? 'You have a new notification.')
            ->when(isset($this->data['action_url']), function ($mail) {
                return $mail->action('View Details', $this->data['action_url']);
            });
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->data['title'] ?? 'Notification',
            'message' => $this->data['message'] ?? 'You have a new notification',
            'action_url' => $this->data['action_url'] ?? url('/dashboard'),
            'priority' => $this->data['priority'] ?? 'info',
            'event_type' => $this->eventType,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => \Str::uuid()->toString(),
            'title' => $this->data['title'] ?? 'Notification',
            'message' => $this->data['message'] ?? 'You have a new notification',
            'action_url' => $this->data['action_url'] ?? url('/dashboard'),
            'priority' => $this->data['priority'] ?? 'info',
            'created_at' => now()->toIso8601String(),
        ]);
    }
}