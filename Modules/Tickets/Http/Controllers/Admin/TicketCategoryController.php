<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tickets\Domain\Models\TicketCategory;
use Modules\Tickets\Domain\Models\TicketStage;


use Modules\Tickets\Domain\Models\TicketGroup;

class TicketCategoryController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.categories.view', only: ['index', 'show']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.categories.create', only: ['create', 'store']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.categories.update', only: ['edit', 'update']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.categories.delete|tickets.categories.delete_requires_approval', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = TicketCategory::with(['stage', 'complaints', 'routing.group', 'roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }

        $categories = $query->paginate(10)->withQueryString();
        $stages = TicketStage::all();
        $groups = TicketGroup::all();
        $roles = \Modules\Users\Domain\Models\Role::all();
        return view('tickets::admin.categories.index', compact('categories', 'stages', 'groups', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stage_id' => 'required|exists:pgsql.tickets.ticket_stages,id',
            'sla_hours' => 'nullable|integer|min:1',
            'group_id' => 'nullable|exists:pgsql.tickets.ticket_groups,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $category = TicketCategory::create($request->only('name', 'stage_id', 'sla_hours'));

        if ($request->group_id) {
            $category->routing()->create(['group_id' => $request->group_id]);
        }

        $category->roles()->sync($request->roles);

        return redirect()->route('admin.tickets.categories.index')
            ->with('success', __('tickets::messages.messages.created'));
    }

    public function update(Request $request, TicketCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stage_id' => 'required|exists:pgsql.tickets.ticket_stages,id',
            'sla_hours' => 'nullable|integer|min:1',
            'group_id' => 'nullable|exists:pgsql.tickets.ticket_groups,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $category->update($request->only('name', 'stage_id', 'sla_hours'));

        if ($request->group_id) {
            $category->routing()->updateOrCreate(
                [],
                ['group_id' => $request->group_id]
            );
        } else {
            $category->routing()->delete();
        }

        $category->roles()->sync($request->roles);

        return redirect()->route('admin.tickets.categories.index')
            ->with('success', __('tickets::messages.messages.updated'));
    }

    public function destroy(Request $request, TicketCategory $category, \Modules\Core\Application\Services\ApprovalService $approvalService)
    {
        if (!auth()->user()->can('tickets.categories.delete') && auth()->user()->can('tickets.categories.delete_requires_approval')) {
            if (!$category->pendingApprovalRequest()) {
                $approvalService->requestApproval(
                    $category,
                    'tickets.ticket_categories',
                    'delete',
                    ['reason' => $request->get('reason', 'طلب حذف فئة تذاكر')]
                );
            }
            return redirect()->route('admin.tickets.categories.index')
                ->with('success', __('tickets::messages.delete_request_sent'));
        }

        $category->delete();

        return redirect()->route('admin.tickets.categories.index')
            ->with('success', __('tickets::messages.messages.category_deleted'));
    }
}
