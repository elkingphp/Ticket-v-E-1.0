<?php

namespace Modules\Tickets\Services;

use Modules\Users\Domain\Models\User;
use Illuminate\Support\Collection;
use Modules\Tickets\Domain\Models\Ticket;

class TicketNotificationResolver
{
    /**
     * Resolve the users who should receive the notification.
     *
     * @param Ticket $ticket
     * @param string $action
     * @param array $meta
     * @return Collection
     */
    public function resolve(Ticket $ticket, string $action, array $meta = []): Collection
    {
        $recipients = collect();
        $actorId = $meta['actor_id'] ?? auth()->id();

        switch ($action) {
            case 'created':
                // 1. Members of the assigned group
                if ($ticket->assigned_group_id) {
                    $groupMembers = User::whereHas('supportGroups', function ($query) use ($ticket) {
                        $query->where('tickets.ticket_groups.id', $ticket->assigned_group_id);
                    })->get();
                    $recipients = $recipients->merge($groupMembers);
                }

                // 2. Notify Admis defined in the settings
                $settingRepo = app(\Modules\Settings\Domain\Interfaces\SettingRepositoryInterface::class);
                $rolesJson = $settingRepo->getByKey('tickets_notification_roles', '["super-admin"]');
                $notificationRoles = json_decode($rolesJson, true) ?? ['super-admin'];

                // Get roles that actually exist in the DB to avoid RoleDoesNotExist exception
                $existingRoles = \Modules\Users\Domain\Models\Role::whereIn('name', $notificationRoles)->pluck('name')->toArray();

                if (!empty($existingRoles)) {
                    $admins = User::role($existingRoles)->get();
                    $recipients = $recipients->merge($admins);
                }
                break;

            case 'assigned':
                // The person it was assigned to
                if ($ticket->assigned_to) {
                    $agent = User::find($ticket->assigned_to);
                    if ($agent) {
                        $recipients->push($agent);
                    }
                }
                break;

            case 'status_changed':
            case 'replied':
            case 'closed':
                // 1. The assigned agent
                if ($ticket->assigned_to) {
                    $agent = User::find($ticket->assigned_to);
                    if ($agent)
                        $recipients->push($agent);
                }
                // 2. Members of the assigned group (if no specific agent is assigned)
                elseif ($ticket->assigned_group_id) {
                    $groupMembers = User::whereHas('supportGroups', function ($query) use ($ticket) {
                        $query->where('tickets.ticket_groups.id', $ticket->assigned_group_id);
                    })->get();
                    $recipients = $recipients->merge($groupMembers);
                }

                // 3. Fallback: If no single agent and no group is assigned, OR the group has 0 members, notify default roles
                if ($recipients->isEmpty()) {
                    $settingRepo = app(\Modules\Settings\Domain\Interfaces\SettingRepositoryInterface::class);
                    $rolesJson = $settingRepo->getByKey('tickets_notification_roles', '["super-admin"]');
                    $notificationRoles = json_decode($rolesJson, true) ?? ['super-admin'];

                    $existingRoles = \Modules\Users\Domain\Models\Role::whereIn('name', $notificationRoles)->pluck('name')->toArray();

                    if (!empty($existingRoles)) {
                        $admins = User::role($existingRoles)->get();
                        $recipients = $recipients->merge($admins);
                    }
                }

                // 4. The owner of the ticket (unless they are the actor or the reply is an internal note)
                $isInternal = $meta['is_internal'] ?? false;
                if ($ticket->user_id && $ticket->user_id != $actorId && !$isInternal) {
                    $owner = User::find($ticket->user_id);
                    if ($owner)
                        $recipients->push($owner);
                }
                break;
        }

        // Always remove the actor from recipients to avoid notifying oneself
        return $recipients->unique('id')->reject(fn($user) => $user->id === $actorId);
    }
}
