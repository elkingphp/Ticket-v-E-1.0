<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Users\Domain\Interfaces\UserRepositoryInterface;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UsersController extends Controller implements HasMiddleware
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:users.view', only: ['index', 'show', 'export']),
            new Middleware('permission:users.create', only: ['create', 'store']),
            new Middleware('permission:users.edit', only: ['edit', 'update']),
            new Middleware('permission:users.delete', only: ['destroy']),
        ];
    }

    public function export()
    {
        $users = $this->userRepository->all();
        $filename = "users_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'First Name', 'Last Name', 'Username', 'Email', 'Status', 'Joined Date'];

        $callback = function () use ($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->first_name,
                    $user->last_name,
                    $user->username,
                    $user->email,
                    $user->status,
                    $user->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'role']);
        $users = $this->userRepository->advancedSearch($filters);
        $roles = Role::all();
        $stats = $this->userRepository->getStatistics();
        return view('users::users.index', compact('users', 'roles', 'stats'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users::users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array'],
            'status' => ['required', 'in:active,blocked,inactive'],
        ]);

        $this->preventPrivilegeEscalation($request->roles);

        $user = $this->userRepository->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'joined_at' => now(),
        ]);

        if ($request->status === 'blocked') {
            $user->update(['blocked_at' => now()]);
        } elseif ($request->status === 'active') {
            $user->update(['activated_at' => now()]);
        }

        $user->assignRole($request->roles);

        return redirect()->route('users.index')->with('success', __('users::users.created_success'));
    }

    public function show($id)
    {
        $user = $this->userRepository->find($id);
        return view('users::users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = $this->userRepository->find($id);
        $roles = Role::all();
        return view('users::users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = $this->userRepository->find($id);

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array'],
            'status' => ['required', 'in:active,blocked,inactive'],
        ]);

        $this->preventPrivilegeEscalation($request->roles);

        $data = $request->only(['first_name', 'last_name', 'username', 'email', 'status']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($data['status'] !== $user->status) {
            $this->userRepository->updateStatus($id, $data['status']);
            unset($data['status']);
        }

        $this->userRepository->update($id, $data);
        $user->syncRoles($request->roles);

        return redirect()->route('users.index')->with('success', __('users::users.updated_success'));
    }

    public function destroy($id)
    {
        if (auth()->id() == $id) {
            return back()->with('error', __('users::users.cannot_delete_self'));
        }

        $this->userRepository->delete($id);
        return redirect()->route('users.index')->with('success', __('users::users.deleted_success'));
    }

    /**
     * SECURITY: Prevent privilege escalation.
     * Only super-admins can assign super-admin or admin roles.
     */
    protected function preventPrivilegeEscalation(array $requestedRoles): void
    {
        $currentUser = auth()->user();

        // super-admin can do anything
        if ($currentUser->hasRole('super-admin')) {
            return;
        }

        $protectedRoles = ['super-admin', 'admin'];

        foreach ($requestedRoles as $role) {
            // Role can be an ID or a name
            $roleName = is_numeric($role)
                ? \Spatie\Permission\Models\Role::find($role)?->name
                : $role;

            if (in_array($roleName, $protectedRoles, true)) {
                abort(403, __('users::users.cannot_assign_elevated_role'));
            }
        }
    }

    public function bulkActions(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'action' => 'required|string|in:delete,activate,block',
        ]);

        $ids = $request->ids;

        if ($request->action === 'delete') {
            if (!auth()->user()->can('users.delete')) {
                abort(403);
            }
            // Exclude current user
            $ids = array_diff($ids, [auth()->id()]);
            $this->userRepository->bulkDelete($ids);
            $message = __('users::users.bulk_delete_success');
        } elseif ($request->action === 'activate') {
            if (!auth()->user()->can('users.edit')) {
                abort(403);
            }
            $this->userRepository->bulkUpdateStatus($ids, 'active');
            $message = __('users::users.bulk_activate_success');
        } elseif ($request->action === 'block') {
            if (!auth()->user()->can('users.edit')) {
                abort(403);
            }
            $this->userRepository->bulkUpdateStatus($ids, 'blocked');
            $message = __('users::users.bulk_block_success');
        }

        return back()->with('success', $message ?? __('Success'));
    }

    public function downloadTemplate()
    {
        $filename = "users_import_template.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['first_name', 'last_name', 'username', 'email', 'password', 'role', 'status']);
            // Example row
            fputcsv($file, ['John', 'Doe', 'johndoe', 'john@example.com', 'password123', 'Customer', 'active']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            // SECURITY: Restrict to CSV only, max 2MB, prevent XLSX macros
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        // Simple CSV import implementation
        $file = $request->file('file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        $header = array_shift($data); // Remove header

        $count = 0;
        foreach ($data as $row) {
            if (count($row) < 7)
                continue;

            // SECURITY: Sanitize each cell against CSV Injection.
            // Attackers can prefix cells with =, +, -, @ to trigger formula execution in spreadsheet apps.
            $sanitize = fn($val) => ltrim(trim((string) $val), '=+-@|\t\r');

            $firstName = $sanitize($row[0]);
            $lastName = $sanitize($row[1]);
            $username = $sanitize($row[2]);
            $email = filter_var(trim($row[3]), FILTER_SANITIZE_EMAIL);
            $rawPass = $row[4]; // kept temporarily for hashing only
            $roleName = $sanitize($row[5]);
            $status = in_array(trim($row[6] ?? ''), ['active', 'inactive', 'blocked'])
                ? trim($row[6])
                : 'active';

            // Skip rows with invalid email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Skip duplicate emails
            if (\Modules\Users\Domain\Models\User::where('email', $email)->exists()) {
                continue;
            }

            $userData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($rawPass),
                'status' => $status,
                'joined_at' => now(),
            ];

            // Clear raw password from memory immediately after hashing
            unset($rawPass);

            $user = $this->userRepository->create($userData);

            // Assign role – only if the role actually exists
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->assignRole($role);
            }

            $count++;
        }

        return back()->with('success', __('users::users.import_success', ['count' => $count]));
    }
}