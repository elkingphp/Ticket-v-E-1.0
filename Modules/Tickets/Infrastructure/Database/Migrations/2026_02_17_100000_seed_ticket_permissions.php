<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    public function up(): void
    {
        $permissions = [
            'tickets.view',
            'tickets.create',
            'tickets.manage',
            'tickets.lookups',
            'tickets.settings',
            'tickets.routing',
            'tickets.templates.manage',
        ];

        foreach ($permissions as $per) {
            Permission::findOrCreate($per, 'web');
        }

        // Assign to Admin
        try {
            /** @var Role $adminRole */
            $adminRole = Role::findByName('admin', 'web');
            $adminRole->givePermissionTo($permissions);
        } catch (\Exception $e) {
            // Role might not exist in some environments
        }

        // Assign to Agent
        try {
            /** @var Role $agentRole */
            $agentRole = Role::findByName('agent', 'web');
            $agentRole->givePermissionTo([
                'tickets.view',
                'tickets.manage',
            ]);
        } catch (\Exception $e) {
            // Role might not exist
        }
    }

    public function down(): void
    {
        // Optional: Remove permissions
    }
};
