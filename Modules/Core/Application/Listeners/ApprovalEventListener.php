<?php

namespace Modules\Core\Application\Listeners;

use Modules\Core\Domain\Events\ApprovalRequested;
use Modules\Core\Domain\Events\ApprovalApproved;
use Modules\Core\Domain\Events\ApprovalRejected;

class ApprovalEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        // Dependencies like NotificationService or ActivityService can be injected here
    }

    public function handleRequested(ApprovalRequested $event)
    {
        $request = $event->approvalRequest;

        $roles = get_setting('approval_notification_roles', []);
        if (is_string($roles)) {
            $roles = json_decode($roles, true) ?? [];
        }

        if (!empty($roles)) {
            $roleIds = array_filter($roles, 'is_numeric');
            $roleNames = array_filter($roles, fn($val) => !is_numeric($val));

            $users = \Modules\Users\Domain\Models\User::whereHas('roles', function ($q) use ($roleIds, $roleNames) {
                $q->where(function ($query) use ($roleIds, $roleNames) {
                    if (!empty($roleIds)) {
                        $query->whereIn('id', $roleIds);
                    }
                    if (!empty($roleNames)) {
                        $query->orWhereIn('name', $roleNames);
                    }
                });
            })->where('id', '!=', $request->requested_by)->get();

            foreach ($users as $user) {
                try {
                    $user->notify(new \Modules\Core\Application\Notifications\ApprovalRequestNotification($request, 'requested'));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send approval request notification to user {$user->id}: " . $e->getMessage());
                }
            }
        }
    }

    public function handleApproved(ApprovalApproved $event)
    {
        $request = $event->approvalRequest;

        $requester = $request->requester;
        if ($requester) {
            try {
                $requester->notify(new \Modules\Core\Application\Notifications\ApprovalRequestNotification($request, 'approved'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send approval approved notification to user {$requester->id}: " . $e->getMessage());
            }
        }
    }

    public function handleRejected(ApprovalRejected $event)
    {
        $request = $event->approvalRequest;

        $requester = $request->requester;
        if ($requester) {
            try {
                $requester->notify(new \Modules\Core\Application\Notifications\ApprovalRequestNotification($request, 'rejected'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send approval rejected notification to user {$requester->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array
     */
    public function subscribe($events)
    {
        return [
            ApprovalRequested::class => 'handleRequested',
            ApprovalApproved::class => 'handleApproved',
            ApprovalRejected::class => 'handleRejected',
        ];
    }
}
