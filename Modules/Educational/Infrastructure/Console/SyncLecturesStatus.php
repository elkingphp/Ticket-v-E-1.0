<?php

namespace Modules\Educational\Infrastructure\Console;

use Illuminate\Console\Command;
use Modules\Educational\Domain\Models\Lecture;
use Modules\Educational\Domain\State\LectureStateMachine;
use Modules\Educational\Application\Exceptions\IllegalStateTransitionException;
use Carbon\Carbon;

class SyncLecturesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'educational:sync-lectures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically transitions lecture statuses based on current time (Scheduled -> Running -> Completed)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $this->info("Syncing lecture statuses at: " . $now->toDateTimeString());

        // 1. Scheduled -> Running (If current time is between start and end)
        $toRunning = Lecture::where('status', 'scheduled')
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>', $now)
            ->get();

        /** @var Lecture $lecture */
        foreach ($toRunning as $lecture) {
            try {
                (new LectureStateMachine($lecture))->transitionToRunning();
                $this->comment("Lecture #{$lecture->id} is now RUNNING.");
            } catch (IllegalStateTransitionException $e) {
                $this->warn("Skipping Lecture #{$lecture->id}: " . $e->getMessage());
            }
        }

        // 2. Running/Scheduled -> Completed (If current time is past end time)
        $toCompleted = Lecture::whereIn('status', ['scheduled', 'running'])
            ->where('ends_at', '<=', $now)
            ->get();

        /** @var Lecture $lecture */
        foreach ($toCompleted as $lecture) {
            try {
                (new LectureStateMachine($lecture))->transitionToCompleted();
                $this->comment("Lecture #{$lecture->id} is now COMPLETED.");
            } catch (IllegalStateTransitionException $e) {
                $this->warn("Skipping Lecture #{$lecture->id}: " . $e->getMessage());
            }
        }

        $this->info("Sync completed. Processed " . ($toRunning->count() + $toCompleted->count()) . " transitions.");

        // Refresh analytics view CONCURRENTLY (Requires unique index on MV)
        \Illuminate\Support\Facades\DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY education.mv_lecture_stats');
        $this->info("Analytics Materialized View refreshed CONCURRENTLY.");
    }
}
