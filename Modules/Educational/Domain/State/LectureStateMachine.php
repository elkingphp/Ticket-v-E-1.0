<?php

namespace Modules\Educational\Domain\State;

use Modules\Educational\Domain\Models\Lecture;
use Modules\Educational\Domain\Events\LectureStatusChanged;
use Modules\Educational\Application\Exceptions\IllegalStateTransitionException;

/**
 * Class LectureStateMachine
 * 
 * Implements the State Pattern to manage Lecture transitions and guards.
 * Ensures Data Governance and consistency across the lifecycle.
 */
class LectureStateMachine
{
    protected Lecture $lecture;

    public function __construct(Lecture $lecture)
    {
        $this->lecture = $lecture;
    }

    /**
     * Business logic for transitioning to RUNNING.
     */
    public function transitionToRunning(): void
    {
        $this->guardCanStart();
        $this->executeTransition('running');
    }

    /**
     * Business logic for transitioning to COMPLETED.
     */
    public function transitionToCompleted(): void
    {
        $this->guardCanComplete();
        $this->executeTransition('completed');
    }

    /**
     * Business logic for transitioning to CANCELLED.
     */
    public function transitionToCancelled(): void
    {
        $this->executeTransition('cancelled');
    }

    /**
     * Guard: Ensure lecture is ready to start.
     */
    protected function guardCanStart(): void
    {
        if ($this->lecture->status !== 'scheduled') {
            throw new IllegalStateTransitionException("Only scheduled lectures can be started.");
        }

        if (is_null($this->lecture->instructor_profile_id)) {
            throw new IllegalStateTransitionException("Lecture cannot start without an assigned instructor.");
        }
    }

    /**
     * Guard: Ensure lecture is ready to be completed.
     */
    protected function guardCanComplete(): void
    {
        if (!in_array($this->lecture->status, ['scheduled', 'running'])) {
            throw new IllegalStateTransitionException("Only running or scheduled lectures can be completed.");
        }

        // Drill-down logic: If it was running, it MUST have attendance locked
        if ($this->lecture->status === 'running') {
            $locked = $this->lecture->attendances()->whereNotNull('locked_at')->exists();
            if (!$locked) {
                throw new IllegalStateTransitionException("Lecture cannot be completed: Attendance records must be locked/finalized first.");
            }
        }
    }

    /**
     * Execute the status change and dispatch events.
     */
    protected function executeTransition(string $newStatus): void
    {
        $oldStatus = $this->lecture->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        $this->lecture->update(['status' => $newStatus]);

        event(new LectureStatusChanged($this->lecture, $oldStatus, $newStatus));
    }
}
