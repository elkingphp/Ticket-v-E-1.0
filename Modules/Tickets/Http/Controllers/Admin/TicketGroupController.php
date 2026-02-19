<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tickets\Domain\Models\TicketGroup;
use Modules\Tickets\Domain\Models\TicketRouting;
use Modules\Users\Domain\Models\User;

class TicketGroupController extends Controller
{
    public function index(Request $request)
    {
        $query = TicketGroup::with(['members']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $groups = $query->paginate(10)->withQueryString();

        // Fetch users who can be members of support groups
        $users = User::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);

        return view('tickets::admin.groups.index', compact('groups', 'users'));
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

    public function destroy(TicketGroup $group)
    {
        $group->delete();

        return redirect()->route('admin.tickets.groups.index')
            ->with('success', __('tickets::messages.messages.deleted'));
    }
}
