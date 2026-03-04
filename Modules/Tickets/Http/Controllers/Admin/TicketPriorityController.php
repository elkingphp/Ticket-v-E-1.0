<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tickets\Domain\Models\TicketPriority;

class TicketPriorityController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.priorities.view', only: ['index', 'show']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.priorities.create', only: ['create', 'store']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.priorities.update', only: ['edit', 'update']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.priorities.delete|tickets.priorities.delete_requires_approval', only: ['destroy']),
        ];
    }

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

    public function destroy(Request $request, TicketPriority $priority, \Modules\Core\Application\Services\ApprovalService $approvalService)
    {
        if (!auth()->user()->can('tickets.priorities.delete') && auth()->user()->can('tickets.priorities.delete_requires_approval')) {
            if (!$priority->pendingApprovalRequest()) {
                $approvalService->requestApproval(
                    $priority,
                    'tickets.ticket_priorities',
                    'delete',
                    ['reason' => $request->get('reason', 'طلب حذف أولوية تذاكر')]
                );
            }
            return redirect()->route('admin.tickets.priorities.index')
                ->with('success', __('tickets::messages.delete_request_sent'));
        }

        $priority->delete();

        return redirect()->route('admin.tickets.priorities.index')
            ->with('success', __('tickets::messages.messages.priority_deleted'));
    }
}
