<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\ScheduleTemplate;
use Modules\Educational\Domain\Models\Program;
use Modules\Educational\Domain\Models\Group;
use Modules\Educational\Domain\Models\InstructorProfile;
use Modules\Educational\Domain\Models\Room;
use Modules\Educational\Domain\Models\SessionType;
use Modules\Educational\Domain\Models\EvaluationForm;
use Modules\Core\Application\Services\ApprovalService;
use Exception;
use Illuminate\Validation\ValidationException;

class ScheduleTemplateController extends Controller
{
    public function index(Request $request)
    {
        // For the left sidebar: Programs and their active Groups
        $programsList = Program::with([
            'groups' => function ($q) {
                // Optional: filter by active groups if needed
                $q->orderBy('name');
            }
        ])->orderBy('name')->get();

        $query = ScheduleTemplate::with(['program', 'group', 'instructorProfile.user', 'room.floor.building.campus', 'sessionType']);

        // Check if we are selecting a specific group for drill-down
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
            // In drill-down, it's better to show all templates for the group (or a higher limit)
            $templates = $query->orderBy('day_of_week')->orderBy('start_time')->get();
        } else {
            // Fallback for general view / advanced filters
            if ($request->filled('program_id')) {
                $query->where('program_id', $request->program_id);
            }
            if ($request->filled('session_type_id')) {
                $query->where('session_type_id', $request->session_type_id);
            }
            if ($request->filled('room_id')) {
                $query->where('room_id', $request->room_id);
            }
            if ($request->filled('start_time')) {
                $query->whereTime('start_time', '=', substr($request->start_time, 0, 5) . ':00');
            }
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active' ? 1 : 0);
            }

            $templates = $query->latest()->paginate(15)->withQueryString();
        }

        $programs = Program::all();
        $sessionTypes = SessionType::all();
        $rooms = Room::all();
        $evaluationForms = EvaluationForm::published()->get();

        // AJAX response for when a group is clicked dynamically
        if ($request->ajax()) {
            return response()->json([
                'templates' => $templates
            ]);
        }

        return view('educational::schedules.index', compact('templates', 'programsList', 'programs', 'sessionTypes', 'rooms', 'evaluationForms'));
    }

    public function create()
    {
        $programs = Program::whereIn('status', ['published', 'running'])->get();
        $instructors = InstructorProfile::with('user')->get();
        $rooms = Room::with('floor.building.campus')->where('room_status', 'active')->get();
        $sessionTypes = SessionType::where('is_active', true)->get();
        $evaluationForms = EvaluationForm::published()->get();

        return view('educational::schedules.create', compact('programs', 'instructors', 'rooms', 'sessionTypes', 'evaluationForms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'program_id' => 'required|exists:' . Program::class . ',id',
            'group_id' => 'required|exists:' . Group::class . ',id',
            'session_type_id' => 'required|exists:' . SessionType::class . ',id',
            'subject' => 'nullable|string|max:255',
            'instructor_profile_id' => 'required|exists:' . InstructorProfile::class . ',id',
            'room_id' => 'required|exists:' . Room::class . ',id',
            'day_of_week' => 'required|integer|between:0,6',
            'recurrence_type' => 'required|in:weekly,biweekly_even,biweekly_odd',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after_or_equal:effective_from',
            'is_active' => 'boolean',
            'evaluation_form_id' => 'nullable|exists:' . EvaluationForm::class . ',id',
        ]);

        $warnings = $this->checkScheduleOverlap($request);

        if (!empty($validated['evaluation_form_id'])) {
            $form = EvaluationForm::with('formType')->find($validated['evaluation_form_id']);
            $validated['allow_evaluator_types'] = $form->formType->allowed_roles ?? [];
        } else {
            $validated['allow_evaluator_types'] = null;
        }

        ScheduleTemplate::create($validated);

        $msg = __('educational::messages.template_saved');
        if (!empty($warnings)) {
            $msg .= ' (مع وجود تحذيرات: ' . implode(' - ', $warnings) . ')';
            return redirect()->route('educational.schedules.index')->with('warning', $msg);
        }

        return redirect()->route('educational.schedules.index')->with('success', $msg);
    }

    public function edit($id)
    {
        $template = ScheduleTemplate::findOrFail($id);
        $programs = Program::whereIn('status', ['published', 'running'])->get();
        $instructors = InstructorProfile::with('user')->get();
        $rooms = Room::with('floor.building.campus')->where('room_status', 'active')->get();
        $groups = Group::where('program_id', $template->program_id)->get();
        $sessionTypes = SessionType::where('is_active', true)->get();
        $evaluationForms = EvaluationForm::published()->get();

        return view('educational::schedules.edit', compact('template', 'programs', 'instructors', 'rooms', 'groups', 'sessionTypes', 'evaluationForms'));
    }

    public function update(Request $request, $id)
    {
        $template = ScheduleTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'program_id' => 'required|exists:' . Program::class . ',id',
            'group_id' => 'required|exists:' . Group::class . ',id',
            'session_type_id' => 'required|exists:' . SessionType::class . ',id',
            'subject' => 'nullable|string|max:255',
            'instructor_profile_id' => 'required|exists:' . InstructorProfile::class . ',id',
            'room_id' => 'required|exists:' . Room::class . ',id',
            'day_of_week' => 'required|integer|between:0,6',
            'recurrence_type' => 'required|in:weekly,biweekly_even,biweekly_odd',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after_or_equal:effective_from',
            'is_active' => 'boolean',
            'evaluation_form_id' => 'nullable|exists:' . EvaluationForm::class . ',id',
        ]);

        $warnings = $this->checkScheduleOverlap($request, $id);

        if (!empty($validated['evaluation_form_id'])) {
            $form = EvaluationForm::with('formType')->find($validated['evaluation_form_id']);
            $validated['allow_evaluator_types'] = $form->formType->allowed_roles ?? [];
        } else {
            $validated['allow_evaluator_types'] = null;
        }

        $template->update($validated);

        $msg = __('educational::messages.template_updated');
        if (!empty($warnings)) {
            $msg .= ' (مع وجود تحذيرات: ' . implode(' - ', $warnings) . ')';
            return redirect()->route('educational.schedules.index')->with('warning', $msg);
        }

        return redirect()->route('educational.schedules.index')->with('success', $msg);
    }

    public function destroy($id, ApprovalService $approvalService)
    {
        $template = ScheduleTemplate::findOrFail($id);

        if ($template->pendingApprovalRequest()) {
            return redirect()->back()->with('error', 'يوجد طلب معلق لحذف هذا الجدول بالفعل.');
        }

        try {
            $approvalService->requestApproval(
                approvable: $template,
                schema: 'education',
                action: 'delete',
                metadata: [
                    'name' => $template->name ?? 'جدول بدون اسم',
                    'program_name' => collect([$template->program])->pluck('name')->first() ?? 'غير محدد',
                    'group_name' => collect([$template->group])->pluck('name')->first() ?? 'غير محدد',
                    'reason' => 'حذف النموذج'
                ],
                levels: 1
            );

            return redirect()->route('educational.schedules.index')->with('success', 'تم إرسال طلب الحذف للمراجعة والموافقة.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'فشل في إرسال طلب الحذف: ' . $e->getMessage());
        }
    }

    public function getGroups($programId)
    {
        $groups = Group::where('program_id', $programId)->get();
        return response()->json($groups);
    }

    private function checkScheduleOverlap(Request $request, $ignoreId = null)
    {
        $reqStartTime = \Carbon\Carbon::parse($request->start_time)->format('H:i:s');
        $reqEndTime = \Carbon\Carbon::parse($request->end_time)->format('H:i:s');

        $baseQuery = ScheduleTemplate::where('day_of_week', $request->day_of_week)
            ->where(function ($q) use ($reqStartTime, $reqEndTime) {
                // Time overlap logic: StartA < EndB and EndA > StartB
                $q->where('start_time', '<', $reqEndTime)
                    ->where('end_time', '>', $reqStartTime);
            })
            ->where(function ($q) use ($request) {
                // If either is 'weekly', they overlap. Otherwise, they must be the same recurrence type.
                $q->where('recurrence_type', 'weekly')
                    ->orWhere('recurrence_type', $request->recurrence_type);
                if ($request->recurrence_type === 'weekly') {
                    $q->orWhereNotNull('recurrence_type');
                }
            });

        if ($ignoreId) {
            $baseQuery->where('id', '!=', $ignoreId);
        }

        // 1. Group Overlap (Blocker)
        $groupOverlap = (clone $baseQuery)->where('group_id', $request->group_id)->exists();
        if ($groupOverlap) {
            throw ValidationException::withMessages([
                'start_time' => __('educational::messages.schedule_overlap') ?? 'عذراً.. يوجد تعارض في التوقيت مع محاضرة أخرى لنفس المجموعة.',
            ]);
        }

        $warnings = [];

        // 2. Instructor Overlap (Warning)
        if ($request->filled('instructor_profile_id')) {
            $instructorOverlap = (clone $baseQuery)->where('instructor_profile_id', $request->instructor_profile_id)->exists();
            if ($instructorOverlap) {
                $warnings[] = __('educational::messages.instructor_overlap_warning') ?? 'المحاضر مشغول في محاضرة أخرى';
            }
        }

        // 3. Room Overlap (Warning)
        if ($request->filled('room_id')) {
            $roomOverlap = (clone $baseQuery)->where('room_id', $request->room_id)->exists();
            if ($roomOverlap) {
                $warnings[] = __('educational::messages.room_overlap_warning') ?? 'القاعة مستخدمة بالفعل لمحاضرة أخرى';
            }
        }

        return $warnings;
    }
}
