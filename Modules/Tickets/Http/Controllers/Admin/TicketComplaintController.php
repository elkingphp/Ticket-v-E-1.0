<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tickets\Domain\Models\TicketComplaint;
use Modules\Tickets\Domain\Models\TicketCategory;
use Modules\Tickets\Domain\Models\TicketSubComplaint;
use Modules\Tickets\Domain\Models\TicketStage;


use Modules\Tickets\Domain\Models\TicketGroup;

class TicketComplaintController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.complaints.view', only: ['index', 'show']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.complaints.create', only: ['create', 'store']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.complaints.update', only: ['edit', 'update']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.complaints.delete|tickets.complaints.delete_requires_approval', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = TicketComplaint::with(['category.stage', 'subComplaints', 'routing.group', 'roles']);
        $groups = TicketGroup::all();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $complaints = $query->paginate(10)->withQueryString();
        $categories = TicketCategory::with('stage')->get();
        $roles = \Modules\Users\Domain\Models\Role::all();
        return view('tickets::admin.complaints.index', compact('complaints', 'categories', 'groups', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:pgsql.tickets.ticket_categories,id',
            'sla_hours' => 'nullable|integer|min:0',
            'group_id' => 'nullable|exists:pgsql.tickets.ticket_groups,id',
            'sub_complaints' => 'nullable|array',
            'sub_complaints.*' => 'nullable|string|max:255',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $complaint = TicketComplaint::create($request->only('name', 'category_id', 'sla_hours'));

        if ($request->group_id) {
            $complaint->routing()->create(['group_id' => $request->group_id]);
        }

        $complaint->roles()->sync($request->roles);

        if ($request->has('sub_complaints')) {
            foreach ($request->sub_complaints as $subName) {
                if (!empty($subName)) {
                    $complaint->subComplaints()->create(['name' => $subName]);
                }
            }
        }

        return redirect()->route('admin.tickets.complaints.index')
            ->with('success', __('tickets::messages.messages.created'));
    }

    public function update(Request $request, TicketComplaint $complaint)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:pgsql.tickets.ticket_categories,id',
            'sla_hours' => 'nullable|integer|min:0',
            'group_id' => 'nullable|exists:pgsql.tickets.ticket_groups,id',
            'sub_complaints' => 'nullable|array',
            'sub_complaints.*' => 'nullable|string|max:255',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $complaint->update($request->only('name', 'category_id', 'sla_hours'));

        if ($request->group_id) {
            $complaint->routing()->updateOrCreate(
                [],
                ['group_id' => $request->group_id]
            );
        } else {
            $complaint->routing()->delete();
        }

        $complaint->roles()->sync($request->roles);

        // Sync Sub Complaints
        $complaint->subComplaints()->delete();
        if ($request->has('sub_complaints')) {
            foreach ($request->sub_complaints as $subName) {
                if (!empty($subName)) {
                    $complaint->subComplaints()->create(['name' => $subName]);
                }
            }
        }

        return redirect()->route('admin.tickets.complaints.index')
            ->with('success', __('tickets::messages.messages.updated'));
    }

    public function destroy(Request $request, TicketComplaint $complaint, \Modules\Core\Application\Services\ApprovalService $approvalService)
    {
        if (!auth()->user()->can('tickets.complaints.delete') && auth()->user()->can('tickets.complaints.delete_requires_approval')) {
            if (!$complaint->pendingApprovalRequest()) {
                $approvalService->requestApproval(
                    $complaint,
                    'tickets.ticket_complaints',
                    'delete',
                    ['reason' => $request->get('reason', 'طلب حذف نوع شكوى تذاكر')]
                );
            }
            return redirect()->route('admin.tickets.complaints.index')
                ->with('success', __('tickets::messages.delete_request_sent'));
        }

        $complaint->delete();

        return redirect()->route('admin.tickets.complaints.index')
            ->with('success', __('tickets::messages.messages.complaint_deleted'));
    }
}
