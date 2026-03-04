<?php

namespace Modules\Educational\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Modules\Educational\Domain\Models\EvaluationForm;

/**
 * EvaluationResultsExport
 *
 * 3-sheet Excel workbook:
 *  Sheet 1 — Summary       : form metadata + overall stats + per-question averages
 *  Sheet 2 — Detailed      : every answer per evaluator per question
 *  Sheet 3 — Comparison    : trainee vs observer average per question
 *
 * submitted_at is used — created_at is NEVER referenced here.
 */
class EvaluationResultsExport implements WithMultipleSheets
{
    public function __construct(private EvaluationForm $form)
    {
    }

    public function sheets(): array
    {
        $evaluations = $this->form->lectureEvaluations()
            ->with(['answers.question', 'lecture'])
            ->get();

        return [
            new Sheets\SummarySheet($this->form, $evaluations),
            new Sheets\DetailedAnswersSheet($this->form, $evaluations),
            new Sheets\RoleComparisonSheet($this->form, $evaluations),
        ];
    }
}
