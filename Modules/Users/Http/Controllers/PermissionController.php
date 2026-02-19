<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:manage permissions'),
        ];
    }

    public function index()
    {
        $permissions = Permission::all()->groupBy('module');
        return view('users::permissions.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
            'module' => 'required|string',
        ]);

        Permission::create([
            'name' => $request->name,
            'module' => $request->module,
            'guard_name' => 'web'
        ]);

        return redirect()->route('permissions.index')->with('success', __('Permission created successfully'));
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('permissions.index')->with('success', __('Permission deleted successfully'));
    }
}