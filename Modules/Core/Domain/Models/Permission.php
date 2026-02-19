<?php

namespace Modules\Core\Domain\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'guard_name',
        'module',
    ];

    /**
     * Scope a query to only include permissions of a given module.
     */
    public function scopeOfModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Override findOrCreate to support the module column.
     */
    public static function findOrCreate(string $name, $guardName = null, string $module = 'Core'): SpatiePermission
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        $permission = static::where('name', $name)
            ->where('guard_name', $guardName)
            ->first();

        if (!$permission) {
            return static::create([
                'name' => $name,
                'guard_name' => $guardName,
                'module' => $module,
            ]);
        }

        return $permission;
    }
}