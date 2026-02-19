<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class FallbackNotification extends Notification
{
    use Queueable;

    protected string $eventType;
    protected array $data;
    protected \Exception $exception;

    public function __construct(string $eventType, array $data, \Exception $exception)
    {
        $this->eventType = $eventType;
        $this->data = $data;
        $this->exception = $exception;
    }

    public function via($notifiable): array
    {
        return ['slack'];
    }

    public function toSlack($notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->error()
            ->content('🚨 Notification System Failure')
            ->attachment(function ($attachment) {
            $attachment->title('Failed Notification Details')
                ->fields([
                'Event Type' => $this->eventType,
                'Error' => $this->exception->getMessage(),
                'Time' => now()->toDateTimeString(),
            ]);
        });
    }
}