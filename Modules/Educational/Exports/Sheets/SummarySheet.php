<?php

namespace Modules\Educational\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Educational\Domain\Models\EvaluationForm;

class SummarySheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(
        private EvaluationForm $form,
        private Collection $evaluations
    ) {
    }

    public function title(): string
    {
        return 'الملخص';
    }

    public function array(): array
    {
        $traineeEvals = $this->evaluations->where('evaluator_role', 'trainee');
        $observerEvals = $this->evaluations->where('evaluator_role', 'observer');
        $allRatings = $this->evaluations->flatMap->answers->whereNotNull('answer_rating');
        $totalCount = $this->evaluations->count();

        $rows = [
            ['نموذج التقييم', $this->form->title],
            ['النوع', \Modules\Educational\Domain\Models\EvaluationForm::TYPES[$this->form->type] ?? $this->form->type],
            ['الحالة', $this->form->status],
            ['تاريخ النشر', $this->form->published_at?->format('Y-m-d') ?? '—'],
            [''],
            ['─── إحصائيات عامة ───'],
            ['إجمالي التقييمات', $totalCount],
            ['تقييمات المتدربين', $traineeEvals->count()],
            ['تقييمات المراقبين', $observerEvals->count()],
            ['متوسط التقييم العام', $allRatings->count() ? round($allRatings->avg('answer_rating'), 2) : '—'],
            [
                'متوسط المتدربين',
                $traineeEvals->flatMap->answers->whereNotNull('answer_rating')->count()
                ? round($traineeEvals->flatMap->answers->whereNotNull('answer_rating')->avg('answer_rating'), 2) : '—'
            ],
            [
                'متوسط المراقبين',
                $observerEvals->flatMap->answers->whereNotNull('answer_rating')->count()
                ? round($observerEvals->flatMap->answers->whereNotNull('answer_rating')->avg('answer_rating'), 2) : '—'
            ],
            [''],
            ['─── نتيجة كل سؤال ───'],
            ['السؤال', 'النوع', 'المتوسط العام', 'متوسط المتدربين', 'متوسط المراقبين', 'عدد الإجابات'],
        ];

        foreach ($this->form->questions as $q) {
            $answers = $this->evaluations->flatMap->answers->where('question_id', $q->id);

            if ($q->type === 'rating_1_to_5') {
                $rA = $answers->whereNotNull('answer_rating');
                $tRA = $traineeEvals->flatMap->answers->where('question_id', $q->id)->whereNotNull('answer_rating');
                $oRA = $observerEvals->flatMap->answers->where('question_id', $q->id)->whereNotNull('answer_rating');

                $rows[] = [
                    $q->question_text,
                    'تقييم 1-5',
                    $rA->count() ? round($rA->avg('answer_rating'), 2) : '—',
                    $tRA->count() ? round($tRA->avg('answer_rating'), 2) : '—',
                    $oRA->count() ? round($oRA->avg('answer_rating'), 2) : '—',
                    $answers->count(),
                ];
            } else {
                $rows[] = [
                    $q->question_text,
                    \Modules\Educational\Domain\Models\EvaluationQuestion::TYPES[$q->type] ?? $q->type,
                    '—',
                    '—',
                    '—',
                    $answers->count(),
                ];
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            6 => ['font' => ['bold' => true]],
            14 => ['font' => ['bold' => true]],
            15 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DBEAFE']]],
        ];
    }
}
