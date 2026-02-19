<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tickets\Domain\Models\TicketCategory;
use Modules\Tickets\Domain\Models\TicketStage;


use Modules\Tickets\Domain\Models\TicketGroup;

class TicketCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = TicketCategory::with(['stage', 'complaints', 'routing.group']);

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
        return view('tickets::admin.categories.index', compact('categories', 'stages', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stage_id' => 'required|exists:pgsql.tickets.ticket_stages,id',
            'sla_hours' => 'nullable|integer|min:1',
            'group_id' => 'nullable|exists:pgsql.tickets.ticket_groups,id',
        ]);

        $category = TicketCategory::create($request->only('name', 'stage_id', 'sla_hours'));

        if ($request->group_id) {
            $category->routing()->create(['group_id' => $request->group_id]);
        }

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

        return redirect()->route('admin.tickets.categories.index')
            ->with('success', __('tickets::messages.messages.updated'));
    }

    public function destroy(TicketCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.tickets.categories.index')
            ->with('success', __('tickets::messages.messages.deleted'));
    }
}
