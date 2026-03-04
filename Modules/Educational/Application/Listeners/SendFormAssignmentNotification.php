<?php

namespace Modules\Educational\Application\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Modules\Educational\Domain\Events\FormAssignedToLecture;
use Modules\Educational\Application\Notifications\LectureEvaluationAssigned;
use Modules\Educational\Domain\Models\TraineeProfile;
use Modules\Users\Domain\Models\User;

class SendFormAssignmentNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(FormAssignedToLecture $event): void
    {
        $assignment = $event->assignment;
        $lecture = $assignment->lecture;

        if (!$lecture)
            return;

        $allowedRoles = $assignment->allow_evaluator_types ?? [];

        // 1. Notify Trainees (if allowed)
        if (in_array('trainee', $allowedRoles)) {
            $traineeUsers = User::whereHas('traineeProfile', function ($q) use ($lecture) {
                $q->where('group_id', $lecture->group_id);
            })->get();

            if ($traineeUsers->isNotEmpty()) {
                Notification::send($traineeUsers, new LectureEvaluationAssigned($assignment, 'trainee'));
            }
        }

        // 2. Notify Observers (if allowed)
        // For Enterprise logic, we might notify the specific admin/observer who is assigned to watch this course.
        // As a baseline, we can notify users with relevant permissions or the one who created the assignment if they are an observer.
        if (in_array('observer', $allowedRoles)) {
            // Option A: Identify specific observers for this lecture/group if they exist.
            // Option B: For now, we'll notify the 'assigned_by' user if they have the right permissions to be an observer.
            $observer = User::find($assignment->assigned_by);
            if ($observer) {
                $observer->notify(new LectureEvaluationAssigned($assignment, 'observer'));
            }
        }
    }
}
