<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ThresholdExceededAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return $notifiable->getEnabledChannels('threshold_exceeded');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Alert Threshold Exceeded')
            ->line($this->data['message'] ?? 'An alert threshold has been exceeded.')
            ->line('Event Type: ' . ($this->data['event_type'] ?? 'Unknown'))
            ->line('Count: ' . ($this->data['count'] ?? 'Unknown'))
            ->action('View Dashboard', url('/dashboard'))
            ->priority('high');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Threshold Exceeded',
            'message' => $this->data['message'] ?? 'An alert threshold has been exceeded',
            'action_url' => url('/dashboard'),
            'priority' => $this->data['severity'] ?? 'warning',
            'event_type' => $this->data['event_type'] ?? null,
            'count' => $this->data['count'] ?? null,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => Str::uuid()->toString(),
            'title' => 'Threshold Exceeded',
            'message' => $this->data['message'] ?? 'An alert threshold has been exceeded',
            'action_url' => url('/dashboard'),
            'priority' => $this->data['severity'] ?? 'warning',
            'created_at' => now()->toIso8601String(),
        ]);
    }
}