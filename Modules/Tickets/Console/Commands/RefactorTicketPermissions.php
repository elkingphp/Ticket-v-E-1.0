<?php

namespace Modules\Tickets\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class RefactorTicketPermissions extends Command
{
    protected $signature = 'tickets:refactor-permissions
                            {--dry-run : Preview changes without applying them}';

    protected $description = 'Refactors old ticket permissions to the new granular structure.';

    /**
     * Mapping: old permission name => new permission name(s)
     * Each old permission maps to multiple new ones (all roles that had old will get all new)
     */
    protected array $mapping = [
        'tickets.view' => ['tickets.view_desk'],
        'tickets.create' => ['tickets.create', 'tickets.view_desk'],
        'tickets.manage' => ['tickets.view_desk', 'tickets.reply'],
        'tickets.lookups' => [],   // No equivalent - purely UI concept now
        'tickets.routing' => [],   // No equivalent - merged into admin area
        'tickets.settings' => [],   // Removed - redundant with tickets.settings.view
    ];

    /**
     * New permissions to always create (with guard web)
     */
    protected array $newPermissions = [
        'tickets.view_desk',
        'tickets.reply',
        'tickets.distribute',
        'tickets.delete_requires_approval',
        'tickets.bulk_close',
    ];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('🔍 DRY RUN - No changes will be applied.');
            $this->newLine();
        }

        $this->info('=== Step 1: Creating new permissions ===');
        foreach ($this->newPermissions as $permName) {
            $exists = Permission::where('name', $permName)->exists();
            if ($exists) {
                $this->line("  ⏭  Already exists: <comment>{$permName}</comment>");
                // Fix module if it was incorrectly set to something other than 'Tickets'
                Permission::where('name', $permName)->where('module', '!=', 'Tickets')->update(['module' => 'Tickets']);
            } else {
                if (!$isDryRun) {
                    Permission::create(['name' => $permName, 'guard_name' => 'web', 'module' => 'Tickets']);
                }
                $this->line("  ✅ Created: <info>{$permName}</info>");
            }
        }

        $this->newLine();
        $this->info('=== Step 2: Migrating roles from old to new permissions ===');

        foreach ($this->mapping as $oldName => $newNames) {
            /** @var Permission $oldPerm */
            $oldPerm = Permission::where('name', $oldName)->first();

            if (!$oldPerm) {
                $this->line("  ⏭  Old permission not found (skip): <comment>{$oldName}</comment>");
                continue;
            }

            $roles = $oldPerm->roles;

            if ($roles->isEmpty()) {
                $this->line("  ⚠  No roles assigned to: <comment>{$oldName}</comment>");
            } else {
                foreach ($roles as $role) {
                    foreach ($newNames as $newName) {
                        $newPerm = Permission::where('name', $newName)->first();
                        if ($newPerm && !$role->hasPermissionTo($newPerm)) {
                            if (!$isDryRun) {
                                $role->givePermissionTo($newPerm);
                            }
                            $this->line("  ✅ Role '<info>{$role->name}</info>': <comment>{$oldName}</comment> → <info>{$newName}</info>");
                        } elseif ($newPerm) {
                            $this->line("  ⏭  Role '<info>{$role->name}</info>' already has: <comment>{$newName}</comment>");
                        }
                    }
                }
            }

            // Delete old permission
            if (!$isDryRun) {
                $oldPerm->delete();
            }
            $this->line("  🗑  Removed old permission: <comment>{$oldName}</comment>");
        }

        $this->newLine();
        $this->info('=== Step 3: Clearing permission cache ===');
        if (!$isDryRun) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            $this->call('cache:clear');
        }
        $this->line('  ✅ Cache cleared.');

        $this->newLine();
        if ($isDryRun) {
            $this->warn('✅ Dry run complete. Run without --dry-run to apply changes.');
        } else {
            $this->info('✅ Permission refactoring complete!');
            $this->table(
                ['New Permission', 'Description'],
                [
                    ['tickets.view_desk', 'View helpdesk page only (no ticket details)'],
                    ['tickets.reply', 'Reply, change status/priority, self-assign'],
                    ['tickets.distribute', 'Assign tickets to others / groups'],
                    ['tickets.delete_requires_approval', 'Submit ticket deletion requests'],
                    ['tickets.bulk_close', 'Bulk close tickets with unified reply'],
                ]
            );
        }

        return Command::SUCCESS;
    }
}
