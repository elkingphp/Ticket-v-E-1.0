<?php

namespace Modules\Educational\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Educational\Domain\Models\EvaluationForm;

/**
 * RoleComparisonSheet
 *
 * Trainee average vs Observer average per question.
 * Only includes rating_1_to_5 questions (meaningful to compare).
 * Red flag column marks observer_avg < 3.
 */
class RoleComparisonSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(
        private EvaluationForm $form,
        private Collection $evaluations
    ) {
    }

    public function title(): string
    {
        return 'مقارنة المتدرب vs المراقب';
    }

    public function array(): array
    {
        $traineeEvals = $this->evaluations->where('evaluator_role', 'trainee');
        $observerEvals = $this->evaluations->where('evaluator_role', 'observer');

        $rows = [
            [
                'السؤال',
                'متوسط المتدربين',
                'متوسط المراقبين',
                'الفرق',
                '🚨 تنبيه',
            ]
        ];

        foreach ($this->form->questions->where('type', 'rating_1_to_5') as $q) {
            $tR = $traineeEvals->flatMap->answers->where('question_id', $q->id)->whereNotNull('answer_rating');
            $oR = $observerEvals->flatMap->answers->where('question_id', $q->id)->whereNotNull('answer_rating');

            $tAvg = $tR->count() ? round($tR->avg('answer_rating'), 2) : null;
            $oAvg = $oR->count() ? round($oR->avg('answer_rating'), 2) : null;
            $diff = ($tAvg !== null && $oAvg !== null) ? round($tAvg - $oAvg, 2) : '—';

            $flag = ($oAvg !== null && $oAvg < 3.0) ? '⚠️ يستدعي المراجعة' : '';

            $rows[] = [
                $q->question_text,
                $tAvg ?? '—',
                $oAvg ?? '—',
                $diff,
                $flag,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEF3C7']]],
        ];
    }
}
