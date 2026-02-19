<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class UserRegisteredAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return $notifiable->getEnabledChannels('user_registered');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('👤 New User Registration')
            ->line('A new user has registered on the platform.')
            ->line('User: ' . ($this->data['user_name'] ?? 'Unknown'))
            ->line('Email: ' . ($this->data['user_email'] ?? 'Unknown'))
            ->action('View User', $this->data['action_url'] ?? url('/users'));
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'New User Registered',
            'message' => ($this->data['user_name'] ?? 'A user') . ' has registered on the platform',
            'action_url' => $this->data['action_url'] ?? url('/users'),
            'priority' => 'info',
            'user_id' => $this->data['user_id'] ?? null,
            'user_name' => $this->data['user_name'] ?? null,
            'user_email' => $this->data['user_email'] ?? null,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => Str::uuid()->toString(),
            'title' => 'New User Registered',
            'message' => ($this->data['user_name'] ?? 'A user') . ' has registered',
            'action_url' => $this->data['action_url'] ?? url('/users'),
            'priority' => 'info',
            'created_at' => now()->toIso8601String(),
        ]);
    }
}