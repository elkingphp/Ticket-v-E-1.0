<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Modules\Educational\Domain\Models\EvaluationForm;
use Modules\Educational\Domain\Models\Lecture;
use Modules\Educational\Domain\Models\LectureEvaluation;
use Modules\Educational\Domain\Models\LectureFormAssignment;
use Modules\Educational\Domain\Models\TraineeProfile;

/**
 * LectureAssignmentController
 *
 * Handles Phase C: assigning evaluation forms to lectures & filling evaluations.
 *
 * Routes:
 *   POST   /educational/evaluations/lectures/{lecture}/assign     → assign
 *   DELETE /educational/evaluations/assignments/{assignment}      → revoke
 *   GET    /educational/evaluations/assignments/{assignment}/fill → fill (evaluation form UI)
 *   POST   /educational/evaluations/assignments/{assignment}/fill → submit
 */
class LectureAssignmentController extends Controller
{
    use AuthorizesRequests;

    // ─── Assign a Form to a Lecture ───────────────────────────────────────────

    /**
     * Assign (or update) an evaluation form to a specific lecture.
     * Called from the lectures/index page via Modal.
     */
    public function assign(Request $request, Lecture $lecture)
    {
        $this->authorize('manage', \Modules\Educational\Domain\Models\EvaluationForm::class);

        $validated = $request->validate([
            'form_id' => [
                'required',
                'exists:' . EvaluationForm::class . ',id',
                // Only published forms can be assigned
                function ($attr, $value, $fail) {
                    $form = EvaluationForm::find($value);
                    if (!$form || $form->status !== 'published') {
                        $fail('يمكن تعيين النماذج المنشورة فقط.');
                    }
                },
            ],
        ]);

        $allow_evaluator_types = [];
        $form = EvaluationForm::with('formType')->find($validated['form_id']);
        if ($form && $form->formType) {
            $allow_evaluator_types = $form->formType->allowed_roles ?? [];
        }

        $assignment = LectureFormAssignment::updateOrCreate(
            ['lecture_id' => $lecture->id, 'form_id' => $validated['form_id']],
            [
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'allow_evaluator_types' => $allow_evaluator_types,
                'is_active' => true,
            ]
        );

        event(new \Modules\Educational\Domain\Events\FormAssignedToLecture($assignment));

        return back()
            ->with('success', 'تم تعيين نموذج التقييم للمحاضرة بنجاح.');
    }

    /**
     * Revoke (deactivate) a form assignment from a lecture.
     */
    public function revoke(LectureFormAssignment $assignment)
    {
        $this->authorize('manage', \Modules\Educational\Domain\Models\EvaluationForm::class);

        $assignment->update(['is_active' => false]);

        return redirect()->back()->with('success', 'تم إلغاء تعيين النموذج.');
    }

    // ─── Fill Evaluation (Trainee / Observer UI) ──────────────────────────────

    /**
     * Show the evaluation fill form.
     * Validates that the user is allowed to fill this assignment.
     */
    public function fill(LectureFormAssignment $assignment)
    {
        // 1. Assignment must be active
        abort_if(!$assignment->is_active, 403, 'هذا النموذج غير متاح حالياً.');

        // 2. Resolve current evaluator identity & check permissions
        [$evaluatorType, $evaluatorId, $evaluatorRole, $isAllowed] = $this->resolveEvaluator($assignment);

        // 3. Check evaluator is allowed
        abort_if(!$isAllowed, 403, 'غير مسموح لك بتعبئة هذا النموذج.');

        // 4. Check for duplicate (already submitted)
        $alreadySubmitted = LectureEvaluation::where([
            'lecture_id' => $assignment->lecture_id,
            'form_id' => $assignment->form_id,
            'evaluator_type' => $evaluatorType,
            'evaluator_id' => $evaluatorId,
        ])->exists();

        if ($alreadySubmitted) {
            return back()
                ->with('info', 'لقد قمت بتعبئة هذا النموذج مسبقاً. لا يمكن التقييم مرتين.');
        }

        $assignment->load('lecture', 'form.questions');

        return view('modules.educational.evaluations.fill', compact(
            'assignment',
            'evaluatorRole'
        ));
    }

    /**
     * Submit a completed evaluation.
     *
     * Security:
     *  • DB::transaction + lockForUpdate → kills race conditions
     *  • form_snapshot captured at submission time
     *  • snapshot_hash = SHA-256(json_encode(snapshot)) for audit integrity
     *  • submitted_at is authoritative — created_at is never used for business logic
     */
    public function submit(Request $request, LectureFormAssignment $assignment)
    {
        abort_if(!$assignment->is_active, 403, 'هذا النموذج غير متاح حالياً.');

        [$evaluatorType, $evaluatorId, $evaluatorRole, $isAllowed] = $this->resolveEvaluator($assignment);

        abort_if(!$isAllowed, 403, 'غير مسموح لك بتعبئة هذا النموذج.');

        // Load form with questions for validation
        $assignment->load('form.questions');
        $form = $assignment->form;
        $questions = $form->questions;

        // Build dynamic validation rules per question type
        $rules = ['overall_comments' => 'nullable|string|max:1000'];
        $ratingIds = [];
        $textIds = [];
        $boolIds = [];
        $choiceIds = [];

        foreach ($questions as $q) {
            $field = "answers.{$q->id}";
            $required = $q->is_required ? 'required' : 'nullable';

            if ($q->type === 'rating_1_to_5') {
                $rules[$field] = "{$required}|integer|min:1|max:5";
                $ratingIds[] = $q->id;
            } elseif ($q->type === 'text') {
                $rules[$field] = "{$required}|string|max:2000";
                $textIds[] = $q->id;
            } elseif ($q->type === 'boolean') {
                $rules[$field] = "{$required}|in:0,1";
                $boolIds[] = $q->id;
            } elseif ($q->type === 'multiple_choice') {
                $validOptions = $q->getOptionsArray();
                $rules[$field] = "{$required}|string|in:" . implode(',', array_map('addslashes', $validOptions));
                $choiceIds[] = $q->id;
            }
        }

        $validated = $request->validate($rules);
        $answers = $validated['answers'] ?? [];

        return DB::transaction(function () use ($assignment, $form, $questions, $answers, $evaluatorType, $evaluatorId, $evaluatorRole, $ratingIds, $textIds, $boolIds, $choiceIds) {
            // ── Race Condition Guard ────────────────────────────────────────
            // Lock & check — prevents duplicate submission under concurrent load
            $exists = LectureEvaluation::where([
                'lecture_id' => $assignment->lecture_id,
                'form_id' => $assignment->form_id,
                'evaluator_type' => $evaluatorType,
                'evaluator_id' => $evaluatorId,
            ])->lockForUpdate()->exists();

            if ($exists) {
                return back()
                    ->with('info', 'تم تسجيل تقييمك مسبقاً.');
            }

            // ── Build Snapshot ──────────────────────────────────────────────
            // Capture form structure at submission time — snapshot is immutable.
            $snapshot = $form->buildSnapshot();
            $snapshotHash = LectureEvaluation::generateSnapshotHash($snapshot);

            // ── Create Evaluation Record ────────────────────────────────────
            $evaluation = LectureEvaluation::create([
                'lecture_id' => $assignment->lecture_id,
                'form_id' => $assignment->form_id,
                'evaluator_type' => $evaluatorType,
                'evaluator_id' => $evaluatorId,
                'evaluator_role' => $evaluatorRole,
                'form_snapshot' => $snapshot,
                'snapshot_hash' => $snapshotHash,
                'overall_comments' => request('overall_comments'),
                'submitted_at' => now(), // authoritative — never use created_at
            ]);

            // ── Save Answers ────────────────────────────────────────────────
            foreach ($questions as $q) {
                $val = $answers[$q->id] ?? null;
                if ($val === null || $val === '')
                    continue;

                \Modules\Educational\Domain\Models\EvaluationAnswer::create([
                    'lecture_evaluation_id' => $evaluation->id,
                    'question_id' => $q->id,
                    'answer_rating' => in_array($q->id, $ratingIds) ? (int) $val : null,
                    'answer_value' => in_array($q->id, $ratingIds) ? null : (string) $val,
                ]);
            }

            // ── Dispatch Domain Event ───────────────────────────────────────
            $evaluation->load('answers');
            if (class_exists(\Modules\Educational\Domain\Events\EvaluationSubmitted::class)) {
                event(new \Modules\Educational\Domain\Events\EvaluationSubmitted($evaluation));
            }

            return redirect()
                ->route('educational.lectures.index', ['date' => $assignment->lecture->starts_at->format('Y-m-d')])
                ->with('success', 'تم إرسال تقييمك بنجاح. شكراً لمشاركتك.');
        });
    }

    /**
     * Show a specific evaluation result.
     * Accessible by admins/managers, or the evaluator themselves (optional).
     */
    public function showEvaluation(LectureEvaluation $evaluation)
    {
        $this->authorize('viewResults', $evaluation->form);

        $evaluation->load([
            'lecture.group',
            'lecture.room.floor.building.campus',
            'lecture.instructorProfile.user',
            'lecture.instructorProfile.companies',
            'lecture.instructorProfile.track',
            'form',
            'answers.question'
        ]);

        // Use a snapshot-aware view or generic one
        return view('modules.educational.evaluations.view_submission', compact('evaluation'));
    }

    /**
     * View results for a specific lecture assignment.
     * Redirects to the main results dashboard filtered by this lecture.
     */
    public function viewAssignmentResults(LectureFormAssignment $assignment)
    {
        $this->authorize('viewResults', $assignment->form);

        return redirect()->route('educational.evaluations.forms.results', [
            'form' => $assignment->form_id,
            'lecture_id' => $assignment->lecture_id
        ]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Resolve the current user's evaluator identity and determine if they are allowed.
     *
     * Returns: [evaluator_type (FQCN), evaluator_id, evaluator_role, is_allowed]
     *
     * Handles both Spatie Roles and legacy string flags ('trainee', 'observer').
     */
    private function resolveEvaluator(LectureFormAssignment $assignment): array
    {
        $user = auth()->user();

        // Fetch up-to-date allowed roles from the form type if available
        $assignment->loadMissing('form.formType');
        if ($assignment->form && $assignment->form->formType) {
            $allowedRoles = $assignment->form->formType->allowed_roles ?? [];
        } else {
            // Fallback to locally cached config, if no form type is linked
            $allowedRoles = $assignment->allow_evaluator_types ?? [];
        }

        $allowedRolesLower = array_map('strtolower', $allowedRoles);

        // Check if user has a trainee profile in the lecture's group
        $traineeProfile = TraineeProfile::where('user_id', $user->id)
            ->where('group_id', $assignment->lecture?->group_id)
            ->first();

        $settings = app(\Modules\Educational\Application\Services\EducationalSettings::class);
        $generalSupervisorRoles = $settings->globalSupervisorRoles();
        $isGeneralSupervisor = $user->hasAnyRole($generalSupervisorRoles) || $user->hasRole('super-admin');

        // 1. Authorize Evaluator
        // Support Spatie roles logic naturally but with case-insensitivity
        $userRoleNames = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
        $hasSpatieRole = count(array_intersect($userRoleNames, $allowedRolesLower)) > 0;

        // Fallbacks for legacy assignments that might still contain fixed strings
        $hasLegacyTrainee = in_array('trainee', $allowedRolesLower, true);
        $hasLegacyObserver = in_array('observer', $allowedRolesLower, true);

        // 1.5 Check if user is the assigned supervisor for this lecture
        $isSupervisor = $assignment->lecture?->supervisor_id === $user->id;

        $isAllowed = false;

        if ($traineeProfile && ($hasLegacyTrainee || in_array('trainee', $userRoleNames))) {
            // Trainees are allowed if they have a profile in the group and the form allows trainees
            $isAllowed = true;
        } elseif ($hasLegacyObserver || $hasSpatieRole) {
            // STAFF (Observer/Spatie Role) check:
            // Must be EITHER the specifically assigned supervisor OR a General Supervisor
            if ($isSupervisor || $isGeneralSupervisor) {
                $isAllowed = true;
            }
        }

        // 2. Identify Evaluator Identity
        if ($traineeProfile && ($hasLegacyTrainee || in_array('trainee', $userRoleNames))) {
            $evaluatorType = TraineeProfile::class;
            $evaluatorId = $traineeProfile->id;
            $evaluatorRole = 'trainee';
        } else {
            $evaluatorType = \Modules\Users\Domain\Models\User::class;
            $evaluatorId = $user->id;
            // Determine logical role string
            if ($isGeneralSupervisor) {
                $evaluatorRole = 'admin'; // General supervisor acts as admin/manager
            } elseif ($isSupervisor) {
                $evaluatorRole = 'observer'; // Specifically assigned supervisor acts as observer
            } else {
                $evaluatorRole = 'observer';
            }
        }

        return [$evaluatorType, $evaluatorId, $evaluatorRole, $isAllowed];
    }
}
