<?php

namespace Modules\Educational\Application\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Modules\Educational\Domain\Models\LectureEvaluation;
use Modules\Educational\Domain\Models\EvaluationAnswer;
use Modules\Users\Domain\Models\User;
use Modules\Educational\Application\Notifications\WeeklyReportNotification;

class WeeklyEvaluationReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(\Modules\Educational\Application\Services\EvaluationSettings $settings): void
    {
        $lastWeek = now()->subDays(7);
        $threshold = $settings->redFlagThreshold();

        // 1. New Evaluations this week
        $evals = LectureEvaluation::where('submitted_at', '>=', $lastWeek)->get();
        if ($evals->isEmpty())
            return;

        // 2. Average rating this week
        $avg = EvaluationAnswer::whereIn('lecture_evaluation_id', $evals->pluck('id'))
            ->whereNotNull('answer_rating')
            ->avg('answer_rating');

        // 3. Red flags this week
        $redFlagsCount = 0;
        if ($settings->isRedFlagEnabled()) {
            $redFlagsCount = $evals->where('evaluator_role', 'observer')->filter(function ($e) use ($threshold) {
                return $e->answers->whereNotNull('answer_rating')->avg('answer_rating') < $threshold;
            })->count();
        }

        $stats = [
            'new_evaluations_count' => $evals->count(),
            'weekly_avg' => round($avg, 2),
            'lectures_evaluated_count' => $evals->pluck('lecture_id')->unique()->count(),
            'red_flags_count' => $redFlagsCount,
        ];

        // 4. Send to Managers
        $managers = User::whereHas('permissions', function ($q) {
            $q->where('name', 'manage educational');
        })->get();

        if ($managers->isNotEmpty()) {
            Notification::send($managers, new WeeklyReportNotification($stats));
        }
    }
}
