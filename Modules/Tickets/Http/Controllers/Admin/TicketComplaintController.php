<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tickets\Domain\Models\TicketComplaint;
use Modules\Tickets\Domain\Models\TicketCategory;
use Modules\Tickets\Domain\Models\TicketSubComplaint;
use Modules\Tickets\Domain\Models\TicketStage;


use Modules\Tickets\Domain\Models\TicketGroup;

class TicketComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = TicketComplaint::with(['category.stage', 'subComplaints', 'routing.group']);
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
        return view('tickets::admin.complaints.index', compact('complaints', 'categories', 'groups'));
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
        ]);

        $complaint = TicketComplaint::create($request->only('name', 'category_id', 'sla_hours'));

        if ($request->group_id) {
            $complaint->routing()->create(['group_id' => $request->group_id]);
        }

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

    public function destroy(TicketComplaint $complaint)
    {
        $complaint->delete();

        return redirect()->route('admin.tickets.complaints.index')
            ->with('success', __('tickets::messages.messages.deleted'));
    }
}
