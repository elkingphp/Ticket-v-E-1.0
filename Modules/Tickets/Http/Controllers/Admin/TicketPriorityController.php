<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tickets\Domain\Models\TicketPriority;

class TicketPriorityController extends Controller
{
    public function index(Request $request)
    {
        $query = TicketPriority::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $priorities = $query->paginate(10)->withQueryString();
        return view('tickets::admin.priorities.index', compact('priorities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
            'is_default' => 'boolean',
            'sla_multiplier' => 'numeric|min:0',
        ]);

        if ($request->is_default) {
            TicketPriority::where('is_default', true)->update(['is_default' => false]);
        }

        TicketPriority::create($request->all());

        return redirect()->route('admin.tickets.priorities.index')
            ->with('success', __('tickets::messages.messages.created'));
    }

    public function update(Request $request, TicketPriority $priority)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
            'is_default' => 'boolean',
            'sla_multiplier' => 'numeric|min:0',
        ]);

        if ($request->is_default) {
            TicketPriority::where('is_default', true)->where('id', '!=', $priority->id)->update(['is_default' => false]);
        }

        $priority->update($request->all());

        return redirect()->route('admin.tickets.priorities.index')
            ->with('success', __('tickets::messages.messages.updated'));
    }

    public function destroy(TicketPriority $priority)
    {
        $priority->delete();

        return redirect()->route('admin.tickets.priorities.index')
            ->with('success', __('tickets::messages.messages.deleted'));
    }
}
