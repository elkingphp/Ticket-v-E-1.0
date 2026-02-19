<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Notifications\DatabaseNotification;

class NewNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;
    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(DatabaseNotification $notification, int $userId)
    {
        $this->notification = $notification;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notification.new';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $avatar = $this->notification->data['avatar'] ?? null;

        if ($avatar && !str_starts_with($avatar, 'http')) {
            $avatar = asset($avatar);
        }

        $avatar = $avatar ?: asset('assets/images/users/user-dummy-img.jpg');

        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->data['title'] ?? 'System Notification',
            'message' => $this->notification->data['message'] ?? '',
            'priority' => $this->notification->data['priority'] ?? 'info',
            'avatar' => $avatar,
            'created_at' => $this->notification->created_at->toIso8601String(),
            'created_at_human' => $this->notification->created_at->diffForHumans(),
            'action_url' => $this->notification->data['action_url'] ?? '#',
        ];
    }
}