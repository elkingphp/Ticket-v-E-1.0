<?php

namespace Modules\Core\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Modules\Users\Domain\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions grouped by module
        $permissionsByModule = [
            'Core' => [
                'settings.view',
                'settings.manage',
                'audit.view',
                'profile.update',
                'analytics.view',
                'integrity_widget.view',
            ],
            'Users' => [
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',
                'roles.view',
                'roles.manage',
                'permissions.view',
                'permissions.manage',
            ],
            'Settings' => [
                'settings.view',
                'settings.manage',
            ],
        ];

        // Create Permissions
        foreach ($permissionsByModule as $module => $permissions) {
            foreach ($permissions as $permission) {
                Permission::updateOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['module' => $module]
                );
            }
        }

        // Create Roles and Assign Permissions

        // Super Admin: Dynamic Sync - Always gets ALL permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);

        // Editor Role
        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editorRole->syncPermissions([
            'users.view',
            'users.create',
            'users.edit',
            'roles.view',
            'settings.view',
            'audit.view',
            'profile.update',
            'analytics.view',
        ]);

        // Regular User Role
        $regularUserRole = Role::firstOrCreate(['name' => 'regular-user', 'guard_name' => 'web']);
        $regularUserRole->syncPermissions([
            'profile.update',
        ]);

        // Admin Role (Compatible with existing system)
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions([
            'users.view',
            'users.create',
            'users.edit',
            'roles.view',
            'settings.view',
        ]);
    }
}