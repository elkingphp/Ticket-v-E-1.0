<?php

namespace Modules\Core\Application\Services;

use Modules\Core\Domain\Models\Permission;
use Illuminate\Support\Collection;

class ModulePermissionService extends BaseService
{
    /**
     * Get all permissions grouped by module.
     */
    public function getPermissionsGroupedByModule(): Collection
    {
        return Permission::all()->groupBy('module');
    }

    /**
     * Delete all permissions for a specific module.
     */
    public function deletePermissionsByModule(string $module): void
    {
        Permission::where('module', $module)->delete();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Sync permissions for a module.
     * Use this in module seeders or during installation.
     */
    public function syncModulePermissions(string $module, array $permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web', $module);
        }
    }
}