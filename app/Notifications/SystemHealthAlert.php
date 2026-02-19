<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SystemHealthAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return $notifiable->getEnabledChannels('system_health');
    }

    public function toMail($notifiable): MailMessage
    {
        $severity = $this->data['severity'] ?? 'warning';
        $emoji = $severity === 'critical' ? '🚨' : '⚠️';

        return (new MailMessage)
            ->subject("{$emoji} System Health Alert")
            ->line($this->data['message'] ?? 'A system health issue has been detected.')
            ->line('Issue: ' . ($this->data['issue'] ?? 'Unknown'))
            ->when(isset($this->data['recommendation']), function ($mail) {
            return $mail->line('Recommendation: ' . $this->data['recommendation']);
        })
            ->action('View Dashboard', url('/dashboard'))
            ->priority($severity === 'critical' ? 'high' : 'normal');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->data['title'] ?? 'System Health Alert',
            'message' => $this->data['message'] ?? 'A system health issue has been detected',
            'action_url' => url('/dashboard'),
            'priority' => $this->data['severity'] ?? 'warning',
            'issue' => $this->data['issue'] ?? null,
            'recommendation' => $this->data['recommendation'] ?? null,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => \Str::uuid()->toString(),
            'title' => $this->data['title'] ?? 'System Health Alert',
            'message' => $this->data['message'] ?? 'A system health issue has been detected',
            'action_url' => url('/dashboard'),
            'priority' => $this->data['severity'] ?? 'warning',
            'created_at' => now()->toIso8601String(),
        ]);
    }
}