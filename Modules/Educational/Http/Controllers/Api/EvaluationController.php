<?php

namespace Modules\Educational\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\LectureEvaluation;
use Modules\Educational\Domain\Models\EvaluationAnswer;
use Modules\Educational\Domain\Events\EvaluationSubmitted;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    /**
     * Submit an evaluation for a lecture.
     */
    public function submit(Request $request): JsonResponse
    {
        $request->validate([
            'lecture_id' => 'required|exists:' . \Modules\Educational\Domain\Models\Lecture::class . ',id',
            'form_id' => 'required|exists:' . \Modules\Educational\Domain\Models\EvaluationForm::class . ',id',
            'evaluator_type' => 'required|string',
            'evaluator_id' => 'required|integer',
            'overall_comments' => 'nullable|string',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:' . \Modules\Educational\Domain\Models\EvaluationQuestion::class . ',id',
            'answers.*.rating' => 'nullable|integer|min:1|max:5',
            'answers.*.value' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $evaluation = LectureEvaluation::create([
                'lecture_id' => $request->lecture_id,
                'form_id' => $request->form_id,
                'evaluator_type' => $request->evaluator_type,
                'evaluator_id' => $request->evaluator_id,
                'overall_comments' => $request->overall_comments,
            ]);

            foreach ($request->answers as $ans) {
                EvaluationAnswer::create([
                    'lecture_evaluation_id' => $evaluation->id,
                    'question_id' => $ans['question_id'],
                    'answer_rating' => $ans['rating'] ?? null,
                    'answer_value' => $ans['value'] ?? null,
                ]);
            }

            // Fresh load for accurate event dispatch context
            $evaluation->load('answers');

            // Dispatch domain event (which may create Support QA Tickets)
            event(new EvaluationSubmitted($evaluation));

            return response()->json(['message' => 'Evaluation submitted successfully.', 'data' => $evaluation], 201);
        });
    }
}
