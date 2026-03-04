<?php

namespace Modules\Tickets\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RemoveTicketSettingsPermission extends Command
{
    protected $signature = 'tickets:remove-settings-permission
                            {--dry-run : Preview changes without applying them}';

    protected $description = 'Completely removes all tickets.settings.* permissions from the system.';

    /**
     * All tickets.settings.* permission names to purge
     */
    protected array $targets = [
        'tickets.settings',
        'tickets.settings.view',
        'tickets.settings.create',
        'tickets.settings.update',
        'tickets.settings.delete',
        'tickets.settings.delete_requires_approval',
    ];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('🔍 DRY RUN — No changes will be applied.');
            $this->newLine();
        }

        $found = 0;

        foreach ($this->targets as $permName) {
            /** @var Permission|null $permission */
            $permission = Permission::where('name', $permName)->first();

            if (!$permission) {
                $this->line("  ⏭  Not found (already clean): <comment>{$permName}</comment>");
                continue;
            }

            $found++;
            $rolesCount = DB::table('role_has_permissions')
                ->where('permission_id', $permission->id)
                ->count();

            $this->line("  🎯 Found: <info>{$permName}</info> (id={$permission->id}, attached to {$rolesCount} roles)");

            if (!$isDryRun) {
                // 1. Remove from role_has_permissions
                DB::table('role_has_permissions')
                    ->where('permission_id', $permission->id)
                    ->delete();

                // 2. Remove from model_has_permissions (user-direct assignments)
                DB::table('model_has_permissions')
                    ->where('permission_id', $permission->id)
                    ->delete();

                // 3. Delete the permission record
                $permission->forceDelete();

                $this->line("  🗑  Deleted: <comment>{$permName}</comment> + {$rolesCount} role associations");
            } else {
                $this->line("  [DRY] Would delete: <comment>{$permName}</comment> + {$rolesCount} role associations");
            }
        }

        if ($found === 0) {
            $this->info('✅ System is already clean — no tickets.settings.* permissions found.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info('=== Clearing permission cache ===');
        if (!$isDryRun) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            $this->call('cache:clear');
            $this->info('✅ tickets.settings.* permissions fully removed from the system.');
        } else {
            $this->warn('✅ Dry run complete. Run without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }
}
