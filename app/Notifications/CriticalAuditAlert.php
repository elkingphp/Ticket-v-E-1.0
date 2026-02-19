<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CriticalAuditAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        // استخدام تفضيلات المستخدم
        return $notifiable->getEnabledChannels('audit_critical');
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🚨 Critical Audit Event Detected')
            ->line($this->data['message'] ?? 'A critical audit event has been detected.')
            ->action('View Details', $this->data['action_url'] ?? url('/audit'))
            ->line('Please review this event immediately.')
            ->priority('high');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => $this->data['title'] ?? 'Critical Audit Event',
            'message' => $this->data['message'] ?? 'A critical event has been detected',
            'action_url' => $this->data['action_url'] ?? url('/audit'),
            'priority' => 'critical',
            'audit_id' => $this->data['audit_id'] ?? null,
            'event' => $this->data['event'] ?? null,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => \Str::uuid()->toString(),
            'title' => $this->data['title'] ?? 'Critical Audit Event',
            'message' => $this->data['message'] ?? 'A critical event has been detected',
            'action_url' => $this->data['action_url'] ?? url('/audit'),
            'priority' => 'critical',
            'created_at' => now()->toIso8601String(),
        ]);
    }
}