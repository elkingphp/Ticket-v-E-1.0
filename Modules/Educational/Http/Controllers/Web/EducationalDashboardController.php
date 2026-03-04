<?php

namespace Modules\Educational\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Modules\Educational\Domain\Models\Lecture;
use Modules\Educational\Domain\Models\Room;
use Modules\Educational\Domain\Models\Program;
use Modules\Educational\Domain\Models\InstructorProfile;
use Modules\Educational\Domain\Models\TraineeProfile;
use Modules\Educational\Domain\Models\TrainingCompany;
use Modules\Educational\Domain\Models\Track;
use Modules\Educational\Domain\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EducationalDashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = now();

        $dateFrom = $request->get('date_from') ? Carbon::parse($request->get('date_from'))->startOfDay() : $now->copy()->startOfMonth();
        $dateTo = $request->get('date_to') ? Carbon::parse($request->get('date_to'))->endOfDay() : $now->copy()->endOfMonth();
        $programId = $request->get('program_id');

        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();

        // 1. Core KPIs
        $stats = [
            'total_programs' => Program::count(),
            'active_programs' => Program::where('status', 'running')->count(),
            'total_students' => TraineeProfile::count(),
            'total_instructors' => InstructorProfile::count(),
            'total_companies' => TrainingCompany::count(),
            'total_tracks' => Track::count(),
            'lectures_today' => Lecture::whereBetween('starts_at', [$todayStart, $todayEnd])
                ->when($programId, fn($q) => $q->where('program_id', $programId))
                ->count(),
            'upcoming_lectures' => Lecture::where('status', 'scheduled')
                ->whereBetween('starts_at', [$now, $dateTo])
                ->when($programId, fn($q) => $q->where('program_id', $programId))
                ->count(),
        ];

        // 2. Room Occupancy Today
        $totalRooms = Room::where('room_status', 'active')->count();
        $occupiedRoomsToday = Lecture::whereBetween('starts_at', [$todayStart, $todayEnd])
            ->when($programId, fn($q) => $q->where('program_id', $programId))
            ->distinct('room_id')
            ->count('room_id');

        $stats['occupancy_rate'] = $totalRooms > 0 ? round(($occupiedRoomsToday / $totalRooms) * 100, 1) : 0;

        // Attendance stats in period
        $attendanceQuery = Attendance::whereHas('lecture', function ($q) use ($dateFrom, $dateTo, $programId) {
            $q->whereBetween('starts_at', [$dateFrom, $dateTo])
                ->when($programId, fn($sq) => $sq->where('program_id', $programId));
        });

        $totalAttendances = (clone $attendanceQuery)->count();
        $presentCount = (clone $attendanceQuery)->whereIn('status', ['present', 'late'])->count();

        $stats['attendance_rate'] = $totalAttendances > 0 ? round(($presentCount / $totalAttendances) * 100, 1) : 0;

        // 3. Instructor Distribution (Top 5 busiest instructors in period)
        $topInstructors = Lecture::whereBetween('starts_at', [$dateFrom, $dateTo])
            ->whereNotNull('instructor_profile_id')
            ->when($programId, fn($q) => $q->where('program_id', $programId))
            ->select('instructor_profile_id', DB::raw('count(*) as lecture_count'))
            ->groupBy('instructor_profile_id')
            ->with(['instructorProfile.user'])
            ->orderByDesc('lecture_count')
            ->limit(5)
            ->get();

        // 4. Lecture Status Overview
        $statusBreakdown = Lecture::whereBetween('starts_at', [$dateFrom, $dateTo])
            ->when($programId, fn($q) => $q->where('program_id', $programId))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 5. Recent Activity
        $recentLectures = Lecture::with(['program', 'group', 'room'])
            ->when($programId, fn($q) => $q->where('program_id', $programId))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $programs = Program::orderBy('name')->get();

        return view('educational::dashboard', compact(
            'stats',
            'topInstructors',
            'statusBreakdown',
            'recentLectures',
            'programs',
            'dateFrom',
            'dateTo',
            'programId'
        ));
    }
}
