<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\Lecture;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Return absent trainees for a specific lecture (JSON for modal).
     */
    public function absenceReport($lectureId)
    {
        $lecture = Lecture::with([
            'program',
            'group',
            'room',
            'instructorProfile.user',
            'attendances.traineeProfile.user',
        ])->findOrFail($lectureId);

        $allTrainees = \Modules\Educational\Domain\Models\TraineeProfile::with('user')
            ->where('group_id', $lecture->group_id)
            ->get();

        $attendedIds = $lecture->attendances->where('status', 'present')->pluck('trainee_profile_id')->toArray();
        $lateIds = $lecture->attendances->where('status', 'late')->pluck('trainee_profile_id')->toArray();
        $excusedIds = $lecture->attendances->where('status', 'excused')->pluck('trainee_profile_id')->toArray();

        $absentTrainees = $allTrainees->filter(function ($t) use ($attendedIds, $lateIds, $excusedIds) {
            return !in_array($t->id, $attendedIds)
                && !in_array($t->id, $lateIds)
                && !in_array($t->id, $excusedIds);
        })->values();

        return response()->json([
            'lecture' => [
                'id' => $lecture->id,
                'program' => $lecture->program->name ?? '-',
                'group' => $lecture->group->name ?? '-',
                'room' => $lecture->room->name ?? '-',
                'instructor' => $lecture->instructorProfile->user->full_name ?? '-',
                'starts_at' => $lecture->starts_at->format('H:i'),
                'ends_at' => $lecture->ends_at->format('H:i'),
                'date' => $lecture->starts_at->translatedFormat('l، d F Y'),
            ],
            'summary' => [
                'total' => $allTrainees->count(),
                'present' => count($attendedIds),
                'late' => count($lateIds),
                'excused' => count($excusedIds),
                'absent' => $absentTrainees->count(),
            ],
            'absent_trainees' => $absentTrainees->map(fn($t) => [
                'id' => $t->id,
                'full_name' => $t->user->full_name ?? '-',
                'military_number' => $t->user->military_number ?? '-',
            ]),
        ]);
    }


    public function dashboard(Request $request)
    {
        $programs = \Modules\Educational\Domain\Models\Program::orderBy('name')->get();
        $buildings = \Modules\Educational\Domain\Models\Building::with('floors')->orderBy('name')->get();

        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $programId = $request->input('program_id');
        $attendanceStatus = $request->input('attendance_status');
        $search = $request->input('search');
        $buildingId = $request->input('building_id');
        $floorId = $request->input('floor_id');

        // Determine available floors based on selected building (for JS cascade)
        $floors = $buildingId
            ? \Modules\Educational\Domain\Models\Floor::where('building_id', $buildingId)->orderBy('name')->get()
            : collect();

        // Initial query for rooms that have lectures today
        $roomsQuery = \Modules\Educational\Domain\Models\Room::query()
            ->with(['floor.building'])
            // ─── Building filter ───────────────────────────────────────────
            ->when($buildingId, function ($q) use ($buildingId) {
                $q->whereHas('floor', fn($fq) => $fq->where('building_id', $buildingId));
            })
            // ─── Floor filter ──────────────────────────────────────────────
            ->when($floorId, fn($q) => $q->where('floor_id', $floorId))
            ->whereHas('lectures', function ($q) use ($date, $programId, $attendanceStatus, $search) {
                $q->whereDate('starts_at', $date);
                if ($programId)
                    $q->where('program_id', $programId);
                if ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->whereHas('group', fn($ssq) => $ssq->where('name', 'ilike', "%{$search}%"))
                            ->orWhereHas('program', fn($ssq) => $ssq->where('name', 'ilike', "%{$search}%"))
                            ->orWhere('education.rooms.name', 'ilike', "%{$search}%");
                    });
                }
                if ($attendanceStatus === 'completed') {
                    $q->whereRaw('(SELECT count(*) FROM education.attendances WHERE lecture_id = education.lectures.id) >= (SELECT count(*) FROM education.trainee_profiles WHERE group_id = education.lectures.group_id)');
                } elseif ($attendanceStatus === 'pending') {
                    $q->whereRaw('(SELECT count(*) FROM education.attendances WHERE lecture_id = education.lectures.id) < (SELECT count(*) FROM education.trainee_profiles WHERE group_id = education.lectures.group_id)');
                }
            })
            ->orderBy('name');

        // Paginate by Hall
        $rooms = $roomsQuery->paginate(12)->withQueryString();

        // Load filtered lectures into each room
        foreach ($rooms as $room) {
            $room->day_lectures = \Modules\Educational\Domain\Models\Lecture::query()
                ->with(['program', 'group', 'instructorProfile.user', 'sessionType'])
                ->withCount('attendances')
                ->addSelect([
                    'trainees_count' => \Modules\Educational\Domain\Models\TraineeProfile::selectRaw('count(*)')
                        ->whereColumn('group_id', 'education.lectures.group_id')
                ])
                ->where('room_id', $room->id)
                ->whereDate('starts_at', $date)
                ->when($programId, fn($q) => $q->where('program_id', $programId))
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->whereHas('group', fn($ssq) => $ssq->where('name', 'ilike', "%{$search}%"))
                            ->orWhereHas('program', fn($ssq) => $ssq->where('name', 'ilike', "%{$search}%"));
                    });
                })
                ->when($attendanceStatus === 'completed', fn($q) => $q->whereRaw('(SELECT count(*) FROM education.attendances WHERE lecture_id = education.lectures.id) >= (SELECT count(*) FROM education.trainee_profiles WHERE group_id = education.lectures.group_id)'))
                ->when($attendanceStatus === 'pending', fn($q) => $q->whereRaw('(SELECT count(*) FROM education.attendances WHERE lecture_id = education.lectures.id) < (SELECT count(*) FROM education.trainee_profiles WHERE group_id = education.lectures.group_id)'))
                ->orderBy('starts_at')
                ->get();
        }

        // Summary uses all lectures today
        $summaryBase = \Modules\Educational\Domain\Models\Lecture::whereDate('starts_at', $date);
        $summary = [
            'total' => (clone $summaryBase)->count(),
            'present' => \Modules\Educational\Domain\Models\Attendance::whereHas('lecture', fn($q) => $q->whereDate('starts_at', $date))->where('status', 'present')->count(),
            'absent' => \Modules\Educational\Domain\Models\Attendance::whereHas('lecture', fn($q) => $q->whereDate('starts_at', $date))->where('status', 'absent')->count(),
        ];

        return view('modules.educational.attendance.dashboard', compact(
            'rooms',
            'programs',
            'buildings',
            'floors',
            'date',
            'programId',
            'attendanceStatus',
            'summary',
            'search',
            'buildingId',
            'floorId'
        ));
    }

    public function overrideList(Request $request)
    {
        $settings = app(\Modules\Educational\Application\Services\EducationalSettings::class);
        $lockHours = $settings->attendanceLockHours();

        // 1. Fetch Rooms and Groups for Filter dropdowns
        $rooms = \Modules\Educational\Domain\Models\Room::orderBy('name')->get();
        $groups = \Modules\Educational\Domain\Models\Group::orderBy('name')->get();

        // 2. Query locked attendances
        $query = \Modules\Educational\Domain\Models\Attendance::query()
            ->with(['traineeProfile.user', 'lecture.room', 'lecture.group.program'])
            ->join('education.lectures', 'education.attendances.lecture_id', '=', 'education.lectures.id')
            ->select('education.attendances.*')
            ->where(function ($q) use ($lockHours) {
                // Manually locked OR automatically locked by time
                $q->whereNotNull('education.attendances.locked_at');
                if ($lockHours >= 0) {
                    $q->orWhere('education.lectures.ends_at', '<', now()->subHours($lockHours));
                }
            });

        // 3. Dynamic Filters
        if ($request->filled('date')) {
            $query->whereDate('education.lectures.starts_at', $request->date);
        }

        if ($request->filled('room_id')) {
            $query->where('education.lectures.room_id', $request->room_id);
        }

        if ($request->filled('group_id')) {
            $query->where('education.lectures.group_id', $request->group_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('traineeProfile.user', function ($sq) use ($search) {
                    $sq->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhere('military_number', 'ilike', "%{$search}%");
                });
            });
        }

        $attendances = $query->orderBy('education.lectures.starts_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('modules.educational.attendance.override', compact('attendances', 'lockHours', 'rooms', 'groups'));
    }

    public function show($lectureId)
    {
        $lecture = Lecture::with([
            'program',
            'group.trainees.user',
            'group.jobProfile.track',
            'room.floor.building.campus',
            'instructorProfile.user',
            'sessionType',
            'attendances',
            'supervisor'
        ])->findOrFail($lectureId);

        $settings = app(\Modules\Educational\Application\Services\EducationalSettings::class);
        $isSupervisor = $lecture->supervisor_id === auth()->id();
        $isGeneralSupervisor = auth()->user()->hasAnyRole($settings->globalSupervisorRoles());

        abort_if(!$isSupervisor && !$isGeneralSupervisor && auth()->user()->cannot('education.attendance.override'), 403, 'غير مسموح لك بتسجيل حضور هذه المحاضرة. يجب أن تكون المراقب المختار أو مشرفاً عاماً.');

        $lockHours = $settings->attendanceLockHours();
        $isLocked = $lockHours >= 0 && now()->isAfter($lecture->ends_at->copy()->addHours($lockHours));

        // Fetch all trainees in the group
        $trainees = $lecture->group->trainees ?? collect();

        // Map existing attendances for quick lookup
        $existingAttendances = $lecture->attendances->keyBy('trainee_profile_id');

        return view('modules.educational.attendance.mark', compact('lecture', 'trainees', 'existingAttendances', 'isLocked'));
    }

    public function store(Request $request, $lectureId)
    {
        $lecture = Lecture::findOrFail($lectureId);

        $settings = app(\Modules\Educational\Application\Services\EducationalSettings::class);
        $isSupervisor = $lecture->supervisor_id === auth()->id();
        $isGeneralSupervisor = auth()->user()->hasAnyRole($settings->globalSupervisorRoles());

        abort_if(!$isSupervisor && !$isGeneralSupervisor && auth()->user()->cannot('education.attendance.override'), 403, 'غير مسموح لك بتسجيل حضور هذه المحاضرة.');

        $settings = app(\Modules\Educational\Application\Services\EducationalSettings::class);
        $lockHours = $settings->attendanceLockHours();

        if ($lockHours >= 0 && now()->isAfter($lecture->ends_at->copy()->addHours($lockHours))) {
            return back()->with('error', 'السجل مقفل تلقائياً. لقد تجاوزت المدة المسموحة (' . $lockHours . ' ساعة) لتسجيل وتعديل الغياب بحرية. يرجى التقدم بطلب استثنائي إذا لزم الأمر.');
        }

        $validated = $request->validate([
            'attendance' => 'required|array',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
            'attendance.*.notes' => 'nullable|string|max:500',
            'attendance.*.check_in_time' => 'nullable|date_format:H:i',
        ]);

        foreach ($validated['attendance'] as $traineeId => $data) {
            \Modules\Educational\Domain\Models\Attendance::updateOrCreate(
                [
                    'lecture_id' => $lecture->id,
                    'trainee_profile_id' => $traineeId
                ],
                [
                    'status' => $data['status'],
                    'notes' => $data['notes'] ?? null,
                    'check_in_time' => $data['check_in_time'] ?? null,
                ]
            );
        }

        return redirect()->route('educational.attendance.dashboard')
            ->with('success', __('educational::messages.attendance_saved_successfully'));
    }

    public function requestOverride(Request $request, $attendanceId, \Modules\Core\Application\Services\ApprovalService $approvalService)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,excused',
            'notes' => 'required|string|min:5',
            'check_in_time' => 'nullable|date_format:H:i',
        ]);

        $attendance = \Modules\Educational\Domain\Models\Attendance::findOrFail($attendanceId);

        if (!$attendance->isLocked()) {
            return back()->with('error', 'هذا السجل غير مقفل حالياً؛ يمكنك تعديله مباشرة من صفحة رصد الحضور.');
        }

        try {
            // Initiate Approval Request
            $approvalService->requestApproval(
                approvable: $attendance,
                schema: 'education',
                action: 'override_attendance',
                metadata: [
                    'requested_changes' => [
                        'status' => $request->status,
                        'notes' => $request->notes,
                        'check_in_time' => $request->check_in_time,
                        'old_status' => $attendance->status
                    ]
                ],
                levels: 1
            );

            // Fire Event
            event(new \Modules\Educational\Domain\Events\AttendanceOverridden($attendance));

            return back()->with('success', 'تم إرسال طلب التعديل الاستثنائي بنجاح للمراجعة والاعتماد.');
        } catch (\Exception $e) {
            return back()->with('error', 'فشل إرسال الطلب: ' . $e->getMessage());
        }
    }
}
