<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Modules\Educational\Domain\Models\EvaluationForm;
use Modules\Educational\Domain\Models\EvaluationQuestion;
use Modules\Educational\Domain\Models\TraineeProfile;
use Modules\Educational\Domain\Models\LectureEvaluation;

/**
 * EvaluationController (Web)
 *
 * Handles all admin-facing CRUD for EvaluationForms and their Questions.
 * Authorization is enforced via EvaluationFormPolicy (registered in ServiceProvider).
 * All destructive/lifecycle actions are guarded at both Policy and Model level.
 */
class EvaluationController extends Controller
{
    use AuthorizesRequests;

    // ─── Forms CRUD ───────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('viewAny', EvaluationForm::class);

        $query = EvaluationForm::with(['formType'])
            ->withCount('questions')
            ->withCount('lectureEvaluations');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('form_type_id', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
        }

        $forms = $query->orderByDesc('id')->paginate(15);
        $formTypes = \Modules\Educational\Domain\Models\EvaluationType::where('is_active', true)->get();

        return view('modules.educational.evaluations.index', compact('forms', 'formTypes'));
    }

    public function create()
    {
        $this->authorize('create', EvaluationForm::class);
        $formTypes = \Modules\Educational\Domain\Models\EvaluationType::where('is_active', true)->get();
        return view('modules.educational.evaluations.create', compact('formTypes'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', EvaluationForm::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'form_type_id' => ['required', Rule::exists(\Modules\Educational\Domain\Models\EvaluationType::class, 'id')],
            'description' => 'nullable|string|max:2000',
        ]);

        $evalType = \Modules\Educational\Domain\Models\EvaluationType::findOrFail($validated['form_type_id']);
        // Map slug to the legacy `type` enum constraint allowed values
        $typeMap = [
            'lecture_feedback' => 'lecture_feedback',
            'course_evaluation' => 'course_evaluation',
            'instructor_evaluation' => 'instructor_evaluation',
        ];
        $validated['type'] = $typeMap[$evalType->slug] ?? 'general';

        $form = EvaluationForm::create(array_merge($validated, ['status' => 'draft']));

        if ($request->has('questions')) {
            $this->syncQuestions($form, $request->questions);
        }

        return redirect()
            ->route('educational.evaluations.forms.edit', $form)
            ->with('success', 'تم إنشاء النموذج كمسودة. يمكنك الآن إضافة الأسئلة.');
    }

    public function show(EvaluationForm $form)
    {
        $this->authorize('view', $form);
        $form->load('questions');
        return view('modules.educational.evaluations.show', compact('form'));
    }

    public function edit(EvaluationForm $form)
    {
        $this->authorize('update', $form);
        $form->load('questions');
        $formTypes = \Modules\Educational\Domain\Models\EvaluationType::where('is_active', true)->get();
        return view('modules.educational.evaluations.edit', compact('form', 'formTypes'));
    }

    public function update(Request $request, EvaluationForm $form)
    {
        $this->authorize('update', $form);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'form_type_id' => ['required', Rule::exists(\Modules\Educational\Domain\Models\EvaluationType::class, 'id')],
            'description' => 'nullable|string|max:2000',
        ]);

        $evalType = \Modules\Educational\Domain\Models\EvaluationType::findOrFail($validated['form_type_id']);
        // Map slug to the legacy `type` enum constraint allowed values
        $typeMap = [
            'lecture_feedback' => 'lecture_feedback',
            'course_evaluation' => 'course_evaluation',
            'instructor_evaluation' => 'instructor_evaluation',
        ];
        $validated['type'] = $typeMap[$evalType->slug] ?? 'general';

        $form->update($validated);

        return redirect()
            ->route('educational.evaluations.forms.edit', $form)
            ->with('success', 'تم حفظ التعديلات.');
    }

    public function destroy(EvaluationForm $form)
    {
        $this->authorize('delete', $form);
        $form->delete();

        return redirect()
            ->route('educational.evaluations.forms.index')
            ->with('success', 'تم حذف النموذج بنجاح.');
    }

    // ─── Lifecycle ────────────────────────────────────────────────────────────

    public function publish(EvaluationForm $form)
    {
        $this->authorize('publish', $form);

        try {
            $form->publish();
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('educational.evaluations.forms.index')
            ->with('success', 'تم نشر النموذج بنجاح. لا يمكن تعديله الآن.');
    }

    public function archive(EvaluationForm $form)
    {
        $this->authorize('archive', $form);

        try {
            $form->archive();
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('educational.evaluations.forms.index')
            ->with('success', 'تم أرشفة النموذج.');
    }

    // ─── Results Dashboard (Phase D) ──────────────────────────────────────────

    /**
     * Full results dashboard with:
     *  • overall / trainee / observer averages
     *  • trainee_completion_rate & observer_completion_rate (separated)
     *  • trend over time → Line Chart data
     *  • role comparison per question → Bar Chart data
     *  • per-question distribution → mini distribution charts
     *  • Red Flag detection: observer_avg < 3
     *  • Pending Evaluations: eligible trainees who haven't submitted
     *  • Snapshot Integrity check: detects if form was modified since evaluations began
     *
     * Performance: Logic wrapped in Cache::remember.
     * Security: Policy::viewResults guarded.
     */
    public function results(Request $request, EvaluationForm $form, \Modules\Educational\Application\Services\EvaluationSettings $settings)
    {
        $this->authorize('viewResults', $form);

        // Increase memory for reporting
        ini_set('memory_limit', '512M');

        $form->load(['questions', 'assignments.lecture.group', 'formType']);
        $isTraineeEvalAction = $form->formType && collect($form->formType->allowed_roles)->contains('trainee');

        $lectureId = $request->query('lecture_id');
        $isInvalidLecture = false;

        if ($lectureId) {
            $isValid = $form->assignments()->where('lecture_id', $lectureId)->exists();
            if (!$isValid) {
                $isInvalidLecture = true;
                $request->offsetUnset('lecture_id');
            }
        }

        // ── Cache & Snapshot Integrity ────────────────────────────────────────
        $filters = $request->only(['lecture_id', 'date_from', 'date_to']);
        $cacheKey = "eval_results_v7_{$form->id}_" . md5(json_encode($filters));
        $cacheSeconds = $settings->resultsCacheDuration();

        $data = Cache::remember($cacheKey, $cacheSeconds, function () use ($form, $request, $settings, $isTraineeEvalAction) {

            // ── Base Query for Evaluations (for filtering) ────────────────────────
            $baseEvalQuery = DB::table('education.lecture_evaluations')
                ->where('form_id', $form->id)
                ->when($request->filled('lecture_id'), fn($q) => $q->where('lecture_id', $request->lecture_id))
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('submitted_at', '>=', $request->date_from))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('submitted_at', '<=', $request->date_to));

            // ── Basic Counts ──────────────────────────────────────────────────────
            $totalCount = (clone $baseEvalQuery)->count();
            $traineeCount = (clone $baseEvalQuery)->where('evaluator_role', 'trainee')->count();
            $observerCount = (clone $baseEvalQuery)->where('evaluator_role', 'observer')->count();

            // ── Averages (using DB Aggregation) ───────────────────────────────────
            $avgQuery = DB::table('education.evaluation_answers')
                ->join('education.lecture_evaluations', 'education.lecture_evaluations.id', '=', 'education.evaluation_answers.lecture_evaluation_id')
                ->where('education.lecture_evaluations.form_id', $form->id)
                ->when($request->filled('lecture_id'), fn($q) => $q->where('lecture_evaluations.lecture_id', $request->lecture_id))
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('lecture_evaluations.submitted_at', '>=', $request->date_from))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('lecture_evaluations.submitted_at', '<=', $request->date_to))
                ->whereNotNull('answer_rating');

            $overallAvg = (clone $avgQuery)->avg('answer_rating');
            $overallAvg = $overallAvg ? round($overallAvg, 2) : null;

            $traineeAvg = (clone $avgQuery)->where('evaluator_role', 'trainee')->avg('answer_rating');
            $traineeAvg = $traineeAvg ? round($traineeAvg, 2) : null;

            $observerAvg = (clone $avgQuery)->where('evaluator_role', 'observer')->avg('answer_rating');
            $observerAvg = $observerAvg ? round($observerAvg, 2) : null;

            // ── Completion Rates ──────────────────────────────────────────────────
            $assignments = $form->assignments()->with('lecture.group')->where('is_active', true)->get();
            $eligibleTrainees = 0;
            $groupIds = [];
            foreach ($assignments as $asgn) {
                if ($asgn->lecture && $asgn->lecture->group_id) {
                    $groupIds[] = $asgn->lecture->group_id;
                    $eligibleTrainees += TraineeProfile::where('group_id', $asgn->lecture->group_id)->count();
                }
            }
            $groupIds = array_unique($groupIds);

            $traineeCompletionRate = $eligibleTrainees > 0
                ? min(100, round($traineeCount / $eligibleTrainees * 100, 1)) : null;
            $observerCompletionRate = $assignments->count() > 0
                ? min(100, round($observerCount / $assignments->count() * 100, 1)) : null;

            // ── Trend over Time (DB GroupBy) ─────────────────────────────────────
            $trendDataRaw = (clone $avgQuery)
                ->select(DB::raw('DATE(education.lecture_evaluations.submitted_at) as date'), DB::raw('AVG(answer_rating) as avg_rating'))
                ->groupBy(DB::raw('DATE(education.lecture_evaluations.submitted_at)'))
                ->orderBy('date')
                ->get();

            $trendLabels = $trendDataRaw->pluck('date')->toArray();
            $trendData = $trendDataRaw->pluck('avg_rating')->map(fn($v) => round($v, 2))->toArray();

            // ── Role Comparison per Question ─────────────────────────────────────
            $roleComparisonLabels = [];
            $roleComparisonTrainee = [];
            $roleComparisonObserver = [];

            $ratingQuestions = $form->questions->where('type', 'rating_1_to_5');
            foreach ($ratingQuestions as $q) {
                $roleComparisonLabels[] = \Str::limit($q->question_text, 30);

                $tAvg = (clone $avgQuery)->where('question_id', $q->id)->where('evaluator_role', 'trainee')->avg('answer_rating');
                $oAvg = (clone $avgQuery)->where('question_id', $q->id)->where('evaluator_role', 'observer')->avg('answer_rating');

                $roleComparisonTrainee[] = $tAvg ? round($tAvg, 2) : 0;
                $roleComparisonObserver[] = $oAvg ? round($oAvg, 2) : 0;
            }

            // ── Red Flag Detection (Optimization) ──────────────────────────────
            $redFlagQuestions = [];
            $threshold = $settings->redFlagThreshold();

            if ($settings->isRedFlagEnabled()) {
                foreach ($ratingQuestions as $q) {
                    $oAvg = (clone $avgQuery)->where('question_id', $q->id)->where('evaluator_role', 'observer')->avg('answer_rating');
                    if ($oAvg !== null && $oAvg < $threshold) {
                        $redFlagQuestions[] = ['question' => $q->question_text, 'observer_avg' => round($oAvg, 2)];
                    }
                }
            }

            // Fire RedFlagDetected event ONLY if viewing a single lecture and flags exist
            if ($request->filled('lecture_id') && !empty($redFlagQuestions)) {
                $assignment = $form->assignments()->where('lecture_id', $request->lecture_id)->first();
                if ($assignment && !$assignment->red_flag_alert_sent_at) {
                    event(new \Modules\Educational\Domain\Events\RedFlagDetected($assignment, $redFlagQuestions));
                }
            }

            // ── Pending Evaluations or Attendance ─────────────────────────────
            $pendingTrainees = collect();
            $lectureAttendance = collect();

            if ($isTraineeEvalAction) {
                $submittedTraineeIds = (clone $baseEvalQuery)->where('evaluator_role', 'trainee')->pluck('evaluator_id')->unique()->toArray();
                $pendingTrainees = TraineeProfile::with('user')
                    ->when(count($groupIds) > 0, fn($q) => $q->whereIn('group_id', $groupIds))
                    ->whereNotIn('id', $submittedTraineeIds)
                    ->take(24)
                    ->get();
            } elseif ($request->filled('lecture_id')) {
                $lectureAttendance = \Modules\Educational\Domain\Models\Attendance::with('traineeProfile.user')
                    ->where('lecture_id', $request->lecture_id)
                    ->get();
            }

            // ── Per-Question Stats (DB Optimized) ─────────────────────────────
            $questionStats = $form->questions->map(function ($question) use ($request) {
                $ansQuery = DB::table('education.evaluation_answers')
                    ->join('education.lecture_evaluations', 'education.lecture_evaluations.id', '=', 'education.evaluation_answers.lecture_evaluation_id')
                    ->where('evaluation_answers.question_id', $question->id)
                    ->when($request->filled('lecture_id'), fn($q) => $q->where('lecture_evaluations.lecture_id', $request->lecture_id))
                    ->when($request->filled('date_from'), fn($q) => $q->whereDate('lecture_evaluations.submitted_at', '>=', $request->date_from))
                    ->when($request->filled('date_to'), fn($q) => $q->whereDate('lecture_evaluations.submitted_at', '<=', $request->date_to));

                $respCount = (clone $ansQuery)->count();
                $stat = ['question' => $question, 'response_count' => $respCount];

                if ($question->type === 'rating_1_to_5') {
                    $rAvg = (clone $ansQuery)->whereNotNull('answer_rating')->avg('answer_rating');
                    $stat['rating_average'] = $rAvg ? round($rAvg, 2) : null;

                    $dist = [];
                    for ($r = 1; $r <= 5; $r++) {
                        $dist[$r] = (clone $ansQuery)->where('answer_rating', $r)->count();
                    }
                    $stat['rating_distribution'] = $dist;
                    $stat['dist_chart_data'] = array_values($dist);

                    $tAvg = (clone $ansQuery)->where('evaluator_role', 'trainee')->avg('answer_rating');
                    $oAvg = (clone $ansQuery)->where('evaluator_role', 'observer')->avg('answer_rating');
                    $stat['trainee_average'] = $tAvg ? round($tAvg, 2) : null;
                    $stat['observer_average'] = $oAvg ? round($oAvg, 2) : null;
                } elseif ($question->type === 'text') {
                    $stat['text_answers'] = (clone $ansQuery)->whereNotNull('answer_value')->limit(5)->pluck('answer_value')->toArray();
                } elseif ($question->type === 'boolean') {
                    $stat['yes_count'] = (clone $ansQuery)->where('answer_value', '1')->count();
                    $stat['no_count'] = (clone $ansQuery)->where('answer_value', '0')->count();
                } elseif ($question->type === 'multiple_choice') {
                    $dist = [];
                    $options = $question->getOptionsArray();
                    foreach ($options as $val => $label) {
                        $key = is_int($val) ? $label : $val;
                        $displayLabel = $label;
                        $dist[$displayLabel] = (clone $ansQuery)->where('answer_value', $key)->count();
                    }
                    $stat['choice_distribution'] = $dist;
                }

                return $stat;
            });

            // ── Snapshot Integrity check ─────────────────────────────────────
            $currentSnapshot = $form->buildSnapshot();
            $currentHash = LectureEvaluation::generateSnapshotHash($currentSnapshot);
            $hasInconsistentSnapshots = (clone $baseEvalQuery)->where('snapshot_hash', '!=', $currentHash)->exists();

            return compact(
                'questionStats',
                'totalCount',
                'traineeCount',
                'observerCount',
                'overallAvg',
                'traineeAvg',
                'observerAvg',
                'traineeCompletionRate',
                'observerCompletionRate',
                'trendLabels',
                'trendData',
                'roleComparisonLabels',
                'roleComparisonTrainee',
                'roleComparisonObserver',
                'redFlagQuestions',
                'pendingTrainees',
                'lectureAttendance',
                'hasInconsistentSnapshots'
            );
        });

        // ── Evaluations List (Paginated & Filtered outside Cache) ────────────
        $evaluations = $form->lectureEvaluations()
            ->with(['evaluator', 'lecture'])
            ->when($request->filled('lecture_id'), fn($q) => $q->where('lecture_id', $request->lecture_id))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('submitted_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('submitted_at', '<=', $request->date_to))
            ->when($request->filled('evaluator_role'), fn($q) => $q->where('evaluator_role', $request->evaluator_role))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sq) use ($search) {
                    $sq->whereHasMorph('evaluator', [\Modules\Educational\Domain\Models\TraineeProfile::class, \Modules\Users\Domain\Models\User::class], function ($msq, $type) use ($search) {
                        if ($type === \Modules\Educational\Domain\Models\TraineeProfile::class) {
                            $msq->whereHas('user', fn($usq) => $usq->where('first_name', 'ilike', "%{$search}%")->orWhere('last_name', 'ilike', "%{$search}%"));
                        } else {
                            $msq->where('first_name', 'ilike', "%{$search}%")->orWhere('last_name', 'ilike', "%{$search}%");
                        }
                    });
                });
            })
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        // Eager load 'user' for TraineeProfile evaluators only (polymorphic safe)
        $evaluations->loadMorph('evaluator', [
            \Modules\Educational\Domain\Models\TraineeProfile::class => ['user'],
        ]);

        // ── Lecture filter options ────────────────────────────────────────────
        $lectureOptions = $form->assignments()->with('lecture')->where('is_active', true)->get()
            ->pluck('lecture')->filter()->mapWithKeys(fn($l) => [$l->id => $l->starts_at->format('d/m/Y H:i')]);

        if ($request->ajax()) {
            return view('modules.educational.evaluations.partials.submissions_table', [
                'evaluations' => $evaluations,
                'totalCount' => $data['totalCount'] ?? 0
            ]);
        }

        return view('modules.educational.evaluations.results', array_merge($data, [
            'form' => $form,
            'evaluations' => $evaluations,
            'lectureOptions' => $lectureOptions,
            'isInvalidLecture' => $isInvalidLecture
        ]));
    }

    /**
     * Export to Excel — 3-sheet workbook (Summary / Detailed / Comparison).
     * Policy::viewResults guarded — NOT just a hidden button.
     */
    public function export(EvaluationForm $form)
    {
        $this->authorize('viewResults', $form);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \Modules\Educational\Exports\EvaluationResultsExport($form),
            'evaluation-results-' . $form->id . '-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    // ─── Question Management ──────────────────────────────────────────────────

    public function storeQuestion(Request $request, EvaluationForm $form)
    {
        $this->authorize('manageQuestions', $form);

        $validated = $request->validate([
            'question_text' => 'required|string|max:500',
            'type' => ['required', Rule::in(array_keys(EvaluationQuestion::TYPES))],
            'options' => 'nullable|array|min:2',
            'options.*' => 'string|max:200',
            'is_required' => 'boolean',
        ]);

        if ($validated['type'] !== 'multiple_choice') {
            $validated['options'] = null;
        }

        $maxOrder = $form->questions()->max('order_index') ?? 0;
        $question = $form->questions()->create(array_merge($validated, [
            'order_index' => $maxOrder + 1,
            'is_required' => $validated['is_required'] ?? true,
        ]));

        if ($request->wantsJson()) {
            return response()->json(['question' => $question], 201);
        }

        return redirect()
            ->route('educational.evaluations.forms.edit', $form)
            ->with('success', 'تم إضافة السؤال.');
    }

    public function updateQuestion(Request $request, EvaluationQuestion $question)
    {
        $this->authorize('manageQuestions', $question->form);

        $validated = $request->validate([
            'question_text' => 'required|string|max:500',
            'type' => ['required', Rule::in(array_keys(EvaluationQuestion::TYPES))],
            'options' => 'nullable|array|min:2',
            'options.*' => 'string|max:200',
            'is_required' => 'boolean',
        ]);

        if ($validated['type'] !== 'multiple_choice') {
            $validated['options'] = null;
        }

        $question->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['question' => $question]);
        }

        return redirect()
            ->route('educational.evaluations.forms.edit', $question->form_id)
            ->with('success', 'تم تحديث السؤال.');
    }

    public function destroyQuestion(Request $request, EvaluationQuestion $question)
    {
        $this->authorize('manageQuestions', $question->form);
        $question->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'deleted']);
        }

        return redirect()
            ->route('educational.evaluations.forms.edit', $question->form_id)
            ->with('success', 'تم حذف السؤال.');
    }

    public function reorderQuestions(Request $request, EvaluationForm $form)
    {
        $this->authorize('manageQuestions', $form);

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|numeric',
        ]);

        $questionIds = $validated['order'];

        DB::transaction(function () use ($questionIds, $form) {
            foreach ($questionIds as $index => $id) {
                // Bulk-ish update but scoped to form for security
                $form->questions()->where('id', $id)->update(['order_index' => $index + 1]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Questions reordered successfully',
            'order' => $questionIds
        ]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function syncQuestions(EvaluationForm $form, array $questions): void
    {
        foreach ($questions as $index => $q) {
            if (empty($q['question_text']))
                continue;

            $form->questions()->create([
                'question_text' => $q['question_text'],
                'type' => $q['type'] ?? 'rating_1_to_5',
                'options' => ($q['type'] ?? '') === 'multiple_choice' ? ($q['options'] ?? null) : null,
                'is_required' => $q['is_required'] ?? true,
                'order_index' => $index + 1,
            ]);
        }
    }
}
