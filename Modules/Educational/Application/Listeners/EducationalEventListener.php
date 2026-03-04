<?php

namespace Modules\Educational\Application\Listeners;

use Modules\Educational\Domain\Events\LectureCreated;
use Modules\Educational\Domain\Events\LectureStatusChanged;
use Modules\Educational\Domain\Events\AttendanceOverridden;
use Modules\Educational\Domain\Events\EvaluationSubmitted;
use Modules\Educational\Domain\Events\SchedulingConflictOccurred;
use Modules\Educational\Application\Services\EducationalIntegrationService;

class EducationalEventListener
{
    protected $integrationService;

    /**
     * Create the event listener.
     */
    public function __construct(EducationalIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Handle the LectureCreated event.
     */
    public function handleLectureCreated(LectureCreated $event): void
    {
        $this->integrationService->logEvent('lecture_created', "Lecture #{$event->lecture->id} created", $event->lecture);
    }

    /**
     * Handle the LectureStatusChanged event.
     */
    public function handleLectureStatusChanged(LectureStatusChanged $event): void
    {
        $this->integrationService->logEvent(
            'lecture_status_changed',
            "Lecture #{$event->lecture->id} changed from {$event->oldStatus} to {$event->newStatus}",
            $event->lecture
        );

        if ($event->newStatus === 'cancelled') {
            $hoursUntilStart = now()->diffInHours($event->lecture->starts_at, false);
            if ($hoursUntilStart > 0 && $hoursUntilStart <= 24) {
                $programLabel = $event->lecture->program->name ?? $event->lecture->program_id;

                $this->integrationService->raiseSupportTicket(
                    "Last-Minute Lecture Cancellation",
                    "Critical: Lecture #{$event->lecture->id} (Program: {$programLabel}) was cancelled only {$hoursUntilStart} hours before starting.",
                    'urgent'
                );

                $this->integrationService->notifyStaff(
                    "Lecture Cancelled",
                    "Assigned lecture #{$event->lecture->id} was cancelled less than 24h before start.",
                    'danger'
                );
            }
        }
    }

    /**
     * Handle the SchedulingConflictOccurred event.
     */
    public function handleSchedulingConflictOccurred(SchedulingConflictOccurred $event): void
    {
        $this->integrationService->logEvent('scheduling_conflict', "Engine detected conflict: {$event->reason}");

        $this->integrationService->raiseSupportTicket(
            "Scheduling Engine Conflict Detected",
            "Generation engine halted due to resource collision: {$event->reason}.",
            'high'
        );
    }

    /**
     * Handle the AttendanceOverridden event.
     */
    public function handleAttendanceOverridden(AttendanceOverridden $event): void
    {
        $this->integrationService->raiseSupportTicket(
            "Attendance Locked Override Detected",
            "Attendance for Trainee #{$event->attendance->trainee_profile_id} in Lecture #{$event->attendance->lecture_id} was modified after lock.",
            'medium'
        );
    }

    /**
     * Handle the EvaluationSubmitted event.
     */
    public function handleEvaluationSubmitted(EvaluationSubmitted $event): void
    {
        $needsReview = false;
        foreach ($event->evaluation->answers as $answer) {
            if ($answer->answer_rating && $answer->answer_rating <= 2) {
                $needsReview = true;
                break;
            }
        }

        if ($needsReview) {
            $this->integrationService->raiseSupportTicket(
                "Low Lecture Evaluation Report",
                "Lecture #{$event->evaluation->lecture_id} received a very low rating.",
                'medium'
            );
        }
    }

    /**
     * Handle generic ApprovalApproved for Educational specific actions
     */
    public function handleApprovalApproved(\Modules\Core\Domain\Events\ApprovalApproved $event): void
    {
        $request = $event->approvalRequest;

        // Action: Program Deletion
        if ($request->action === 'delete' && $request->approvable_type === \Modules\Educational\Domain\Models\Program::class) {
            $program = \Modules\Educational\Domain\Models\Program::find($request->approvable_id);
            if ($program) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($program) {
                    $program->campuses()->detach();
                    $program->delete();
                });
            }
        }

        // Action: Attendance Override
        if ($request->action === 'override_attendance' && $request->approvable_type === \Modules\Educational\Domain\Models\Attendance::class) {
            $attendance = \Modules\Educational\Domain\Models\Attendance::find($request->approvable_id);
            if ($attendance && isset($request->metadata['requested_changes'])) {
                $changes = $request->metadata['requested_changes'];
                $attendance->update([
                    'status' => $changes['status'],
                    'notes' => ($attendance->notes ? $attendance->notes . "\n" : "") . "Override approved: " . ($changes['notes'] ?? ""),
                    'check_in_time' => $changes['check_in_time'] ?? $attendance->check_in_time,
                    'locked_at' => now(), // Keep it locked, but updated
                ]);
            }
        }

        // Action: Group Deletion
        if ($request->action === 'delete' && $request->approvable_type === \Modules\Educational\Domain\Models\Group::class) {
            $group = \Modules\Educational\Domain\Models\Group::find($request->approvable_id);
            if ($group) {
                // If there are soft deletes or relations needed to be detached, do it here. For now, simple delete.
                $group->delete();
            }
        }

        // Action: Schedule Template Deletion
        if ($request->action === 'delete' && $request->approvable_type === \Modules\Educational\Domain\Models\ScheduleTemplate::class) {
            $template = \Modules\Educational\Domain\Models\ScheduleTemplate::find($request->approvable_id);
            if ($template) {
                $template->delete();
            }
        }

        // Action: Campus Deletion
        if ($request->action === 'delete' && $request->approvable_type === \Modules\Educational\Domain\Models\Campus::class) {
            $campus = \Modules\Educational\Domain\Models\Campus::find($request->approvable_id);
            if ($campus) {
                $campus->delete();
            }
        }

        // Action: Building Deletion
        if ($request->action === 'delete' && $request->approvable_type === \Modules\Educational\Domain\Models\Building::class) {
            $building = \Modules\Educational\Domain\Models\Building::find($request->approvable_id);
            if ($building) {
                $building->delete();
            }
        }
    }
}
