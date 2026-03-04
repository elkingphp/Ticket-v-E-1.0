<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Domain\Models\Permission;
use Modules\Users\Domain\Models\Role;

return new class extends Migration {
    public function up(): void
    {
        $permissions = [
            // Helpdesk (Agent Desk) - Granular permissions
            'tickets.view_desk',                   // View helpdesk list only
            'tickets.reply',                       // Reply, change status/priority, self-assign
            'tickets.distribute',                  // Assign to group or other agent
            'tickets.delete_requires_approval',    // Submit delete request (needs approval)
            'tickets.bulk_close',                  // Bulk close with unified reply
            // Stages
            'tickets.stages.view',
            'tickets.stages.create',
            'tickets.stages.update',
            'tickets.stages.delete_requires_approval',
            'tickets.stages.delete',
            // Categories
            'tickets.categories.view',
            'tickets.categories.create',
            'tickets.categories.update',
            'tickets.categories.delete_requires_approval',
            'tickets.categories.delete',
            // Complaints
            'tickets.complaints.view',
            'tickets.complaints.create',
            'tickets.complaints.update',
            'tickets.complaints.delete_requires_approval',
            'tickets.complaints.delete',
            // Statuses
            'tickets.statuses.view',
            'tickets.statuses.create',
            'tickets.statuses.update',
            'tickets.statuses.delete_requires_approval',
            'tickets.statuses.delete',
            // Priorities
            'tickets.priorities.view',
            'tickets.priorities.create',
            'tickets.priorities.update',
            'tickets.priorities.delete_requires_approval',
            'tickets.priorities.delete',
            // Support Groups
            'tickets.groups.view',
            'tickets.groups.create',
            'tickets.groups.update',
            'tickets.groups.delete_requires_approval',
            'tickets.groups.delete',
            // Delete Requests (Admin review workflow)
            'tickets.delete_requests.manage',
            // Email Templates
            'tickets.templates.manage',
        ];

        foreach ($permissions as $p) {
            Permission::findOrCreate($p, 'web', 'Tickets');
        }

        // Remove all old/deprecated permissions safely
        $old_permissions = [
            'tickets.view',
            'tickets.manage',
            'tickets.lookups',
            'tickets.routing',
            'tickets.settings',
            'tickets.settings.view',
            'tickets.settings.create',
            'tickets.settings.update',
            'tickets.settings.delete',
            'tickets.settings.delete_requires_approval',
            'tickets.records.view',
            'tickets.records.create',
            'tickets.records.update',
            'tickets.records.delete',
            'tickets.records.delete_requires_approval',
            'tickets.stages.manage',
            'tickets.categories.manage',
            'tickets.complaints.manage',
            'tickets.statuses.manage',
            'tickets.priorities.manage',
            'tickets.groups.manage',
        ];

        foreach ($old_permissions as $old) {
            /** @var Permission|null $p */
            $p = Permission::where('name', $old)->first();
            if ($p) {
                \Illuminate\Support\Facades\DB::table('model_has_permissions')->where('permission_id', $p->id)->delete();
                \Illuminate\Support\Facades\DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
                $p->delete();
            }
        }

        // Assign all Tickets module permissions to super-admin
        /** @var Role|null $superAdmin */
        $superAdmin = Role::where('name', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(
                Permission::where('module', 'Tickets')->pluck('name')->toArray()
            );
        }
    }

    public function down(): void
    {
        // Reversal not required for permission refactoring migrations
    }
};
