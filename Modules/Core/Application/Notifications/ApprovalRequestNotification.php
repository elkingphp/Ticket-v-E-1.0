<?php

namespace Modules\Core\Application\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Modules\Core\Domain\Models\ApprovalRequest;

class ApprovalRequestNotification extends Notification
{
    use Queueable;

    public $approvalRequest;
    public $actionType;

    /**
     * Create a new notification instance.
     *
     * @param ApprovalRequest $approvalRequest
     * @param string $actionType 'requested', 'approved', 'rejected'
     */
    public function __construct(ApprovalRequest $approvalRequest, string $actionType)
    {
        $this->approvalRequest = $approvalRequest;
        $this->actionType = $actionType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        $modelName = class_basename($this->approvalRequest->approvable_type);
        // Translate model type optionally, fallback to standard naming
        $modelType = __("core::messages.entities.{$modelName}") == "core::messages.entities.{$modelName}" ? $modelName : __("core::messages.entities.{$modelName}");
        $action = $this->approvalRequest->action;

        $model = $this->approvalRequest->approvable;
        $entityName = $model ? ($model->name ?? ($model->title ?? $this->approvalRequest->approvable_id)) : $this->approvalRequest->approvable_id;

        $variables = [
            'type' => $modelType,
            'action' => __('core::messages.actions.' . $action) == 'core::messages.actions.' . $action ? $action : __('core::messages.actions.' . $action),
            'id' => $entityName,
        ];

        $title = __('core::messages.approval_notification_title_' . $this->actionType);

        // e.g. "A new request has been made to delete the resource: TicketCategory (#5)"
        $message = __('core::messages.approval_notification_body_' . $this->actionType, $variables);

        $url = '#';

        if ($this->actionType === 'requested') {
            // Base URLs for entities
            switch ($this->approvalRequest->approvable_type) {
                case 'Modules\Tickets\Domain\Models\TicketCategory':
                    $url = route('admin.tickets.categories.index');
                    break;
                case 'Modules\Tickets\Domain\Models\TicketStage':
                    $url = route('admin.tickets.stages.index');
                    break;
                case 'Modules\Tickets\Domain\Models\TicketComplaint':
                    $url = route('admin.tickets.complaints.index');
                    break;
                case 'Modules\Tickets\Domain\Models\TicketStatus':
                    $url = route('admin.tickets.statuses.index');
                    break;
                case 'Modules\Tickets\Domain\Models\TicketPriority':
                    $url = route('admin.tickets.priorities.index');
                    break;
                case 'Modules\Tickets\Domain\Models\TicketGroup':
                    $url = route('admin.tickets.groups.index');
                    break;
            }

            if ($url !== '#') {
                $typeParam = urlencode($this->approvalRequest->approvable_type);
                $approvalId = $this->approvalRequest->approvable_id;

                $separator = str_contains($url, '?') ? '&' : '?';
                $url .= "{$separator}open_approval={$typeParam}&approval_id={$approvalId}";
            }
        }

        return [
            'title' => $title,
            'message' => $message,
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'type' => 'approval',
            'url' => $url,
            'related_id' => $this->approvalRequest->id,
            'related_type' => get_class($this->approvalRequest)
        ];
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

    protected function getIcon()
    {
        return match ($this->actionType) {
            'requested' => 'ri-question-line',
            'approved' => 'ri-check-line',
            'rejected' => 'ri-close-line',
            default => 'ri-notification-3-line',
        };
    }

    protected function getColor()
    {
        return match ($this->actionType) {
            'requested' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'primary',
        };
    }
}
