<?php

namespace Modules\Tickets\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Tickets\Domain\Models\Ticket;
use Modules\Tickets\Domain\Models\TicketStage;
use Modules\Tickets\Domain\Models\TicketCategory;
use Modules\Tickets\Domain\Models\TicketComplaint;
use Modules\Tickets\Domain\Models\TicketStatus;
use Modules\Tickets\Domain\Models\TicketPriority;
use Modules\Tickets\Domain\Models\TicketSubComplaint;
use Modules\Tickets\Application\Services\TicketService;

class TicketsController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Ticket::where('user_id', $userId)
            ->with(['status', 'priority', 'stage', 'category']);

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $isFinal = $request->status === 'resolved';
            $query->whereHas('status', function ($q) use ($isFinal) {
                $q->where('is_final', $isFinal);
            });
        }

        $tickets = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => Ticket::where('user_id', $userId)->count(),
            'open' => Ticket::where('user_id', $userId)->whereHas('status', function ($q) {
                $q->where('is_final', false);
            })->count(),
            'resolved' => Ticket::where('user_id', $userId)->whereHas('status', function ($q) {
                $q->where('is_final', true);
            })->count(),
        ];

        return view('tickets::index', compact('tickets', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Load only stages visible to user
        $stages = TicketStage::visibleTo($user)
            ->with([
                'categories' => function ($query) use ($user) {
                    $query->visibleTo($user)->with([
                        'complaints' => function ($query) use ($user) {
                            $query->visibleTo($user)->with('subComplaints');
                        }
                    ]);
                }
            ])->get();

        // Assuming we have a default priority or we let user select? 
        // Usually users select priority, or it's inferred. Let's allowing selection for now.
        $priorities = TicketPriority::all();

        return view('tickets::create', compact('stages', 'priorities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'nullable|string|max:255',
            'description' => 'required|string',
            'stage_id' => 'required|exists:pgsql.tickets.ticket_stages,id',
            'category_id' => 'required|exists:pgsql.tickets.ticket_categories,id',
            'complaint_id' => 'nullable|exists:pgsql.tickets.ticket_complaints,id',
            'priority_id' => 'required|exists:pgsql.tickets.ticket_priorities,id',
            'sub_complaints' => 'nullable|array',
            'sub_complaints.*' => 'exists:pgsql.tickets.ticket_sub_complaints,id',
            'lecture_id' => 'nullable|exists:' . \Modules\Educational\Domain\Models\Lecture::class . ',id',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
        ]);

        $user = Auth::user();

        // 1. Authorize: Check if the selected stage is visible to this user
        $stage = TicketStage::visibleTo($user)->find($request->stage_id);
        if (!$stage) {
            return back()->withInput()->withErrors(['stage_id' => 'هذه المرحلة غير مصرح لك باختيارها.']);
        }

        // 2. Authorize: Check if Category is visible
        $category = TicketCategory::visibleTo($user)->where('stage_id', $stage->id)->find($request->category_id);
        if (!$category) {
            return back()->withInput()->withErrors(['category_id' => 'هذا التصنيف غير مصرح لك باختياره.']);
        }

        // 3. Authorize: Check Complaint if provided
        if ($request->filled('complaint_id')) {
            $complaint = TicketComplaint::visibleTo($user)->where('category_id', $category->id)->find($request->complaint_id);
            if (!$complaint) {
                return back()->withInput()->withErrors(['complaint_id' => 'هذه الشكوى غير مصرح لك باختيارها.']);
            }
        }

        $subject = $request->subject;
        if (empty($subject)) {
            $subject = $stage->name . ' > ' . $category->name;
        }

        $data = [
            'stage_id' => $request->stage_id,
            'category_id' => $request->category_id,
            'complaint_id' => $request->complaint_id,
            'priority_id' => $request->priority_id,
            'subject' => $subject,
            'details' => $request->description,
            'sub_complaints' => $request->sub_complaints,
            'lecture_id' => $request->lecture_id,
        ];

        if ($request->hasFile('attachments')) {
            $data['attachments'] = $request->file('attachments');
        }

        $ticket = $this->ticketService->createTicket(Auth::user(), $data);

        return redirect()->route('tickets.show', $ticket->uuid)
            ->with('success', __('tickets::messages.messages.created'));
    }

    /**
     * Show the specified resource.
     */
    public function show($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();

        // If the user is an agent/admin, redirect them to the agent show page
        if (Auth::user()->hasAnyRole(['admin', 'agent', 'super-admin', 'manager']) || Auth::id() !== $ticket->user_id) {
            // Check if they are actually staff/agent before redirecting
            if (Auth::user()->hasAnyRole(['admin', 'agent', 'super-admin', 'manager', 'staff'])) {
                return redirect()->route('agent.tickets.show', $uuid);
            }
        }

        // For students, ensure they only see their own tickets
        if ($ticket->user_id !== Auth::id()) {
            abort(404);
        }

        $ticket->load([
            'status',
            'priority',
            'stage',
            'category',
            'complaint',
            'subComplaints',
            'threads' => function ($query) {
                $query->public()->with(['user', 'attachments']);
            },
            'attachments',
            'lecture.sessionType'
        ]);

        $createdActivity = $ticket->activities()
            ->where('activity_type', 'created')
            ->with('user')
            ->first();

        return view('tickets::show', compact('ticket', 'createdActivity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Users typically don't edit ticket structure after creation, only reply.
        // Implement if needed.
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // For adding replies
        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $ticket = Ticket::where('uuid', $id)->where('user_id', Auth::id())->firstOrFail();

        $attachments = $request->hasFile('attachments') ? $request->file('attachments') : [];

        $this->ticketService->addReply($ticket, Auth::user(), $request->message, $attachments, false);

        return back()->with('success', __('tickets::messages.messages.created'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Users usually can't delete tickets, maybe close them?
        // Let's allow closing for now via update or specific action? 
        // For now, abort.
        abort(403);
    }
}
