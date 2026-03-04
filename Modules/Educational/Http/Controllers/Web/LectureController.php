<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\Lecture;
use Modules\Educational\Domain\Models\ScheduleTemplate;
use Modules\Educational\Application\Services\SchedulingService;
use Modules\Educational\Application\Exceptions\SchedulingConflictException;
use Modules\Educational\Domain\State\LectureStateMachine;
use Modules\Educational\Application\Exceptions\IllegalStateTransitionException;
use Modules\Core\Application\Services\ApprovalService;

class LectureController extends Controller
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }
    /**
     * Display a listing of lectures.
     */
    public function index(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        try {
            $carbonDate = \Carbon\Carbon::parse($date);
        } catch (\Exception $e) {
            $carbonDate = now();
            $date = $carbonDate->format('Y-m-d');
        }
        $startOfDay = $carbonDate->copy()->startOfDay();
        $endOfDay = $carbonDate->copy()->endOfDay();
        $date = $carbonDate->format('Y-m-d'); // Canonical format

        $lecturesQuery = Lecture::with([
            'program',
            'group' => function ($q) {
                $q->withCount('trainees');
            },
            'instructorProfile.user',
            'room',
            'room.floor.building',
            'sessionType',
            'formAssignments.form',
            'evaluations',
            'attendances',
            'supervisor',
        ])
            ->whereBetween('starts_at', [$startOfDay, $endOfDay])
            ->orderBy('starts_at', 'asc');

        if ($request->filled('room_ids')) {
            $lecturesQuery->whereIn('room_id', (array) $request->room_ids);
        }

        $settings = app(\Modules\Educational\Application\Services\EducationalSettings::class);
        $defaultPrograms = $settings->defaultDisplayedPrograms();
        $defaultTracks = $settings->defaultDisplayedTracks();
        $supervisorRoles = $settings->supervisorRoles();
        $globalSupervisorRoles = $settings->globalSupervisorRoles();

        $appliedProgramIds = $request->filled('program_ids') ? (array) $request->program_ids : $defaultPrograms;
        $appliedTrackIds = $request->filled('track_ids') ? (array) $request->track_ids : $defaultTracks;

        if (!empty($appliedProgramIds)) {
            $lecturesQuery->whereIn('program_id', $appliedProgramIds);
        }

        if (!empty($appliedTrackIds)) {
            $lecturesQuery->whereHas('instructorProfile', function ($sq) use ($appliedTrackIds) {
                $sq->whereIn('track_id', $appliedTrackIds);
            });
        }

        if ($request->filled('instructor_ids')) {
            $lecturesQuery->whereIn('instructor_profile_id', (array) $request->instructor_ids);
        }

        $lectures = $lecturesQuery->get();

        // Group lectures by room
        $roomsWithLectures = $lectures->groupBy('room_id');

        // Fetch filter data
        $allRooms = \Modules\Educational\Domain\Models\Room::with(['floor.building'])->orderBy('name')->get();
        $allPrograms = \Modules\Educational\Domain\Models\Program::active()->orderBy('name')->get();
        $allInstructors = \Modules\Educational\Domain\Models\InstructorProfile::with('user')->get();
        $allTracks = \Modules\Educational\Domain\Models\Track::active()->orderBy('name')->get();

        $eligibleSupervisors = collect();
        if (!empty($supervisorRoles)) {
            $eligibleSupervisors = \Modules\Users\Domain\Models\User::role($supervisorRoles)->orderBy('first_name')->get();
        }

        $pendingApprovalsCount = \Modules\Core\Domain\Models\ApprovalRequest::where('approvable_type', Lecture::class)
            ->where('status', 'pending')
            ->count();

        return view('modules.educational.lectures.index', compact(
            'lectures',
            'roomsWithLectures',
            'allRooms',
            'allPrograms',
            'allInstructors',
            'allTracks',
            'date',
            'appliedProgramIds',
            'appliedTrackIds',
            'eligibleSupervisors',
            'supervisorRoles',
            'globalSupervisorRoles',
            'pendingApprovalsCount'
        ));
    }

    /**
     * Show the form for creating/generating lectures.
     */
    public function create()
    {
        $templates = ScheduleTemplate::with(['program', 'group'])->get();
        return view('modules.educational.lectures.create', compact('templates'));
    }

    /**
     * Store and trigger generation.
     */
    public function generate(Request $request, SchedulingService $schedulingService)
    {
        $request->validate([
            'template_id' => 'required|exists:' . ScheduleTemplate::class . ',id',
            'generate_from' => 'required|date',
            'generate_to' => 'required|date|after_or_equal:generate_from',
        ]);

        $template = ScheduleTemplate::findOrFail($request->template_id);

        try {
            $generatedIds = $schedulingService->generateFromTemplate(
                $template,
                $request->generate_from,
                $request->generate_to
            );

            $count = count($generatedIds);
            if ($count === 0) {
                return back()->with('info', __('educational::messages.gen_no_new'));
            }

            return back()->with('success', __('educational::messages.gen_successfully', ['count' => $count]));

        } catch (SchedulingConflictException $e) {
            return back()->with('error', __('educational::messages.conflict_detected', ['message' => $e->getMessage()]));
        } catch (\Exception $e) {
            return back()->with('error', __('educational::messages.unexpected_error', ['message' => $e->getMessage()]));
        }
    }
    /**
     * Store a manually created (unscheduled) lecture.
     * Accepts date (hidden) + time_start + time_end separately.
     * Optionally assigns an evaluation form.
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:pgsql.education.rooms,id',
            'lecture_date' => 'required|date_format:Y-m-d',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'subject' => 'nullable|string|max:255',
            'program_id' => 'nullable|exists:pgsql.education.programs,id',
            'group_id' => 'nullable|exists:pgsql.education.groups,id',
            'instructor_profile_id' => 'nullable|exists:pgsql.education.instructor_profiles,id',
            'session_type_id' => 'nullable|exists:pgsql.education.session_types,id',
            'form_id' => 'nullable|exists:pgsql.education.evaluation_forms,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Combine date + time
        $startsAt = \Carbon\Carbon::parse($request->lecture_date . ' ' . $request->time_start . ':00');
        $endsAt = \Carbon\Carbon::parse($request->lecture_date . ' ' . $request->time_end . ':00');

        // Double-check server-side conflict (even if frontend warned)
        $conflict = Lecture::where('room_id', $request->room_id)
            ->where(function ($q) use ($startsAt, $endsAt) {
                $q->where(function ($q2) use ($startsAt, $endsAt) {
                    // New lecture starts inside an existing one
                    $q2->where('starts_at', '<', $endsAt)
                        ->where('ends_at', '>', $startsAt);
                });
            })
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($conflict) {
            return back()
                ->withInput()
                ->with('error', 'لا يمكن إنشاء المحاضرة: يوجد تعارض في وقت القاعة المختارة. اختر وقتاً آخر.');
        }

        $lecture = Lecture::create([
            'room_id' => $request->room_id,
            'program_id' => $request->program_id,
            'group_id' => $request->group_id,
            'instructor_profile_id' => $request->instructor_profile_id,
            'session_type_id' => $request->session_type_id,
            'subject' => $request->subject,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'notes' => $request->notes,
            'status' => 'scheduled',
            'schedule_template_id' => null,
        ]);

        // Optionally assign an evaluation form to the lecture
        if ($request->filled('form_id')) {
            $lecture->formAssignments()->create([
                'form_id' => $request->form_id,
                'is_active' => true,
            ]);
        }

        return redirect()
            ->route('educational.lectures.index', ['date' => $startsAt->format('Y-m-d')])
            ->with('success', 'تم إنشاء المحاضرة الإضافية بنجاح.');
    }

    /**
     * API: Return booked time slots for a room on a given date.
     * Used by the frontend to prevent selecting conflicting times.
     */
    public function bookedSlots(Request $request, $roomId)
    {
        $date = $request->query('date', now()->format('Y-m-d'));

        try {
            $carbonDate = \Carbon\Carbon::parse($date);
        } catch (\Exception $e) {
            return response()->json(['slots' => []]);
        }

        $slots = Lecture::where('room_id', $roomId)
            ->whereBetween('starts_at', [
                $carbonDate->copy()->startOfDay(),
                $carbonDate->copy()->endOfDay(),
            ])
            ->when($request->query('exclude'), fn($q, $exclude) => $q->where('id', '!=', (int) $exclude))
            ->whereNotIn('status', ['cancelled'])
            ->get(['starts_at', 'ends_at', 'subject', 'status'])
            ->map(fn($l) => [
                'start' => $l->starts_at->format('H:i'),
                'end' => $l->ends_at->format('H:i'),
                'subject' => $l->subject ?? '—',
                'status' => $l->status,
            ]);

        return response()->json(['slots' => $slots]);
    }

    /**
     * Update the core details of a lecture (subject, time, instructor, group, form, notes).
     * Used by the inline edit modal on the lectures index page.
     */
    public function updateDetails(Request $request, Lecture $lecture)
    {
        $request->validate([
            'lecture_date' => 'required|date_format:Y-m-d',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'subject' => 'nullable|string|max:255',
            'room_id' => 'required|exists:pgsql.education.rooms,id',
            'program_id' => 'nullable|exists:pgsql.education.programs,id',
            'group_id' => 'nullable|exists:pgsql.education.groups,id',
            'instructor_profile_id' => 'nullable|exists:pgsql.education.instructor_profiles,id',
            'session_type_id' => 'nullable|exists:pgsql.education.session_types,id',
            'form_id' => 'nullable|exists:pgsql.education.evaluation_forms,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $startsAt = \Carbon\Carbon::parse($request->lecture_date . ' ' . $request->time_start . ':00');
        $endsAt = \Carbon\Carbon::parse($request->lecture_date . ' ' . $request->time_end . ':00');

        // Check room conflict excluding current lecture
        $conflict = Lecture::where('room_id', $request->room_id)
            ->where('id', '!=', $lecture->id)
            ->where(function ($q) use ($startsAt, $endsAt) {
                $q->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt);
            })
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($conflict) {
            return back()
                ->withInput()
                ->with('error', 'لا يمكن حفظ التعديل: يوجد تعارض في وقت القاعة المختارة.');
        }

        // Update lecture fields
        $lecture->update([
            'room_id' => $request->room_id,
            'program_id' => $request->program_id,
            'group_id' => $request->group_id,
            'instructor_profile_id' => $request->instructor_profile_id,
            'session_type_id' => $request->session_type_id,
            'subject' => $request->subject,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'notes' => $request->notes,
        ]);

        // Handle evaluation form assignment
        // Deactivate all current assignments first, then assign the new one if provided
        if ($request->filled('form_id')) {
            $lecture->formAssignments()->update(['is_active' => false]);
            $existing = $lecture->formAssignments()->where('form_id', $request->form_id)->first();
            if ($existing) {
                $existing->update(['is_active' => true]);
            } else {
                $lecture->formAssignments()->create([
                    'form_id' => $request->form_id,
                    'is_active' => true,
                ]);
            }
        } elseif ($request->has('form_id')) {
            // form_id was explicitly sent as empty → remove all active assignments
            $lecture->formAssignments()->update(['is_active' => false]);
        }

        return redirect()
            ->route('educational.lectures.index', ['date' => $startsAt->format('Y-m-d')])
            ->with('success', 'تم تحديث تفاصيل المحاضرة بنجاح.');
    }

    public function assignSupervisor(Request $request, $id)
    {
        $settings = app(\Modules\Educational\Application\Services\EducationalSettings::class);
        $isGlobalManager = auth()->user()->hasAnyRole($settings->globalSupervisorRoles()) || auth()->user()->hasRole('super-admin');
        abort_if(!$isGlobalManager, 403, 'غير مسموح لك بهذا الإجراء. يجب أن تكون مشرفاً عاماً.');

        $lecture = Lecture::findOrFail($id);
        $request->validate([
            'supervisor_id' => 'nullable|exists:users,id'
        ]);

        $lecture->update(['supervisor_id' => $request->supervisor_id]);
        return back()->with('success', 'تم تعيين مراقب المحاضرة بنجاح.');
    }

    public function batchAssignSupervisor(Request $request)
    {
        $settings = app(\Modules\Educational\Application\Services\EducationalSettings::class);
        $isGlobalManager = auth()->user()->hasAnyRole($settings->globalSupervisorRoles()) || auth()->user()->hasRole('super-admin');
        abort_if(!$isGlobalManager, 403, 'غير مسموح لك بهذا الإجراء. يجب أن تكون مشرفاً عاماً.');

        $request->validate([
            'lecture_ids' => 'required|array',
            'lecture_ids.*' => 'exists:' . \Modules\Educational\Domain\Models\Lecture::class . ',id',
            'supervisor_id' => 'nullable|exists:users,id'
        ]);

        Lecture::whereIn('id', $request->lecture_ids)->update(['supervisor_id' => $request->supervisor_id]);

        return back()->with('success', 'تم تعيين مراقب للمحاضرات المختارة بنجاح (' . count($request->lecture_ids) . ').');
    }

    /**
     * Update the specified lecture in storage.
     */
    public function update(Request $request, $id)
    {
        $settings = app(\Modules\Educational\Application\Services\EducationalSettings::class);
        $isGlobalManager = auth()->user()->hasAnyRole($settings->globalSupervisorRoles()) || auth()->user()->hasRole('super-admin');
        abort_if(!$isGlobalManager, 403, 'غير مسموح لك بهذا الإجراء. يجب أن تكون مشرفاً عاماً.');

        $lecture = Lecture::findOrFail($id);
        $newStatus = $request->status;

        $request->validate([
            'status' => 'required|in:scheduled,running,completed,cancelled,rescheduled',
            'reason' => 'required_if:status,cancelled|string|max:500'
        ]);

        // If not super-admin, and attempting to cancel, we need approval
        if ($newStatus === 'cancelled' && !auth()->user()->hasRole('super-admin')) {
            if ($lecture->pendingApprovalRequest()) {
                return back()->with('warning', 'يوجد بالفعل طلب معلق لهذه المحاضرة.');
            }

            $this->approvalService->requestApproval(
                approvable: $lecture,
                schema: 'educational',
                action: 'cancel_lecture',
                metadata: [
                    'reason' => $request->reason,
                    'requested_status' => 'cancelled'
                ],
                levels: 1
            );

            return back()->with('info', 'تم إرسال طلب إلغاء المحاضرة للموافقة.');
        }

        $sm = new LectureStateMachine($lecture);

        try {
            switch ($newStatus) {
                case 'running':
                    $sm->transitionToRunning();
                    break;
                case 'completed':
                    $sm->transitionToCompleted();
                    break;
                case 'cancelled':
                    $sm->transitionToCancelled();
                    break;
                default:
                    $lecture->update(['status' => $newStatus]);
            }

            return back()->with('success', __('educational::messages.lecture_status_updated'));
        } catch (IllegalStateTransitionException $e) {
            return back()->with('error', 'Update Failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified lecture from storage.
     */
    public function destroy(Request $request, $id)
    {
        $settings = app(\Modules\Educational\Application\Services\EducationalSettings::class);
        $isGlobalManager = auth()->user()->hasAnyRole($settings->globalSupervisorRoles()) || auth()->user()->hasRole('super-admin');
        abort_if(!$isGlobalManager, 403, 'غير مسموح لك بهذا الإجراء. يجب أن تكون مشرفاً عاماً.');

        $lecture = Lecture::findOrFail($id);

        if (!auth()->user()->hasRole('super-admin')) {
            if ($lecture->pendingApprovalRequest()) {
                return back()->with('warning', 'يوجد بالفعل طلب حذف معلق لهذه المحاضرة.');
            }

            $request->validate(['reason' => 'required|string|max:500']);

            $this->approvalService->requestApproval(
                approvable: $lecture,
                schema: 'educational',
                action: 'delete_lecture',
                metadata: [
                    'reason' => $request->reason
                ],
                levels: 1
            );

            return back()->with('info', 'تم إرسال طلب حذف المحاضرة للموافقة.');
        }

        $lecture->delete();

        return back()->with('success', __('educational::messages.lecture_deleted'));
    }
}
