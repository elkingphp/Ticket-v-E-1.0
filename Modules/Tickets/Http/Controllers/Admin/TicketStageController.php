<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tickets\Domain\Models\TicketStage;


use Modules\Tickets\Domain\Models\TicketGroup;

class TicketStageController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.stages.view', only: ['index', 'show']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.stages.create', only: ['create', 'store']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.stages.update', only: ['edit', 'update']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.stages.delete|tickets.stages.delete_requires_approval', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = TicketStage::with(['categories', 'routing.group', 'roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $stages = $query->paginate(10)->withQueryString();
        $groups = TicketGroup::all();
        $roles = \Modules\Users\Domain\Models\Role::all();
        return view('tickets::admin.stages.index', compact('stages', 'groups', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'external_name' => 'nullable|string|max:255',
            'sla_hours' => 'nullable|integer|min:1',
            'group_id' => 'nullable|exists:pgsql.tickets.ticket_groups,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $stage = TicketStage::create($request->only('name', 'external_name', 'sla_hours'));

        if ($request->group_id) {
            $stage->routing()->create(['group_id' => $request->group_id]);
        }

        $stage->roles()->sync($request->roles);

        return redirect()->route('admin.tickets.stages.index')
            ->with('success', __('tickets::messages.messages.created'));
    }

    public function update(Request $request, TicketStage $stage)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'external_name' => 'nullable|string|max:255',
            'sla_hours' => 'nullable|integer|min:1',
            'group_id' => 'nullable|exists:pgsql.tickets.ticket_groups,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $stage->update($request->only('name', 'external_name', 'sla_hours'));

        if ($request->group_id) {
            $stage->routing()->updateOrCreate(
                [],
                ['group_id' => $request->group_id]
            );
        } else {
            $stage->routing()->delete();
        }

        $stage->roles()->sync($request->roles);

        return redirect()->route('admin.tickets.stages.index')
            ->with('success', __('tickets::messages.messages.updated'));
    }

    public function destroy(Request $request, TicketStage $stage, \Modules\Core\Application\Services\ApprovalService $approvalService)
    {
        $user = auth()->user();

        // SECURITY FIX: Correct permission logic
        // Case 1: User CAN delete directly → delete immediately
        // Case 2: User CANNOT delete directly but CAN request approval → create approval request
        // Case 3: User has neither permission → 403 Forbidden
        if ($user->can('tickets.stages.delete')) {
            // Direct delete – no approval needed
            $stage->delete();

            return redirect()->route('admin.tickets.stages.index')
                ->with('success', __('tickets::messages.messages.stage_deleted'));
        }

        if ($user->can('tickets.stages.delete_requires_approval')) {
            if (!$stage->pendingApprovalRequest()) {
                $approvalService->requestApproval(
                    $stage,
                    'tickets.ticket_stages',
                    'delete',
                    ['reason' => $request->get('reason', 'طلب حذف مرحلة تذاكر')]
                );
            }
            return redirect()->route('admin.tickets.stages.index')
                ->with('success', __('tickets::messages.delete_request_sent'));
        }

        // No permission at all
        abort(403, __('tickets::messages.error'));
    }
}
