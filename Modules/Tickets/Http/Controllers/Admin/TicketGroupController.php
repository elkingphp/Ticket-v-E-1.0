<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tickets\Domain\Models\TicketGroup;
use Modules\Tickets\Domain\Models\TicketRouting;
use Modules\Users\Domain\Models\User;

class TicketGroupController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.groups.view', only: ['index', 'show']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.groups.create', only: ['create', 'store']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.groups.update', only: ['edit', 'update']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.groups.delete|tickets.groups.delete_requires_approval', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = TicketGroup::with(['members']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $groups = $query->paginate(10)->withQueryString();

        // Fetch filtered users who can be members of support groups
        $users = $this->getFilteredUsers();

        return view('tickets::admin.groups.index', compact('groups', 'users'));
    }

    protected function getFilteredUsers()
    {
        $roleIds = get_setting('tickets_support_group_roles', []);

        if (is_string($roleIds)) {
            $roleIds = json_decode($roleIds, true) ?? [];
        }

        if (!empty($roleIds)) {
            return User::whereHas('roles', function ($q) use ($roleIds) {
                // Check if roleIds are names or IDs. Spatie roles can be mixed.
                // Based on previous code, they seem to be IDs or names.
                if (is_numeric($roleIds[0] ?? null)) {
                    $q->whereIn('id', $roleIds);
                } else {
                    $q->whereIn('name', $roleIds);
                }
            })->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);
        }

        return User::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'nullable',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
            'leader_id' => 'nullable|exists:users,id',
        ]);

        return \DB::transaction(function () use ($request) {
            $isDefault = $request->has('is_default');

            if ($isDefault) {
                TicketGroup::where('is_default', true)->update(['is_default' => false]);
            }

            $group = TicketGroup::create([
                'name' => $request->name,
                'is_default' => $isDefault,
            ]);

            if ($request->has('members')) {
                $syncData = [];
                $memberIds = $request->members;

                // Ensure leader is in members list if selected
                if ($request->leader_id && !in_array($request->leader_id, $memberIds)) {
                    $memberIds[] = $request->leader_id;
                }

                foreach ($memberIds as $userId) {
                    $syncData[$userId] = ['is_leader' => ($userId == $request->leader_id)];
                }
                $group->members()->sync($syncData);
            }

            return redirect()->route('admin.tickets.groups.index')
                ->with('success', __('tickets::messages.messages.created'));
        });
    }

    public function update(Request $request, TicketGroup $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'nullable',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
            'leader_id' => 'nullable|exists:users,id',
        ]);

        return \DB::transaction(function () use ($request, $group) {
            $isDefault = $request->has('is_default');

            if ($isDefault) {
                TicketGroup::where('is_default', true)->where('id', '!=', $group->id)->update(['is_default' => false]);
            }

            $group->update([
                'name' => $request->name,
                'is_default' => $isDefault,
            ]);

            $syncData = [];
            $memberIds = $request->members ?? [];

            // Ensure leader is in members list if selected
            if ($request->leader_id && !in_array($request->leader_id, $memberIds)) {
                $memberIds[] = $request->leader_id;
            }

            foreach ($memberIds as $userId) {
                $syncData[$userId] = ['is_leader' => ($userId == $request->leader_id)];
            }
            $group->members()->sync($syncData);

            return redirect()->route('admin.tickets.groups.index')
                ->with('success', __('tickets::messages.messages.updated'));
        });
    }

    public function destroy(Request $request, TicketGroup $group, \Modules\Core\Application\Services\ApprovalService $approvalService)
    {
        if (!auth()->user()->can('tickets.groups.delete') && auth()->user()->can('tickets.groups.delete_requires_approval')) {
            if (!$group->pendingApprovalRequest()) {
                $approvalService->requestApproval(
                    $group,
                    'tickets.ticket_groups',
                    'delete',
                    ['reason' => $request->get('reason', 'طلب حذف مجموعة دعم تذاكر')]
                );
            }
            return redirect()->route('admin.tickets.groups.index')
                ->with('success', __('tickets::messages.delete_request_sent'));
        }

        $group->delete();

        return redirect()->route('admin.tickets.groups.index')
            ->with('success', __('tickets::messages.messages.group_deleted'));
    }
}
