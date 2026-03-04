<?php

namespace Modules\Educational\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Educational\Domain\Models\EvaluationForm;

/**
 * DetailedAnswersSheet
 *
 * Every answer per evaluator. One row per evaluator per question.
 * Columns: lecture_date | evaluator_role | question | answer | submitted_at
 * submitted_at is authoritative — created_at is NEVER exposed.
 */
class DetailedAnswersSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(
        private EvaluationForm $form,
        private Collection $evaluations
    ) {
    }

    public function title(): string
    {
        return 'الإجابات التفصيلية';
    }

    public function array(): array
    {
        $rows = [
            [
                'تاريخ المحاضرة',
                'دور المقيِّم',
                'السؤال',
                'نوع السؤال',
                'الإجابة',
                'تاريخ الإرسال',
            ]
        ];

        foreach ($this->evaluations as $ev) {
            foreach ($ev->answers as $answer) {
                $q = $answer->question;
                if (!$q)
                    continue;

                $value = $answer->answer_rating !== null
                    ? $answer->answer_rating . ' / 5'
                    : $answer->answer_value;

                $rows[] = [
                    optional($ev->lecture?->starts_at)->format('Y-m-d H:i') ?? '—',
                    $ev->evaluator_role === 'trainee' ? 'متدرب' : 'مراقب',
                    $q->question_text,
                    \Modules\Educational\Domain\Models\EvaluationQuestion::TYPES[$q->type] ?? $q->type,
                    $value,
                    optional($ev->submitted_at)->format('Y-m-d H:i') ?? '—',
                ];
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D1FAE5']]],
        ];
    }
}
