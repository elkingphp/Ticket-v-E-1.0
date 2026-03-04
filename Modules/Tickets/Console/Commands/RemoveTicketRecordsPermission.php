<?php

namespace Modules\Tickets\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RemoveTicketRecordsPermission extends Command
{
    protected $signature = 'tickets:remove-records-permission
                            {--dry-run : Preview changes without applying them}';

    protected $description = 'Completely removes all tickets.records.* permissions from the system.';

    protected array $targets = [
        'tickets.records',
        'tickets.records.view',
        'tickets.records.create',
        'tickets.records.update',
        'tickets.records.delete',
        'tickets.records.delete_requires_approval',
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
                DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();
                DB::table('model_has_permissions')->where('permission_id', $permission->id)->delete();
                $permission->forceDelete();
                $this->line("  🗑  Deleted: <comment>{$permName}</comment> + {$rolesCount} role associations");
            } else {
                $this->line("  [DRY] Would delete: <comment>{$permName}</comment> + {$rolesCount} role associations");
            }
        }

        if ($found === 0) {
            $this->info('✅ System is already clean — no tickets.records.* permissions found.');
            return Command::SUCCESS;
        }

        $this->newLine();
        if (!$isDryRun) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            $this->call('cache:clear');
            $this->info('✅ tickets.records.* permissions fully removed.');
        } else {
            $this->warn('✅ Dry run complete. Run without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }
}
