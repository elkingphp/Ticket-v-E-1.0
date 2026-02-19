<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tickets\Domain\Models\TicketStage;


use Modules\Tickets\Domain\Models\TicketGroup;

class TicketStageController extends Controller
{
    public function index(Request $request)
    {
        $query = TicketStage::with(['categories', 'routing.group']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $stages = $query->paginate(10)->withQueryString();
        $groups = TicketGroup::all();
        return view('tickets::admin.stages.index', compact('stages', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sla_hours' => 'nullable|integer|min:1',
            'group_id' => 'nullable|exists:pgsql.tickets.ticket_groups,id',
        ]);

        $stage = TicketStage::create($request->only('name', 'sla_hours'));

        if ($request->group_id) {
            $stage->routing()->create(['group_id' => $request->group_id]);
        }

        return redirect()->route('admin.tickets.stages.index')
            ->with('success', __('tickets::messages.messages.created'));
    }

    public function update(Request $request, TicketStage $stage)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sla_hours' => 'nullable|integer|min:1',
            'group_id' => 'nullable|exists:pgsql.tickets.ticket_groups,id',
        ]);

        $stage->update($request->only('name', 'sla_hours'));

        if ($request->group_id) {
            $stage->routing()->updateOrCreate(
                [],
                ['group_id' => $request->group_id]
            );
        } else {
            $stage->routing()->delete();
        }

        return redirect()->route('admin.tickets.stages.index')
            ->with('success', __('tickets::messages.messages.updated'));
    }

    public function destroy(TicketStage $stage)
    {
        $stage->delete();

        return redirect()->route('admin.tickets.stages.index')
            ->with('success', __('tickets::messages.messages.deleted'));
    }
}
