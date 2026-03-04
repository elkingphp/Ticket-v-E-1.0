<?php

namespace Modules\Tickets\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tickets\Domain\Models\Ticket;
use Modules\Tickets\Domain\Models\TicketGroup;
use Modules\Tickets\Domain\Models\TicketStage;
use Modules\Tickets\Domain\Models\TicketStatus;
use Modules\Tickets\Domain\Models\TicketPriority;
use Modules\Tickets\Domain\Models\TicketCategory;
use Modules\Educational\Domain\Models\Program;
use Modules\Educational\Domain\Models\SessionType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TicketDashboardController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new \Illuminate\Routing\Controllers\Middleware('permission:tickets.view_dashboard'),
        ];
    }

    public function index(Request $request)
    {
        // Use qualified names to avoid ambiguity
        $query = Ticket::query()->select('tickets.tickets.*');

        // Global Date Filtering (Optional, triggered by range selector)
        $range = $request->get('range', 30); // Default all stats to 30 days for a cleaner dashboard if search is gone
        if ($range != 'all') {
            $query->where('tickets.tickets.created_at', '>=', Carbon::now()->subDays((int) $range));
        }

        // 1. KPI Stats
        $stats = [
            'total' => (clone $query)->count(),
            'open' => (clone $query)->whereHas('status', fn($q) => $q->where('tickets.ticket_statuses.is_final', false))->count(),
            'closed' => (clone $query)->whereHas('status', fn($q) => $q->where('tickets.ticket_statuses.is_final', true))->count(),
            'overdue' => (clone $query)->whereNotNull('tickets.tickets.due_at')->where('tickets.tickets.due_at', '<', now())->count(),
        ];

        // 2. Ticket Volume Trend (Flexible Range)
        $trendRange = (int) $request->get('trend_range', 15);
        $dateLimit = Carbon::now()->subDays($trendRange);

        $trends = Ticket::query()
            ->where('tickets.tickets.created_at', '>=', $dateLimit)
            ->select(DB::raw('DATE(tickets.tickets.created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trendData = [
            'labels' => $trends->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray(),
            'values' => $trends->pluck('count')->toArray(),
            'current_range' => $trendRange
        ];

        // 3. Status Distribution
        $statusDistribution = (clone $query)->join('tickets.ticket_statuses', 'tickets.tickets.status_id', '=', 'tickets.ticket_statuses.id')
            ->select('tickets.ticket_statuses.name', 'tickets.ticket_statuses.color', DB::raw('count(*) as count'))
            ->groupBy('tickets.ticket_statuses.name', 'tickets.ticket_statuses.color')
            ->get();

        // 4. Priority Distribution
        $priorityDistribution = (clone $query)->join('tickets.ticket_priorities', 'tickets.tickets.priority_id', '=', 'tickets.ticket_priorities.id')
            ->select('tickets.ticket_priorities.name', 'tickets.ticket_priorities.color', DB::raw('count(*) as count'))
            ->groupBy('tickets.ticket_priorities.name', 'tickets.ticket_priorities.color')
            ->get();

        // 5. Category Distribution (Top 5)
        $categoryDistribution = (clone $query)->join('tickets.ticket_categories', 'tickets.tickets.category_id', '=', 'tickets.ticket_categories.id')
            ->select('tickets.ticket_categories.name', DB::raw('count(*) as count'))
            ->groupBy('tickets.ticket_categories.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 6. Stages Detailed Stats
        $totalTickets = $stats['total'];
        $stageStats = (clone $query)->whereNotNull('tickets.tickets.stage_id')
            ->select('tickets.tickets.stage_id', DB::raw('count(*) as count'))
            ->groupBy('tickets.tickets.stage_id')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) use ($totalTickets) {
                return [
                    'name' => $item->stage->name ?? 'Unknown',
                    'count' => $item->count,
                    'percentage' => $totalTickets > 0 ? round(($item->count / $totalTickets) * 100, 1) : 0
                ];
            });

        // 7. Support Groups Performance (Top 5)
        $supportGroupStats = (clone $query)->whereNotNull('tickets.tickets.assigned_group_id')
            ->join('tickets.ticket_groups', 'tickets.tickets.assigned_group_id', '=', 'tickets.ticket_groups.id')
            ->select('tickets.ticket_groups.name', DB::raw('count(*) as count'))
            ->groupBy('tickets.ticket_groups.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 8. Educational Programs
        $programStats = (clone $query)->join('education.trainee_profiles', 'tickets.tickets.user_id', '=', 'education.trainee_profiles.user_id')
            ->join('education.groups', 'education.trainee_profiles.group_id', '=', 'education.groups.id')
            ->join('education.programs', 'education.groups.program_id', '=', 'education.programs.id')
            ->select('education.programs.name', DB::raw('count(*) as count'))
            ->groupBy('education.programs.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 9. Educational Groups
        $eduGroupStats = (clone $query)->join('education.trainee_profiles', 'tickets.tickets.user_id', '=', 'education.trainee_profiles.user_id')
            ->join('education.groups', 'education.trainee_profiles.group_id', '=', 'education.groups.id')
            ->select('education.groups.name', DB::raw('count(*) as count'))
            ->groupBy('education.groups.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 10. Recent 10 Tickets (Ignored from global range for immediate visibility)
        $recentTickets = Ticket::query()->with(['user', 'status', 'priority', 'category'])
            ->orderBy('tickets.tickets.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('tickets::agent.dashboard', compact(
            'stats',
            'trendData',
            'statusDistribution',
            'priorityDistribution',
            'categoryDistribution',
            'stageStats',
            'supportGroupStats',
            'programStats',
            'eduGroupStats',
            'recentTickets'
        ));
    }
}
