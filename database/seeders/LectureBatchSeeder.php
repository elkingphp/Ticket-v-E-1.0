<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Educational\Domain\Models\Lecture;
use Modules\Educational\Domain\Models\Room;
use Modules\Educational\Domain\Models\Program;
use Modules\Educational\Domain\Models\Group;
use Modules\Educational\Domain\Models\InstructorProfile;
use Modules\Educational\Domain\Models\SessionType;
use Carbon\Carbon;

class LectureBatchSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing lectures to avoid exclusion constraint conflicts during multiple runs
        \Illuminate\Support\Facades\DB::table('education.lectures')->delete();

        $rooms = Room::all();
        $programs = Program::all();
        $groups = Group::all();
        $instructors = InstructorProfile::all();
        $sessionTypes = SessionType::all();

        // Ensure we have at least 10 instructors to avoid double booking constraints
        if ($instructors->count() < 10) {
            for ($i = 0; $i < 10; $i++) {
                $user = \Modules\Users\Domain\Models\User::create([
                    'first_name' => 'Instructor',
                    'last_name' => (10 + $i),
                    'username' => 'instructor_' . (10 + $i) . '_' . time(),
                    'email' => 'instructor_batch_' . (10 + $i) . '_' . time() . '@example.com',
                    'password' => bcrypt('password'),
                ]);
                InstructorProfile::create([
                    'user_id' => $user->id,
                    'status' => 'active',
                ]);
            }
            $instructors = InstructorProfile::all();
        }

        // Ensure we have at least 10 groups
        if ($groups->count() < 10) {
            for ($i = 0; $i < 10; $i++) {
                Group::create([
                    'name' => 'Group Batch ' . (10 + $i),
                    'program_id' => $programs->first()?->id ?? 1,
                    'status' => 'active',
                ]);
            }
            $groups = Group::all();
        }

        if ($rooms->isEmpty() || $programs->isEmpty()) {
            $this->command->error('Missing required base data (rooms or programs).');
            return;
        }

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addMonths(2);

        $this->command->info("Seeding lectures from {$startDate->toDateString()} to {$endDate->toDateString()}");

        $subjects = ['Web Development', 'Mobile Apps', 'Cyber Security', 'UI/UX Design', 'Database Management', 'Cloud Computing', 'Artificial Intelligence', 'Data Science'];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

            // Determine if it's an odd or even week
            $weekOfYear = $date->weekOfYear;
            $isOddWeek = ($weekOfYear % 2 !== 0);

            foreach ($rooms as $roomIndex => $room) {
                // Generate 3 to 6 lectures daily
                $lectureCount = rand(3, 6);

                // Vary selection based on odd/even week
                $offset = $isOddWeek ? 0 : 5;
                $roomInstructor = $instructors->get(($roomIndex + $offset) % $instructors->count());
                $roomGroup = $groups->get(($roomIndex + $offset) % $groups->count());

                for ($i = 0; $i < $lectureCount; $i++) {
                    $startHour = 8 + ($i * 2) + ($i * 0.5); // 8:00, 10:30, 13:00, 15:30...
                    $startTime = $date->copy()->startOfDay()->addHours(floor($startHour))->addMinutes(($startHour - floor($startHour)) * 60);
                    $endTime = $startTime->copy()->addHours(2);

                    $seed = $isOddWeek ? ($i + 1) : ($i + 15);
                    $programIndex = ($room->id + $seed) % $programs->count();
                    $sessionIndex = ($room->id + $seed) % $sessionTypes->count();
                    $subjectIndex = ($room->id + $seed) % count($subjects);

                    Lecture::create([
                        'program_id' => $programs[$programIndex]->id,
                        'group_id' => $roomGroup->id,
                        'instructor_profile_id' => $roomInstructor->id,
                        'room_id' => $room->id,
                        'session_type_id' => $sessionTypes[$sessionIndex]->id,
                        'subject' => $subjects[$subjectIndex] . ($isOddWeek ? ' (Odd Table)' : ' (Even Table)'),
                        'starts_at' => $startTime,
                        'ends_at' => $endTime,
                        'status' => 'scheduled',
                    ]);
                }
            }
        }

        $this->command->info('Lecture batch seeding completed!');
    }
}
