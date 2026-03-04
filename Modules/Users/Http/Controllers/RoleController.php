<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Users\Domain\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:roles.view', only: ['index', 'show']),
            new Middleware('permission:roles.manage', only: ['create', 'store', 'edit', 'update', 'destroy']),
        ];
    }

    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('users::roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy([
            'module',
            function ($p) {
                $parts = explode('.', $p->name);
                if (count($parts) > 2) {
                    array_pop($parts);
                    return implode('.', $parts);
                }
                return $parts[0];
            }
        ]);

        $moduleLabels = trans('users::permissions.modules');
        $resourceLabels = trans('users::permissions.resources');
        $permissionMap = $this->buildPermissionMap();

        return view('users::roles.create', compact('permissions', 'moduleLabels', 'resourceLabels', 'permissionMap'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
        ]);

        $name = Str::slug($request->display_name);
        // Ensure name is unique
        $originalName = $name;
        $i = 1;
        while (Role::where('name', $name)->exists()) {
            $name = $originalName . '-' . $i++;
        }

        $role = Role::create([
            'name' => $name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'guard_name' => 'web'
        ]);

        $permissions = Permission::whereIn('id', $request->permissions)->get();
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')->with('success', __('users::roles.created_success'));
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy([
            'module',
            function ($p) {
                $parts = explode('.', $p->name);
                if (count($parts) > 2) {
                    array_pop($parts);
                    return implode('.', $parts);
                }
                return $parts[0];
            }
        ]);
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        $moduleLabels = trans('users::permissions.modules');
        $resourceLabels = trans('users::permissions.resources');
        $permissionMap = $this->buildPermissionMap();

        return view('users::roles.edit', compact('role', 'permissions', 'rolePermissions', 'moduleLabels', 'resourceLabels', 'permissionMap'));
    }

    /**
     * Build a flat map of permission_name => ['title', 'description'].
     * e.g. 'tickets.categories.view' => ['title' => '...', 'description' => '...']
     *
     * The list array structure is: ['resource_key' => ['action' => ['title'=>..., 'description'=>...]]]
     * Where resource_key may contain literal dots (e.g. 'tickets.categories').
     */
    private function buildPermissionMap(): array
    {
        $list = trans('users::permissions.list'); // raw PHP array, no dot resolution
        $map = [];
        foreach ($list as $resourceKey => $actions) {
            if (!is_array($actions))
                continue;
            // If the resource itself has a title, it's a standalone permission (e.g. tickets.settings)
            if (isset($actions['title'])) {
                $map[$resourceKey] = $actions; // key IS the full permission name
            }
            foreach ($actions as $action => $trans) {
                if (is_array($trans) && isset($trans['title'])) {
                    // Full permission name = resourceKey + '.' + action
                    $map[$resourceKey . '.' . $action] = $trans;
                }
            }
        }
        return $map;
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
        ]);

        $updateData = [
            'display_name' => $request->display_name,
            'description' => $request->description
        ];

        // Only allow changing 'name' for non-protected roles
        if ($role->name !== 'super-admin' && $request->has('name')) {
            $request->validate(['name' => 'required|unique:roles,name,' . $role->id]);
            $updateData['name'] = $request->name;
        }

        $role->update($updateData);

        $permissions = Permission::whereIn('id', $request->permissions)->get();
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')->with('success', __('users::roles.updated_success'));
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin') {
            return redirect()->route('roles.index')->with('error', __('Cannot delete super-admin role'));
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', __('users::roles.deleted_success'));
    }

    public function bulkActions(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', __('users::roles.nothing_selected'));
        }

        if ($action === 'delete') {
            $roles = Role::whereIn('id', $ids)->where('name', '!=', 'super-admin')->get();
            foreach ($roles as $role) {
                /** @var Role $role */
                $role->delete();
            }
            return redirect()->route('roles.index')->with('success', __('users::roles.deleted_success'));
        }

        return redirect()->back();
    }
}