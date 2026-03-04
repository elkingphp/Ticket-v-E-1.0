<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tickets\Domain\Models\TicketStatus;

class TicketStatusController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.statuses.view', only: ['index', 'show']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.statuses.create', only: ['create', 'store']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.statuses.update', only: ['edit', 'update']),
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.statuses.delete|tickets.statuses.delete_requires_approval', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = TicketStatus::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $statuses = $query->paginate(10)->withQueryString();
        return view('tickets::admin.statuses.index', compact('statuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
            'is_default' => 'boolean',
            'is_final' => 'boolean',
        ]);

        if ($request->is_default) {
            TicketStatus::where('is_default', true)->update(['is_default' => false]);
        }

        TicketStatus::create($request->all());

        return redirect()->route('admin.tickets.statuses.index')
            ->with('success', __('tickets::messages.messages.created'));
    }

    public function update(Request $request, TicketStatus $status)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
            'is_default' => 'boolean',
            'is_final' => 'boolean',
        ]);

        if ($request->is_default) {
            TicketStatus::where('is_default', true)->where('id', '!=', $status->id)->update(['is_default' => false]);
        }

        $status->update($request->all());

        return redirect()->route('admin.tickets.statuses.index')
            ->with('success', __('tickets::messages.messages.updated'));
    }

    public function destroy(Request $request, TicketStatus $status, \Modules\Core\Application\Services\ApprovalService $approvalService)
    {
        if (!auth()->user()->can('tickets.statuses.delete') && auth()->user()->can('tickets.statuses.delete_requires_approval')) {
            if (!$status->pendingApprovalRequest()) {
                $approvalService->requestApproval(
                    $status,
                    'tickets.ticket_statuses',
                    'delete',
                    ['reason' => $request->get('reason', 'طلب حذف حالة تذاكر')]
                );
            }
            return redirect()->route('admin.tickets.statuses.index')
                ->with('success', __('tickets::messages.delete_request_sent'));
        }

        $status->delete();

        return redirect()->route('admin.tickets.statuses.index')
            ->with('success', __('tickets::messages.messages.status_deleted'));
    }
}
