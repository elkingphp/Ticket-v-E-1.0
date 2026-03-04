<?php

namespace Modules\Educational\Application\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Modules\Educational\Application\Exceptions\SchedulingConflictException;
use Modules\Educational\Domain\Events\LectureCreated;
use Modules\Educational\Domain\Events\SchedulingConflictOccurred;
use Modules\Educational\Domain\Models\Lecture;
use Modules\Educational\Domain\Models\ScheduleTemplate;
use Modules\Educational\Domain\Models\LectureFormAssignment;

class SchedulingService
{
    /**
     * Idempotent generator that transforms a Schedule Template into a series of Lectures.
     * 
     * @param ScheduleTemplate $template
     * @param string $generateFrom Date string (Y-m-d)
     * @param string $generateTo Date string (Y-m-d)
     * @return array List of generated lecture IDs
     * @throws SchedulingConflictException
     */
    public function generateFromTemplate(ScheduleTemplate $template, string $generateFrom, string $generateTo): array
    {
        $startDate = Carbon::parse($generateFrom);
        $endDate = Carbon::parse($generateTo);

        // Ensure the generation bounds respect the template's effective dates
        if ($template->effective_from && $startDate->lt($template->effective_from)) {
            $startDate = Carbon::parse($template->effective_from);
        }
        if ($template->effective_until && $endDate->gt($template->effective_until)) {
            $endDate = Carbon::parse($template->effective_until);
        }

        // Generate the period of days to consider
        $period = CarbonPeriod::create($startDate, $endDate);

        $lecturesToCreate = [];

        foreach ($period as $date) {
            // Check if this date matches the template's day of week
            if ($date->dayOfWeek == $template->day_of_week) {

                // Handle recurrence patterns (Bi-weekly)
                if ($template->recurrence_type && $template->recurrence_type !== 'weekly') {
                    $anchorDate = Carbon::parse($template->effective_from)->startOfWeek();
                    $currentWeek = $date->copy()->startOfWeek();
                    $weeksPassed = $anchorDate->diffInWeeks($currentWeek);

                    if ($template->recurrence_type === 'biweekly_odd') {
                        // Odd weeks: runs when diff is 0, 2, 4...
                        if ($weeksPassed % 2 !== 0)
                            continue;
                    } elseif ($template->recurrence_type === 'biweekly_even') {
                        // Even weeks: runs when diff is 1, 3, 5...
                        if ($weeksPassed % 2 === 0)
                            continue;
                    }
                }

                // Construct the exact timestamps for the prospective lecture
                $startDateTime = $date->copy()->setTimeFromTimeString($template->start_time);
                $endDateTime = $date->copy()->setTimeFromTimeString($template->end_time);

                // 1. Idempotency Check (Skip if this EXACT lecture already exists for this group)
                $exists = Lecture::where('program_id', $template->program_id)
                    ->where('group_id', $template->group_id)
                    ->where('starts_at', $startDateTime)
                    ->where('status', '!=', 'cancelled')
                    ->exists();

                if ($exists) {
                    continue; // Skip silently (Idempotent behavior)
                }

                // 2. Pre-emptive Application-Level Conflict Check for UX (Optional, but highly recommended)
                // Even though Postgres will block it, checking here allows us to throw a friendly exception
                $this->detectConflicts($template, $startDateTime, $endDateTime);

                $lecturesToCreate[] = [
                    'session_type_id' => $template->session_type_id,
                    'subject' => $template->subject,
                    'recurrence_type' => $template->recurrence_type,
                    'program_id' => $template->program_id,
                    'group_id' => $template->group_id,
                    'instructor_profile_id' => $template->instructor_profile_id,
                    'room_id' => $template->room_id,
                    'starts_at' => $startDateTime,
                    'ends_at' => $endDateTime,
                    'status' => 'scheduled',
                    'version' => 1,
                    // Carbon timestamps for created/updated
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (empty($lecturesToCreate)) {
            return []; // Nothing to generate
        }

        return DB::transaction(function () use ($lecturesToCreate, $template) {
            $createdIds = [];

            foreach ($lecturesToCreate as $data) {
                // We insert them individually here mostly to ensure Events can be properly dispatched
                // If performance is strictly required over events, we could use `insert()` or `upsert()`
                try {
                    $lecture = Lecture::create($data);
                    $createdIds[] = $lecture->id;

                    // Dispatch the event asynchronously for notifications/audit
                    event(new LectureCreated($lecture));

                    // Phase C: Automatic Evaluation Form Assignment
                    if ($template->evaluation_form_id) {
                        LectureFormAssignment::create([
                            'lecture_id' => $lecture->id,
                            'form_id' => $template->evaluation_form_id,
                            'allow_evaluator_types' => $template->allow_evaluator_types ?? ['trainee'],
                            'assigned_by' => auth()->id(),
                            'assigned_at' => now(),
                            'is_active' => true,
                        ]);
                    }

                } catch (\Illuminate\Database\QueryException $e) {
                    // Safety net: If race condition occurred strictly between our check and insert, 
                    // Postgres Constraints will fire a QueryException. We capture it and translate it.
                    if (str_contains($e->getMessage(), 'EXCLUDE USING gist')) {
                        throw new SchedulingConflictException("A concurrent scheduling conflict occurred in the database layer. Overlapping prevented natively.");
                    }
                    throw $e;
                }
            }

            return $createdIds;
        });
    }

    /**
     * Conducts a preliminary strict application-level check for overlapping (Double Booking).
     * 
     * @param ScheduleTemplate $template
     * @param Carbon $start
     * @param Carbon $end
     * @throws SchedulingConflictException
     */
    protected function detectConflicts(ScheduleTemplate $template, Carbon $start, Carbon $end): void
    {
        // 1. Room Double Booking
        $roomConflict = Lecture::where('room_id', $template->room_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($start, $end) {
                // The classic strict overlap formula: existing_start < new_end AND existing_end > new_start
                $query->where('starts_at', '<', $end)
                    ->where('ends_at', '>', $start);
            })->exists();

        if ($roomConflict) {
            $reason = "Room #{$template->room_id} has a conflicting schedule between {$start->format('Y-m-d H:i')} and {$end->format('H:i')}";
            event(new SchedulingConflictOccurred($template, $start, $end, $reason));
            throw new SchedulingConflictException($reason);
        }

        // 2. Instructor Double Booking
        if ($template->instructor_profile_id) {
            $instructorConflict = Lecture::where('instructor_profile_id', $template->instructor_profile_id)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($start, $end) {
                    $query->where('starts_at', '<', $end)
                        ->where('ends_at', '>', $start);
                })->exists();

            if ($instructorConflict) {
                $reason = "Instructor #{$template->instructor_profile_id} has a conflicting schedule between {$start->format('Y-m-d H:i')} and {$end->format('H:i')}";
                event(new SchedulingConflictOccurred($template, $start, $end, $reason));
                throw new SchedulingConflictException($reason);
            }
        }

        // 3. Group Double Booking
        $groupConflict = Lecture::where('group_id', $template->group_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($start, $end) {
                $query->where('starts_at', '<', $end)
                    ->where('ends_at', '>', $start);
            })->exists();

        if ($groupConflict) {
            $reason = "Group #{$template->group_id} has a conflicting schedule between {$start->format('Y-m-d H:i')} and {$end->format('H:i')}";
            event(new SchedulingConflictOccurred($template, $start, $end, $reason));
            throw new SchedulingConflictException($reason);
        }
    }
}
