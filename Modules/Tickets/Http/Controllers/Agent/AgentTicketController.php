<?php

namespace Modules\Tickets\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Tickets\Domain\Models\Ticket;
use Modules\Tickets\Domain\Models\TicketStatus;
use Modules\Tickets\Domain\Models\TicketPriority;
use Modules\Tickets\Domain\Models\TicketGroup;
use Modules\Tickets\Application\Services\TicketService;
use Modules\Tickets\Services\TicketActivityService;

class AgentTicketController extends Controller
{
    protected $ticketService;
    protected $activityService;

    public function __construct(TicketService $ticketService, TicketActivityService $activityService)
    {
        $this->ticketService = $ticketService;
        $this->activityService = $activityService;
    }

    /**
     * Display a listing of assigned tickets.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $group_ids = $user->supportGroups()->pluck('tickets.ticket_groups.id')->toArray();

        // Base Query
        $query = Ticket::query()
            ->with(['user', 'status', 'priority', 'category', 'assignedTo', 'assignedGroup']);

        // Filters
        if ($request->has('view')) {
            switch ($request->view) {
                case 'my':
                    $query->where('assigned_to', $user->id);
                    break;
                case 'unassigned':
                    $query->whereNull('assigned_to');
                    break;
                case 'group':
                    $query->whereIn('assigned_group_id', $group_ids);
                    break;
            }
        } else {
            // Default view: assigned to me OR my groups OR unassigned
            $query->where(function ($q) use ($user, $group_ids) {
                $q->where('assigned_to', $user->id)
                    ->orWhereIn('assigned_group_id', $group_ids)
                    ->orWhereNull('assigned_to');
            });
        }

        if ($request->has('status_id') && $request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('priority_id') && $request->priority_id) {
            $query->where('priority_id', $request->priority_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'ilike', '%' . $request->search . '%')
                    ->orWhere('details', 'ilike', '%' . $request->search . '%')
                    ->orWhere('ticket_number', 'ilike', '%' . $request->search . '%')
                    ->orWhere('uuid', 'ilike', '%' . $request->search . '%') // Retained existing UUID search
                    ->orWhereHas('user', function ($qu) use ($request) {
                        $qu->where('name', 'ilike', '%' . $request->search . '%');
                    });
            });
        }

        // Stats
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::whereHas('status', fn($q) => $q->where('is_final', false))->count(),
            'my_tickets' => Ticket::where('assigned_to', $user->id)->count(),
            'unassigned' => Ticket::whereNull('assigned_to')->count(),
            'overdue' => Ticket::whereNotNull('due_at')->where('due_at', '<', now())->count(),
        ];

        $tickets = $query->latest()->paginate(20)->withQueryString();
        $statuses = TicketStatus::all();
        $priorities = TicketPriority::all();
        $groups = TicketGroup::all();

        return view('tickets::agent.index', compact('tickets', 'statuses', 'priorities', 'groups', 'stats'));
    }

    /**
     * Show ticket details.
     */
    public function show($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)
            ->with(['user', 'status', 'priority', 'category', 'complaint', 'subComplaints', 'threads.user', 'threads.attachments', 'attachments', 'lockedBy', 'activities.user'])
            ->firstOrFail();

        $user = Auth::user();
        $isLocked = false;
        $lockedByAnother = false;

        if ($ticket->locked_by && $ticket->locked_by != $user->id) {
            // Check if lock is still valid (e.g., within 30 minutes)
            if ($ticket->locked_at && $ticket->locked_at->gt(now()->subMinutes(30))) {
                $lockedByAnother = true;
            } else {
                // Lock expired, take over
                $ticket->update([
                    'locked_by' => $user->id,
                    'locked_at' => now(),
                ]);
            }
        } elseif (!$ticket->locked_by) {
            // Not locked, lock it
            $ticket->update([
                'locked_by' => $user->id,
                'locked_at' => now(),
            ]);
        } else {
            // Already locked by me, refresh lock
            $ticket->update(['locked_at' => now()]);
        }

        $statuses = TicketStatus::all();
        $priorities = TicketPriority::all();
        $groups = TicketGroup::all();
        $groupMembers = $ticket->assigned_group_id ? TicketGroup::find($ticket->assigned_group_id)->members : collect();

        $similarTickets = Ticket::where('category_id', $ticket->category_id)
            ->where('id', '!=', $ticket->id)
            ->with(['user', 'status'])
            ->latest()
            ->take(5)
            ->get();

        $createdActivity = $ticket->activities()
            ->where('activity_type', 'created')
            ->with('user')
            ->first();

        $firstAction = $ticket->activities()
            ->whereNotIn('activity_type', ['created'])
            ->oldest()
            ->first();

        return view('tickets::agent.show', compact('ticket', 'statuses', 'priorities', 'groups', 'groupMembers', 'lockedByAnother', 'similarTickets', 'firstAction', 'createdActivity'));
    }

    /**
     * Add a reply (Internal or Public).
     */
    public function reply(Request $request, $uuid)
    {
        $request->validate([
            'message' => 'required|string',
            'type' => 'required|in:message,internal_note',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();

        // Lock check
        if ($ticket->locked_by && $ticket->locked_by != Auth::id() && $ticket->locked_at && $ticket->locked_at->gt(now()->subMinutes(30))) {
            return back()->with('error', __('tickets::messages.locked_note'));
        }

        /** @var \Modules\Tickets\Domain\Models\Ticket $ticket */
        $attachments = $request->hasFile('attachments') ? $request->file('attachments') : [];
        $isInternal = $request->type === 'internal_note';

        // 1. Logic for changing group if internal and target_group_id provided
        if ($isInternal && $request->has('target_group_id') && !empty($request->target_group_id)) {
            $newGroupId = $request->target_group_id;
            if ($ticket->assigned_group_id != $newGroupId) {
                $newGroup = TicketGroup::find($newGroupId);

                if ($newGroup) {
                    $ticket->update([
                        'assigned_group_id' => $newGroup->id,
                        'assigned_to' => null // Clear person when assigned to group
                    ]);

                    // Log the group change activity
                    $this->activityService->record($ticket, 'group_assigned', [
                        'group_name' => $newGroup->name,
                        'actor_name' => Auth::user()->full_name ?? Auth::user()->name,
                        'context' => 'reply_reassignment'
                    ]);
                }
            }
        }

        // 2. Add the reply
        $this->ticketService->addReply($ticket, Auth::user(), $request->message, $attachments, $isInternal);


        return back()->with('success', __('tickets::messages.messages.created'));
    }

    /**
     * Update status.
     */
    public function updateStatus(Request $request, $uuid)
    {
        $request->validate([
            'status_id' => 'required|exists:pgsql.tickets.ticket_statuses,id',
        ]);

        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();

        // Lock check
        if ($ticket->locked_by && $ticket->locked_by != Auth::id() && $ticket->locked_at && $ticket->locked_at->gt(now()->subMinutes(30))) {
            return back()->with('error', __('tickets::messages.locked_note'));
        }

        $status = TicketStatus::findOrFail($request->status_id);

        $this->ticketService->updateStatus($ticket, $status, Auth::user());

        return back()->with('success', __('tickets::messages.messages.updated'));
    }

    /**
     * Update priority.
     */
    public function updatePriority(Request $request, $uuid)
    {
        $request->validate([
            'priority_id' => 'required|exists:pgsql.tickets.ticket_priorities,id',
        ]);

        /** @var Ticket $ticket */
        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();
        $user = Auth::user();

        $oldPriority = $ticket->priority;
        $newPriority = TicketPriority::findOrFail($request->priority_id);

        if ($oldPriority->id !== $newPriority->id) {
            $ticket->update(['priority_id' => $newPriority->id]);

            // Centralized Log & Notification
            $this->activityService->record($ticket, 'priority_changed', [
                'old_priority' => $oldPriority->name,
                'new_priority' => $newPriority->name,
                'actor_name' => $user->full_name ?? $user->name
            ]);
        }

        return back()->with('success', __('tickets::messages.messages.updated'));
    }

    /**
     * Assign ticket.
     */
    public function assign(Request $request, $uuid)
    {
        $request->validate([
            'user_id' => 'required|exists:pgsql.users,id',
        ]);

        /** @var Ticket $ticket */
        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();
        $user = Auth::user();
        $assignee = \Modules\Users\Domain\Models\User::findOrFail($request->user_id);

        $oldAssignee = $ticket->assignedTo;

        if (!$oldAssignee || $oldAssignee->id !== $assignee->id) {
            $ticket->update([
                'assigned_to' => $assignee->id,
                'assigned_group_id' => null // Clear group when assigned to specific person? Or keep? Usually we clear it if it's assigned to a person.
            ]);

            // Centralized Log & Notification
            $this->activityService->record($ticket, 'assigned', [
                'agent_name' => $assignee->full_name,
                'actor_name' => $user->full_name ?? $user->name
            ]);
        }

        return back()->with('success', __('tickets::messages.messages.updated'));
    }

    /**
     * Assign ticket to a group.
     */
    public function assignGroup(Request $request, $uuid)
    {
        $request->validate([
            'group_id' => 'required|exists:pgsql.tickets.ticket_groups,id',
        ]);

        /** @var Ticket $ticket */
        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();
        $user = Auth::user();
        $group = TicketGroup::findOrFail($request->group_id);

        $oldGroup = $ticket->assignedGroup;

        if (!$oldGroup || $oldGroup->id !== $group->id) {
            $ticket->update([
                'assigned_group_id' => $group->id,
                'assigned_to' => null // Clear person when assigned to group
            ]);

            // Centralized Log & Notification
            $this->activityService->record($ticket, 'group_assigned', [
                'group_name' => $group->name,
                'actor_name' => $user->full_name ?? $user->name
            ]);
        }

        return back()->with('success', __('tickets::messages.messages.updated'));
    }

    /**
     * Close the ticket (Set to final status).
     */
    public function close($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();
        $finalStatus = TicketStatus::where('is_final', true)->first();

        if (!$finalStatus) {
            return back()->with('error', __('tickets::messages.no_final_status'));
        }

        $this->ticketService->updateStatus($ticket, $finalStatus, Auth::user());

        return back()->with('success', __('tickets::messages.messages.updated'));
    }

    /**
     * Print ticket details.
     */
    public function print($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)
            ->with(['user', 'status', 'priority', 'category', 'complaint', 'threads.user', 'attachments'])
            ->firstOrFail();

        return view('tickets::agent.print', compact('ticket'));
    }
}
