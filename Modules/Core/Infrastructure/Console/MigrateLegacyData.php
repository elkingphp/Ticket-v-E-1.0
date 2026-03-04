<?php

namespace Modules\Core\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MigrateLegacyData extends Command
{
    protected $signature = 'db:migrate-legacy';
    protected $description = 'Migrates data from the legacy database to the new modular system';

    protected $idMapping = [
        'governorates' => [],
        'campuses' => [],
        'buildings' => [],
        'floors' => [],
        'rooms' => [],
        'programs' => [],
        'tracks' => [],
        'job_profiles' => [],
        'companies' => [],
        'session_types' => [],
        'users' => [],
        'groups' => [],
        'trainees' => [],
        'instructors' => [],
        'lectures' => [],
        'attendances' => [],
        'evaluation_forms' => [],
    ];

    protected function getMappedId($tableName, $oldId)
    {
        if (isset($this->idMapping[$tableName][$oldId])) {
            return $this->idMapping[$tableName][$oldId];
        }

        $mapped = DB::table('migration_id_map')
            ->where('table_name', $tableName)
            ->where('old_id', $oldId)
            ->value('new_id');

        if ($mapped) {
            $this->idMapping[$tableName][$oldId] = $mapped;
        }

        return $mapped;
    }

    protected function setMappedId($tableName, $oldId, $newId)
    {
        $this->idMapping[$tableName][$oldId] = $newId;

        DB::table('migration_id_map')->updateOrInsert(
            ['table_name' => $tableName, 'old_id' => $oldId],
            ['new_id' => $newId, 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function handle()
    {
        $this->info("Starting migration from legacy database (STRICT MODE)...");

        DB::beginTransaction();

        try {
            $this->migrateGovernorates();
            $this->migrateAcademicResources(); // Campuses, Buildings, Floors, Rooms
            $this->migrateEducationalBasics(); // Programs, Tracks, SessionTypes, Companies
            $this->migrateUsersAndRoles();
            $this->migrateRBAC(); // NEW: Migrating roles and assignments
            $this->resetSequences(); // Reset after users to avoid ID conflicts for profiles
            $this->migrateProfiles(); // Instructors, Trainees
            $this->migrateGroupsAndEnrollments();
            $this->migrateAcademicSchedules(); // NEW: Migrating base schedules
            $this->migrateSchedulesAndAttendance();
            $this->migrateTicketTaxonomy(); // NEW: Migrating categories tree
            $this->migrateTickets();
            $this->migrateEvaluations();

            $this->resetSequences(); // Final reset
            DB::commit();
            $this->info("Migration completed successfully in strictly transactional mode!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Migration FAILED resulting in full rollback: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
    protected function migrateGovernorates()
    {
        $this->info("Migrating Governorates...");
        $legacyCities = DB::connection('legacy')->table('cities')->get();
        foreach ($legacyCities as $city) {
            DB::table('education.governorates')->updateOrInsert(
                ['id' => $city->id],
                [
                    'name_ar' => $city->name,
                    'name_en' => $city->name, // Copying same for now
                    'status' => 'active',
                    'created_at' => $city->created_at ?? now(),
                    'updated_at' => $city->updated_at ?? now(),
                ]
            );
            $this->setMappedId('governorates', $city->id, $city->id);
        }
    }

    protected function migrateAcademicResources()
    {
        $this->info("Migrating Academic Resources (Campuses, Buildings, Floors)...");

        // 1. Campuses
        $legacyCampuses = DB::connection('legacy')->table('branch_locations')->where('branch_type', 'LOCATION')->get();
        foreach ($legacyCampuses as $camp) {
            DB::table('education.campuses')->updateOrInsert(
                ['id' => $camp->id],
                [
                    'name' => $camp->name,
                    'code' => $camp->code ?: strtoupper(Str::slug($camp->name)),
                    'status' => 'active',
                    'address' => $camp->address ?? null,
                    'created_at' => $camp->created_at ?? now(),
                    'updated_at' => $camp->updated_at ?? now(),
                ]
            );
            $this->setMappedId('campuses', $camp->id, $camp->id);
        }

        // 2. Buildings
        $legacyBuildings = DB::connection('legacy')->table('branch_locations')->where('branch_type', 'BUILDING')->get();
        foreach ($legacyBuildings as $bld) {
            DB::table('education.buildings')->updateOrInsert(
                ['id' => $bld->id],
                [
                    'name' => $bld->name,
                    'code' => ($bld->code ?: 'BLD') . '-' . $bld->id,
                    'campus_id' => $this->getMappedId('campuses', $bld->parent_id),
                    'status' => 'active',
                    'created_at' => $bld->created_at ?? now(),
                    'updated_at' => $bld->updated_at ?? now(),
                ]
            );
            $this->setMappedId('buildings', $bld->id, $bld->id);
        }

        // 3. Floors
        $legacyFloors = DB::connection('legacy')->table('branch_locations')->where('branch_type', 'FLOOR')->get();
        foreach ($legacyFloors as $flr) {
            DB::table('education.floors')->updateOrInsert(
                ['id' => $flr->id],
                [
                    'name' => $flr->name,
                    'floor_number' => ($flr->code ?: 'FLR') . '-' . $flr->id,
                    'building_id' => $this->getMappedId('buildings', $flr->parent_id),
                    'status' => 'active',
                    'created_at' => $flr->created_at ?? now(),
                    'updated_at' => $flr->updated_at ?? now(),
                ]
            );
            $this->setMappedId('floors', $flr->id, $flr->id);
        }

        // 4. Rooms
        $this->info("Migrating Rooms...");
        $legacyRooms = DB::connection('legacy')->table('class_rooms')->get();
        foreach ($legacyRooms as $room) {
            $floorId = $this->getMappedId('floors', $room->branch_location_id);

            if (!$floorId) {
                // Try to find a building or campus and get/create a floor
                $bldId = $this->getMappedId('buildings', $room->branch_location_id);
                if ($bldId) {
                    $floorId = DB::table('education.floors')->where('building_id', $bldId)->value('id');
                    if (!$floorId) {
                        DB::table('education.floors')->insert([
                            'building_id' => $bldId,
                            'name' => 'General Floor',
                            'floor_number' => 'GEN-' . $bldId,
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $floorId = DB::table('education.floors')->where('building_id', $bldId)->value('id');
                        // No legacy ID for this auto-created floor, maybe we don't map it or use a special one
                    }
                } else {
                    $campusId = $this->getMappedId('campuses', $room->branch_location_id);
                    if ($campusId) {
                        $bldId = DB::table('education.buildings')->where('campus_id', $campusId)->value('id');
                        if (!$bldId) {
                            DB::table('education.buildings')->insert([
                                'campus_id' => $campusId,
                                'name' => 'General Building',
                                'code' => 'GEN-BLD-' . $campusId,
                                'status' => 'active',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $bldId = DB::table('education.buildings')->where('campus_id', $campusId)->value('id');
                        }
                        $floorId = DB::table('education.floors')->where('building_id', $bldId)->value('id');
                        if (!$floorId) {
                            DB::table('education.floors')->insert([
                                'building_id' => $bldId,
                                'name' => 'General Floor',
                                'floor_number' => 'GEN-' . $bldId,
                                'status' => 'active',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $floorId = DB::table('education.floors')->where('building_id', $bldId)->value('id');
                        }
                    }
                }
            }

            DB::table('education.rooms')->updateOrInsert(
                ['id' => $room->id],
                [
                    'name' => $room->name,
                    'code' => strtoupper(Str::slug($room->name ?: 'RM')) . '-' . $room->id,
                    'floor_id' => $floorId,
                    'room_type' => 'lecture',
                    'room_status' => 'active',
                    'capacity' => $room->capacity ?? 30,
                    'created_at' => $room->created_at ?? now(),
                    'updated_at' => $room->updated_at ?? now(),
                ]
            );
            $this->setMappedId('rooms', $room->id, $room->id);
        }
    }

    protected function migrateEducationalBasics()
    {
        $this->info("Migrating Programs, Tracks, JobProfiles, Companies...");

        // 1. Programs
        $legacyPrograms = DB::connection('legacy')->table('study_programs')->get();
        foreach ($legacyPrograms as $prog) {
            $code = strtoupper(Str::slug($prog->name ?: 'PROG')) . '-' . $prog->id;

            DB::table('education.programs')->updateOrInsert(
                ['id' => $prog->id],
                [
                    'name' => $prog->name,
                    'code' => $code,
                    'status' => 'published',
                    'created_at' => $prog->created_at ?? now(),
                    'updated_at' => $prog->updated_at ?? now(),
                ]
            );
            $this->setMappedId('programs', $prog->id, $prog->id);
        }

        // 2. Tracks
        $legacyTracks = DB::connection('legacy')->table('tracks')->get();
        foreach ($legacyTracks as $track) {
            $slug = Str::slug($track->name);
            // Unique check for slug
            if (DB::table('education.tracks')->where('slug', $slug)->where('id', '!=', $track->id)->exists()) {
                $slug .= '-' . $track->id;
            }

            DB::table('education.tracks')->updateOrInsert(
                ['id' => $track->id],
                [
                    'name' => $track->name,
                    'slug' => $slug,
                    'is_active' => true,
                    'created_at' => $track->created_at ?? now(),
                    'updated_at' => $track->updated_at ?? now(),
                ]
            );
            $this->setMappedId('tracks', $track->id, $track->id);

            // Job Profile (Job Profile in new = Track in old sometimes)
            DB::table('education.job_profiles')->updateOrInsert(
                ['id' => $track->id],
                [
                    'track_id' => $track->id,
                    'name' => $track->name,
                    'code' => strtoupper($slug),
                    'status' => 'active',
                    'created_at' => $track->created_at ?? now(),
                    'updated_at' => $track->updated_at ?? now(),
                ]
            );
            $this->setMappedId('job_profiles', $track->id, $track->id);
        }

        // 3. Session Types
        $legacyTypes = DB::connection('legacy')->table('session_types')->get();
        foreach ($legacyTypes as $type) {
            DB::table('education.session_types')->updateOrInsert(
                ['id' => $type->id],
                [
                    'name' => $type->name,
                    'is_active' => true,
                    'created_at' => $type->created_at ?? now(),
                    'updated_at' => $type->updated_at ?? now(),
                ]
            );
            $this->setMappedId('session_types', $type->id, $type->id);
        }

        // 4. Companies
        $legacyCompanies = DB::connection('legacy')->table('training_providers')->get();
        foreach ($legacyCompanies as $comp) {
            DB::table('education.training_companies')->updateOrInsert(
                ['id' => $comp->id],
                [
                    'name' => $comp->name,
                    'contact_email' => $comp->email ?? ('comp' . $comp->id . '@example.com'),
                    'status' => strtolower($comp->status ?? 'active'),
                    'created_at' => $comp->created_at ?? now(),
                    'updated_at' => $comp->updated_at ?? now(),
                ]
            );
            $this->setMappedId('companies', $comp->id, $comp->id);
        }
    }

    protected function migrateUsersAndRoles()
    {
        $this->info("Migrating Users...");

        $usedPhones = DB::table('users')->whereNotNull('phone')->pluck('id', 'phone')->toArray();
        $usedUsernames = DB::table('users')->pluck('id', 'username')->toArray();
        $usedEmails = DB::table('users')->pluck('id', 'email')->toArray();

        $legacyUsers = DB::connection('legacy')->table('users')->get();
        foreach ($legacyUsers as $user) {
            $parts = explode(' ', $user->name, 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? ' ';

            $email = $user->email ?: "user_{$user->id}@digilians.com";
            $username = strstr($email, '@', true) ?: ('user_' . $user->id);
            $phone = $user->phone ?? null;

            // Resolve email conflict
            if (isset($usedEmails[$email]) && $usedEmails[$email] != $user->id) {
                $email = "dup_" . $user->id . "_" . $email;
            }
            $usedEmails[$email] = $user->id;

            // Resolve username conflict
            if (isset($usedUsernames[$username]) && $usedUsernames[$username] != $user->id) {
                $username = $username . $user->id;
            }
            $usedUsernames[$username] = $user->id;

            // Resolve phone conflict
            if ($phone && isset($usedPhones[$phone]) && $usedPhones[$phone] != $user->id) {
                $phone = substr($phone, 0, 10) . $user->id;
            }
            if ($phone)
                $usedPhones[$phone] = $user->id;

            DB::table('users')->updateOrInsert(
                ['id' => $user->id],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'username' => $username,
                    'phone' => $phone,
                    'password' => $user->password ?? Hash::make('password'),
                    'avatar' => $user->avatar ?? null,
                    'status' => strtolower($user->status ?: 'active'),
                    'email_verified_at' => now(),
                    'created_at' => $user->created_at ?? now(),
                    'updated_at' => $user->updated_at ?? now(),
                ]
            );
            $this->setMappedId('users', $user->id, $user->id);
        }
    }

    protected function migrateRBAC()
    {
        $this->info("Migrating Roles and Assignments...");
        $legacyRoles = DB::connection('legacy')->table('roles')->get();

        foreach ($legacyRoles as $lRole) {
            $newName = match ($lRole->name) {
                'مدير النظام' => 'super-admin',
                'test' => 'editor',
                'طالب' => 'trainee',
                'شركة تدريب' => 'company',
                'مراقب تقارير' => 'monitor',
                'IT MCIT' => 'it-admin',
                'موظفي الوزارة' => 'staff',
                'اداره المبادره', ' اداره المبادره' => 'management',
                'wavz' => 'wavz-admin',
                default => Str::slug($lRole->name)
            };

            $role = \Modules\Users\Domain\Models\Role::updateOrCreate(
                ['name' => $newName, 'guard_name' => 'web'],
                ['display_name' => $lRole->name]
            );
            $this->setMappedId('roles', $lRole->id, $role->id);
        }

        $legacyAssignments = DB::connection('legacy')->table('role_users')->get();
        foreach ($legacyAssignments as $assign) {
            $newUserId = $this->getMappedId('users', $assign->user_id);
            $newRoleId = $this->getMappedId('roles', $assign->role_id);
            if ($newUserId && $newRoleId) {
                // Manually insert into model_has_roles to avoid events and speed up
                DB::table('model_has_roles')->updateOrInsert(
                    ['role_id' => $newRoleId, 'model_id' => $newUserId, 'model_type' => 'Modules\Users\Domain\Models\User'],
                    []
                );
            }
        }
    }

    protected function migrateProfiles()
    {
        $this->info("Migrating Profiles...");

        // Drop unique constraints temporarily for profiles to handle duplicate emails/users in legacy
        DB::statement('ALTER TABLE education.instructor_profiles DROP CONSTRAINT IF EXISTS education_instructor_profiles_user_id_unique');
        DB::statement('ALTER TABLE education.trainee_profiles DROP CONSTRAINT IF EXISTS education_trainee_profiles_user_id_unique');

        // 1. Instructors
        $legacyInstructors = DB::connection('legacy')->table('instructors')->get();
        foreach ($legacyInstructors as $ins) {
            $email = $ins->email ?: ("ins_{$ins->id}@example.com");
            $userId = DB::table('users')->where('email', $email)->value('id');

            if (!$userId) {
                $name = $ins->name_en ?? $ins->name_ar ?? 'Instructor';
                $parts = explode(' ', $name, 2);
                $userId = DB::table('users')->insertGetId([
                    'first_name' => $parts[0] ?? 'Instructor',
                    'last_name' => $parts[1] ?? ($ins->id),
                    'username' => 'ins_' . $ins->id,
                    'email' => $email,
                    'phone' => $ins->phone ?? null,
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('education.instructor_profiles')->updateOrInsert(
                ['id' => $ins->id],
                [
                    'user_id' => $userId,
                    'arabic_name' => $ins->name_ar ?? 'Instructor',
                    'english_name' => $ins->name_en ?? (string) $ins->id,
                    'national_id' => $ins->nationalID ? encrypt($ins->nationalID) : null,
                    'status' => 'active',
                    'created_at' => $ins->created_at ?? now(),
                    'updated_at' => $ins->updated_at ?? now(),
                ]
            );
            $this->setMappedId('instructors', $ins->id, $ins->id);
        }

        // 2. Trainees
        $legacyTrainees = DB::connection('legacy')->table('trainees')->get();
        foreach ($legacyTrainees as $tr) {
            $email = $tr->email ?: ("tr_{$tr->id}@example.com");
            $userId = DB::table('users')->where('email', $email)->value('id');

            if (!$userId) {
                $name = $tr->name_en ?? $tr->name_ar ?? 'Trainee';
                $parts = explode(' ', $name, 2);
                $userId = DB::table('users')->insertGetId([
                    'first_name' => $parts[0] ?? 'Trainee',
                    'last_name' => $parts[1] ?? ($tr->id),
                    'username' => 'tr_' . $tr->id,
                    'email' => $email,
                    'phone' => $tr->phone ?? null,
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('education.trainee_profiles')->updateOrInsert(
                ['id' => $tr->id],
                [
                    'user_id' => $userId,
                    'arabic_name' => $tr->name_ar ?? 'Trainee',
                    'english_name' => $tr->name_en ?? (string) $tr->id,
                    'address' => $tr->address ?? null,
                    'nationality' => $tr->nationality ?? null,
                    'national_id' => $tr->nationalID ? encrypt($tr->nationalID) : null,
                    'job_profile_id' => $this->getMappedId('job_profiles', $tr->track_id),
                    'governorate_id' => $this->getMappedId('governorates', $tr->city_id),
                    'enrollment_status' => 'active',
                    'created_at' => $tr->created_at ?? now(),
                    'updated_at' => $tr->updated_at ?? now(),
                ]
            );
            $this->setMappedId('trainees', $tr->id, $tr->id);
        }
    }

    protected function migrateGroupsAndEnrollments()
    {
        $this->info("Migrating Groups and Trainee Enrollments...");

        $legacyGroups = DB::connection('legacy')->table('class_groups')->get();
        foreach ($legacyGroups as $group) {
            DB::table('education.groups')->updateOrInsert(
                ['id' => $group->id],
                [
                    'name' => $group->name_round_group ?? ('Group ' . $group->id),
                    'program_id' => $this->getMappedId('programs', $group->study_program_id),
                    'job_profile_id' => $this->getMappedId('job_profiles', $group->track_id),
                    'capacity' => $group->max_trainees ?? 0,
                    'status' => strtolower($group->status ?: 'active'),
                    'created_at' => $group->created_at ?? now(),
                    'updated_at' => $group->updated_at ?? now(),
                ]
            );
            $this->setMappedId('groups', $group->id, $group->id);
        }

        // Enrollments
        $legacyEnrollments = DB::connection('legacy')->table('group_trainees')->get();
        foreach ($legacyEnrollments as $en) {
            $traineeId = $this->getMappedId('trainees', $en->trainee_id);
            if ($traineeId) {
                DB::table('education.trainee_profiles')
                    ->where('id', $traineeId)
                    ->update(['group_id' => $this->getMappedId('groups', $en->class_group_id)]);
            }
        }
    }

    protected function migrateAcademicSchedules()
    {
        $this->info("Migrating Academic Schedules...");
        $legacySchedules = DB::connection('legacy')->table('academic_schedules')->get();
        foreach ($legacySchedules as $sch) {
            $dayOfWeek = 0;
            try {
                $dayOfWeek = \Carbon\Carbon::parse($sch->day)->dayOfWeek;
            } catch (\Exception $e) {
                $dayOfWeek = ($sch->number_day % 7);
            }

            // Find an instructor from linked attendance_days
            $instructorId = DB::connection('legacy')->table('attendance_days')
                ->where('academic_schedule_id', $sch->id)
                ->whereNotNull('instructor_id')
                ->value('instructor_id');

            $instructorProfileId = $instructorId ? $this->getMappedId('instructors', $instructorId) : null;

            if (!$instructorProfileId) {
                // Find ANY instructor linked to this group
                $instructorId = DB::connection('legacy')->table('attendance_days')
                    ->where('class_group_id', $sch->class_group_id)
                    ->whereNotNull('instructor_id')
                    ->value('instructor_id');
                $instructorProfileId = $instructorId ? $this->getMappedId('instructors', $instructorId) : DB::table('education.instructor_profiles')->value('id');
            }

            if (!$instructorProfileId)
                continue; // Should not happen after profiles are migrated

            DB::table('education.schedule_templates')->updateOrInsert(
                ['id' => $sch->id],
                [
                    'program_id' => $this->getMappedId('programs', $sch->study_program_id),
                    'group_id' => $this->getMappedId('groups', $sch->class_group_id),
                    'instructor_profile_id' => $instructorProfileId,
                    'room_id' => $this->getMappedId('rooms', $sch->class_room_id),
                    'session_type_id' => $this->getMappedId('session_types', $sch->session_type_id),
                    'day_of_week' => $dayOfWeek,
                    'start_time' => $sch->start_time,
                    'end_time' => $sch->end_time,
                    'effective_from' => $sch->created_at ?? now(),
                    'is_active' => strtolower($sch->status ?? 'active') === 'active',
                    'recurrence_type' => 'weekly',
                    'created_at' => $sch->created_at ?? now(),
                    'updated_at' => $sch->updated_at ?? now(),
                ]
            );
            $this->setMappedId('academic_schedules', $sch->id, $sch->id);
        }
    }

    protected function migrateSchedulesAndAttendance()
    {
        $this->info("Migrating Lectures and Attendance...");

        // Drop overlapping constraints temporarily as legacy data might have conflicts
        DB::statement('ALTER TABLE education.lectures DROP CONSTRAINT IF EXISTS no_group_double_booking');
        DB::statement('ALTER TABLE education.lectures DROP CONSTRAINT IF EXISTS no_instructor_double_booking');
        DB::statement('ALTER TABLE education.lectures DROP CONSTRAINT IF EXISTS no_room_double_booking');

        DB::connection('legacy')->table('attendance_days')->orderBy('id')->chunk(500, function ($lectures) {
            foreach ($lectures as $lec) {
                $progId = $this->getMappedId('programs', $lec->study_program_id);
                $groupId = $this->getMappedId('groups', $lec->class_group_id);

                if ($progId && $groupId) {
                    DB::table('education.lectures')->updateOrInsert(
                        ['id' => $lec->id],
                        [
                            'program_id' => $progId,
                            'group_id' => $groupId,
                            'session_type_id' => $this->getMappedId('session_types', $lec->session_type_id),
                            'instructor_profile_id' => $this->getMappedId('instructors', $lec->instructor_id),
                            'room_id' => $this->getMappedId('rooms', $lec->class_room_id),
                            'starts_at' => ($lec->date ?? '2025-01-01') . ' ' . ($lec->time ?? '09:00:00'),
                            'ends_at' => ($lec->date ?? '2025-01-01') . ' ' . ($lec->to_time ?? '12:00:00'),
                            'status' => 'completed',
                            'created_at' => $lec->created_at ?? now(),
                            'updated_at' => $lec->updated_at ?? now(),
                        ]
                    );
                    $this->setMappedId('lectures', $lec->id, $lec->id);
                }
            }
        });

        // Attendance
        $this->info("Migrating Attendance...");
        $reasons = DB::connection('legacy')->table('attendance_reasons')->pluck('reason_text', 'attendance_trainee_id')->toArray();
        DB::table('legacy_data_conflicts')->where('source_table', 'attendance_trainees')->delete();

        // Memory array to detect duplicates securely
        $seenAttendances = [];

        DB::connection('legacy')->table('attendance_trainees')->orderByDesc('updated_at')->orderByDesc('id')->chunk(5000, function ($attendances) use ($reasons, &$seenAttendances) {
            $inserts = [];
            $conflicts = [];
            foreach ($attendances as $att) {
                $traineeProfileId = $this->getMappedId('trainees', $att->trainee_id);
                $lectureId = $this->getMappedId('lectures', $att->attendance_day_id);

                if ($traineeProfileId && $lectureId) {
                    $key = "{$lectureId}_{$traineeProfileId}";
                    if (isset($seenAttendances[$key])) {
                        // Document as duplicate
                        $conflicts[] = [
                            'source_table' => 'attendance_trainees',
                            'legacy_id' => $att->id,
                            'conflict_type' => 'duplicate_attendance',
                            'duplicate_key' => $key,
                            'payload_json' => json_encode([
                                'lecture_id' => $lectureId,
                                'trainee_profile_id' => $traineeProfileId,
                                'legacy_updated_at' => $att->updated_at ?? null,
                            ]),
                            'resolution_status' => 'pending',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } else {
                        $seenAttendances[$key] = true;

                        $newStatus = match ($att->status) {
                            'ABSENCE' => 'absent',
                            'LATE' => 'late',
                            'EXCUSED' => 'excused',
                            default => 'present',
                        };

                        $inserts[] = [
                            'legacy_id' => $att->id, // Traceability Mapping!
                            'lecture_id' => $lectureId,
                            'trainee_profile_id' => $traineeProfileId,
                            'status' => $newStatus,
                            'notes' => $reasons[$att->id] ?? null,
                            'created_at' => $att->created_at ?? now(),
                            'updated_at' => $att->updated_at ?? now(),
                        ];
                    }
                }
            }
            if (!empty($inserts)) {
                DB::table('education.attendances')->insert($inserts);
            }
            if (!empty($conflicts)) {
                DB::table('legacy_data_conflicts')->insert($conflicts);
            }
        });
    }

    protected function migrateTicketTaxonomy()
    {
        $this->info("Migrating Ticket Taxonomy Tree...");

        try {
            DB::table('tickets.ticket_stages')->updateOrInsert(
                ['id' => 1],
                [
                    'name' => 'General',
                    'sla_hours' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        } catch (\Exception $e) {
            $this->error("Failed to create default ticket stage: " . $e->getMessage());
        }

        $subjects = DB::connection('legacy')->table('subject_tickets')
            ->orderByRaw('parent_id ASC NULLS FIRST')
            ->orderBy('id')
            ->get();

        $circularDetect = [];

        foreach ($subjects as $s) {
            // Self-referencing fix
            if ($s->id == $s->parent_id) {
                $s->parent_id = null;
            }

            // Circular dependency detection
            $curr = $s;
            $depth = 0;
            $isCircular = false;
            while ($curr && $curr->parent_id && $depth < 20) {
                if (isset($circularDetect[$curr->id]) && in_array($curr->parent_id, $circularDetect[$curr->id])) {
                    $isCircular = true;
                    break;
                }
                $circularDetect[$curr->id][] = $curr->parent_id;
                $p = DB::connection('legacy')->table('subject_tickets')->where('id', $curr->parent_id)->first();
                $curr = $p;
                $depth++;
                if ($depth >= 20)
                    $isCircular = true; // Hard break any deep logic issues
            }

            if ($isCircular) {
                $this->error("Circular dependency detected on legacy subject ID: {$s->id}. Breaking link.");
                $s->parent_id = null;
            }

            try {
                if (!$s->parent_id) {
                    // Level 0 -> Category
                    $newId = DB::table('tickets.ticket_categories')->insertGetId([
                        'legacy_id' => $s->id,
                        'name' => $s->name,
                        'stage_id' => 1,
                        'sla_hours' => ($s->sla_minutes ?? 0) / 60,
                        'created_at' => $s->created_at ?? now(),
                        'updated_at' => $s->updated_at ?? now()
                    ]);
                    $this->setMappedId('tickets.categories', $s->id, $newId);
                } else {
                    $parent = DB::connection('legacy')->table('subject_tickets')->where('id', $s->parent_id)->first();
                    if ($parent && (!$parent->parent_id || $parent->id == $parent->parent_id)) {
                        // Level 1 -> Complaint
                        $compId = DB::table('tickets.ticket_complaints')->insertGetId([
                            'legacy_id' => $s->id,
                            'category_id' => $this->getMappedId('tickets.categories', $s->parent_id) ?? 1,
                            'name' => $s->name,
                            'sla_hours' => ($s->sla_minutes ?? 1440) / 60,
                            'created_at' => $s->created_at ?? now(),
                            'updated_at' => $s->updated_at ?? now()
                        ]);
                        $this->setMappedId('tickets.complaints', $s->id, $compId);
                    } else {
                        // Level 2+ -> Sub Complaint (Collapse into level-1's parent)
                        $complaintId = null;
                        $curr = $s;
                        while ($curr && $curr->parent_id) {
                            $p = DB::connection('legacy')->table('subject_tickets')->where('id', $curr->parent_id)->first();
                            if ($p && (!$p->parent_id || $p->id == $p->parent_id)) {
                                $complaintId = $this->getMappedId('tickets.complaints', $curr->id);
                                break;
                            }
                            $curr = $p;
                        }
                        // Fallback mapping if broken tree
                        if (!$complaintId)
                            $complaintId = DB::table('tickets.ticket_complaints')->first()->id ?? 1;

                        if ($complaintId) {
                            $subId = DB::table('tickets.ticket_sub_complaints')->insertGetId([
                                'legacy_id' => $s->id,
                                'complaint_id' => $complaintId,
                                'name' => $s->name,
                                'created_at' => $s->created_at ?? now(),
                                'updated_at' => $s->updated_at ?? now()
                            ]);
                            $this->setMappedId('ticket_sub_complaints', $s->id, $subId);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error migrating subject {$s->id}: " . $e->getMessage());
            }
        }
    }

    protected function migrateTickets()
    {
        $this->info("Migrating Ticket Taxonomy and Data...");

        $sysUserId = DB::table('users')->first()->id ?? 1;

        // 1. Statuses
        $legacyStatuses = DB::connection('legacy')->table('status_tickets')->get();
        foreach ($legacyStatuses as $st) {
            try {
                DB::table('tickets.ticket_statuses')->updateOrInsert(
                    ['id' => $st->id],
                    [
                        'legacy_id' => $st->id,
                        'name' => $st->name,
                        'color' => $st->color ?? '#666',
                        'is_default' => false,
                        'is_final' => false,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            } catch (\Exception $e) {
                $this->error("Error migrating status {$st->id}: " . $e->getMessage());
            }
        }

        // 2. Priorities
        $legacyPriorities = DB::connection('legacy')->table('priority_tickets')->get();
        foreach ($legacyPriorities as $pr) {
            try {
                DB::table('tickets.ticket_priorities')->updateOrInsert(
                    ['id' => $pr->id],
                    [
                        'legacy_id' => $pr->id,
                        'name' => $pr->name,
                        'color' => $pr->color ?? '#666',
                        'is_default' => false,
                        'sla_multiplier' => 1.0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
                $this->setMappedId('tickets.priorities', $pr->id, $pr->id);
            } catch (\Exception $e) {
                $this->error("Error migrating priority {$pr->id}: " . $e->getMessage());
            }
        }

        // 3. Tickets & Final Assignment Status
        $this->info("Migrating Ticket Headers...");
        // Get the FINAL active assignment per ticket effectively
        $latestAssignments = DB::connection('legacy')->table('ticket_assign_tos')
            ->whereNotNull('assign_to_user_id')
            ->orderByDesc('created_at')
            ->get()
            ->unique('ticket_id');

        $assignmentsMap = [];
        foreach ($latestAssignments as $la) {
            $assignmentsMap[$la->ticket_id] = $la->assign_to_user_id;
        }

        $legacyTickets = DB::connection('legacy')->table('ticket_headers')->get();
        foreach ($legacyTickets as $t) {
            try {
                $realUserId = $this->getMappedId('users', $t->user_id) ?? $this->getMappedId('users', $t->created_by);
                if ($realUserId && !DB::table('users')->where('id', $realUserId)->exists()) {
                    $realUserId = null;
                }

                // Fallback to sys user if orphaned
                if (!$realUserId)
                    $realUserId = $sysUserId;

                $catId = $this->getMappedId('tickets.categories', $t->subject_id);
                $compId = $this->getMappedId('tickets.complaints', $t->subject_id);

                if (!$catId && !$compId) {
                    $subId = $this->getMappedId('ticket_sub_complaints', $t->subject_id);
                    if ($subId) {
                        $sub = DB::table('tickets.ticket_sub_complaints')->where('id', $subId)->first();
                        $compId = $sub ? $sub->complaint_id : null;
                        if ($compId) {
                            $comp = DB::table('tickets.ticket_complaints')->where('id', $compId)->first();
                            $catId = $comp ? $comp->category_id : null;
                        }
                    }
                } elseif ($compId && !$catId) {
                    $comp = DB::table('tickets.ticket_complaints')->where('id', $compId)->first();
                    $catId = $comp ? $comp->category_id : null;
                }

                $assignedUser = $this->getMappedId('users', $assignmentsMap[$t->id] ?? null);
                if ($assignedUser && !DB::table('users')->where('id', $assignedUser)->exists()) {
                    $assignedUser = null;
                }

                DB::table('tickets.tickets')->updateOrInsert(
                    ['id' => $t->id],
                    [
                        'legacy_id' => $t->id,
                        'subject' => $t->title ?? $t->subject ?? 'Legacy Ticket',
                        'details' => $t->description ?? $t->subject ?? '...',
                        'uuid' => (string) Str::uuid(),
                        'user_id' => $realUserId,
                        'category_id' => $catId,
                        'complaint_id' => $compId,
                        'status_id' => DB::table('tickets.ticket_statuses')->where('id', $t->status_id)->exists() ? $t->status_id : 1,
                        'priority_id' => DB::table('tickets.ticket_priorities')->where('id', $t->priority_id)->exists() ? $t->priority_id : 1,
                        'assigned_to' => $assignedUser,
                        'ticket_number' => ($t->ticket_number ?? 'LEG') . '-' . $t->id,
                        'created_at' => $t->created_at ?? now(),
                        'updated_at' => $t->updated_at ?? now(),
                    ]
                );
                $this->setMappedId('tickets', $t->id, $t->id);
            } catch (\Exception $e) {
                $this->error("Error migrating ticket {$t->id}: " . $e->getMessage());
            }
        }

        // 4. Details (Threads)
        $this->info("Migrating Ticket Details...");
        $legacyDetails = DB::connection('legacy')->table('ticket_detals')->orderBy('created_at')->get();
        foreach ($legacyDetails as $d) {
            try {
                $realUserId = $this->getMappedId('users', $d->created_by);
                if ($realUserId && !DB::table('users')->where('id', $realUserId)->exists()) {
                    $realUserId = $sysUserId;
                }

                $ticketId = $this->getMappedId('tickets', $d->ticket_id);
                if (!$ticketId || !$realUserId)
                    continue;

                DB::table('tickets.ticket_threads')->updateOrInsert(
                    ['id' => $d->id],
                    [
                        'legacy_id' => $d->id,
                        'ticket_id' => $ticketId,
                        'user_id' => $realUserId,
                        'content' => $d->notes ?? '...',
                        'type' => 'message',
                        'is_read_by_staff' => false,
                        'is_read_by_user' => true,
                        'created_at' => $d->created_at ?? now(),
                        'updated_at' => $d->updated_at ?? now(),
                    ]
                );
            } catch (\Exception $e) {
                $this->error("Error migrating thread {$d->id}: " . $e->getMessage());
            }
        }

        // 5. Assignment History & Timeline reconstruction
        $this->info("Reconstructing Timeline Activities...");
        $allAssigns = DB::connection('legacy')->table('ticket_assign_tos')->orderBy('created_at')->get();
        foreach ($allAssigns as $a) {
            $ticketId = $this->getMappedId('tickets', $a->ticket_id);
            $newUserId = $this->getMappedId('users', $a->assign_to_user_id);
            $actionUserId = $this->getMappedId('users', $a->created_by) ?? $sysUserId;

            if ($ticketId) {
                if ($newUserId) {
                    DB::table('tickets.ticket_activities')->insert([
                        'legacy_id' => $a->id,
                        'ticket_id' => $ticketId,
                        'user_id' => $actionUserId,
                        'activity_type' => 'assignment',
                        'description' => 'Ticket assigned to user',
                        'properties' => json_encode(['assigned_to' => $newUserId]),
                        'created_at' => $a->created_at ?? now(),
                        'updated_at' => $a->updated_at ?? now()
                    ]);
                } elseif ($a->assign_to_role_id) {
                    $newRole = DB::table('roles')->where('id', $a->assign_to_role_id)->first();
                    if ($newRole) {
                        DB::table('tickets.ticket_activities')->insert([
                            'legacy_id' => $a->id,
                            'ticket_id' => $ticketId,
                            'user_id' => $actionUserId,
                            'activity_type' => 'assignment',
                            'description' => 'Ticket assigned to group/role',
                            'properties' => json_encode(['assigned_group_id' => $newRole->id, 'group_name' => $newRole->name]),
                            'created_at' => $a->created_at ?? now(),
                            'updated_at' => $a->updated_at ?? now()
                        ]);
                    }
                }
            }
        }

        $allHistories = DB::connection('legacy')->table('ticket_histories')->orderBy('created_at')->get();
        foreach ($allHistories as $h) {
            $ticketId = $this->getMappedId('tickets', $h->ticket_id);
            $actionUserId = $this->getMappedId('users', $h->created_by) ?? $sysUserId;
            if ($ticketId) {
                DB::table('tickets.ticket_activities')->insert([
                    'legacy_id' => $h->id,
                    'ticket_id' => $ticketId,
                    'user_id' => $actionUserId,
                    'activity_type' => 'history',
                    'description' => "{$h->category} changed",
                    'properties' => json_encode(['old_value' => $h->old_value, 'new_value' => $h->new_value, 'type' => $h->type]),
                    'created_at' => $h->created_at ?? now(),
                    'updated_at' => $h->updated_at ?? now()
                ]);
            }
        }
    }

    protected function migrateEvaluations()
    {
        $this->info("Migrating Evaluation Forms and Questions...");

        // 1. Evaluation Types
        try {
            DB::table('education.evaluation_types')->updateOrInsert(
                ['slug' => 'general'],
                [
                    'name' => 'General',
                    'target_type' => 'lecture',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        } catch (\Exception $e) {
            $this->error("Error creating evaluation type: " . $e->getMessage());
        }
        $realTypeId = DB::table('education.evaluation_types')->where('slug', 'general')->value('id');

        // 2. Forms
        $legacyForms = DB::connection('legacy')->table('form_auditing')->get();
        foreach ($legacyForms as $f) {
            try {
                DB::table('education.evaluation_forms')->updateOrInsert(
                    ['id' => $f->id],
                    [
                        'title' => $f->Subject_title ?? 'Legacy Form ' . $f->id,
                        'form_type_id' => $realTypeId,
                        'type' => 'general',
                        'status' => 'published',
                        'created_at' => $f->created_at ?? now(),
                        'updated_at' => $f->updated_at ?? now(),
                    ]
                );
                $this->setMappedId('evaluation_forms', $f->id, $f->id);
            } catch (\Exception $e) {
                $this->error("Error migrating form {$f->id}: " . $e->getMessage());
            }
        }

        // 3. Questions
        $legacyQuestions = DB::connection('legacy')->table('audit_question')->get();
        foreach ($legacyQuestions as $q) {
            try {
                $link = DB::connection('legacy')->table('question_form')->where('audit_question_id', $q->id)->first();
                $formId = $link ? $this->getMappedId('evaluation_forms', $link->form_auditing_id) : null;

                if (!$formId) {
                    $formId = DB::table('education.evaluation_forms')->value('id');
                }
                if (!$formId)
                    continue;

                $newType = match (strtolower($q->answer_type ?? 'text')) {
                    'choice' => 'multiple_choice',
                    'yes_no' => 'boolean',
                    'rating' => 'rating_1_to_5',
                    default => 'text',
                };

                DB::table('education.evaluation_questions')->updateOrInsert(
                    ['id' => $q->id],
                    [
                        'form_id' => $formId,
                        'question_text' => $q->question_ar ?? $q->question_en ?? 'Empty Question',
                        'type' => $newType,
                        'options' => $q->choices ? json_encode(explode(',', $q->choices)) : null,
                        'is_required' => $q->is_required ?? false,
                        'order_index' => $link->order_question ?? 0,
                        'created_at' => $q->created_at ?? now(),
                        'updated_at' => $q->updated_at ?? now(),
                    ]
                );
                $this->setMappedId('evaluation_questions', $q->id, $q->id);
            } catch (\Exception $e) {
                $this->error("Error migrating question {$q->id}: " . $e->getMessage());
            }
        }

        // 4. Answers
        // 4. Answers
        $this->info("Migrating Evaluation Answers...");

        DB::table('legacy_data_conflicts')->where('source_table', 'answer_form')->delete();

        $seenEvaluations = [];
        $seenAnswers = [];

        DB::connection('legacy')->table('answer_form')->orderByDesc('updated_at')->orderByDesc('id')->chunk(5000, function ($legacyAnswers) use (&$seenEvaluations, &$seenAnswers) {
            $evaluationsMap = []; // local cache to reduce db overhead
            $answersToInsert = [];
            $conflicts = [];

            foreach ($legacyAnswers as $ans) {
                // Do not use try-catch block here to prevent silent failures!
                $realUserId = $this->getMappedId('users', $ans->user_id);
                if ($realUserId && !DB::table('users')->where('id', $realUserId)->exists()) {
                    $realUserId = null;
                }

                $lectureId = $this->getMappedId('lectures', $ans->attendance_day_id);
                $formId = $this->getMappedId('evaluation_forms', $ans->form_auditing_id);

                if ($lectureId && $formId && $realUserId) {
                    $mapKey = "{$lectureId}_{$formId}_{$realUserId}";

                    if (!isset($evaluationsMap[$mapKey])) {
                        if (!isset($seenEvaluations[$mapKey])) {
                            $evalId = DB::table('education.lecture_evaluations')->where([
                                'lecture_id' => $lectureId,
                                'form_id' => $formId,
                                'evaluator_id' => $realUserId,
                            ])->value('id');

                            if (!$evalId) {
                                $traineeProfileId = DB::table('education.trainee_profiles')->where('user_id', $realUserId)->value('id');
                                $evalId = DB::table('education.lecture_evaluations')->insertGetId([
                                    'legacy_id' => $ans->id, // Traceability Mapping
                                    'lecture_id' => $lectureId,
                                    'form_id' => $formId,
                                    'evaluator_type' => 'trainee',
                                    'evaluator_id' => $traineeProfileId ?? $realUserId,
                                    'created_at' => $ans->created_at ?? now(),
                                    'updated_at' => $ans->updated_at ?? now(),
                                ]);
                            }
                            $seenEvaluations[$mapKey] = $evalId;
                        }
                        $evaluationsMap[$mapKey] = $seenEvaluations[$mapKey];
                    }
                    $evalId = $evaluationsMap[$mapKey];

                    if ($evalId && $ans->audit_question_id) {
                        $answerKey = "{$evalId}_{$ans->audit_question_id}";

                        if (isset($seenAnswers[$answerKey])) {
                            $conflicts[] = [
                                'source_table' => 'answer_form',
                                'legacy_id' => $ans->id,
                                'conflict_type' => 'duplicate_evaluation_answer',
                                'duplicate_key' => $answerKey,
                                'payload_json' => json_encode([
                                    'lecture_evaluation_id' => $evalId,
                                    'question_id' => $ans->audit_question_id,
                                    'legacy_updated_at' => $ans->updated_at ?? null,
                                ]),
                                'resolution_status' => 'pending',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        } else {
                            $seenAnswers[$answerKey] = true;
                            $answersToInsert[] = [
                                'legacy_id' => $ans->id, // Traceability Mapping!
                                'lecture_evaluation_id' => $evalId,
                                'question_id' => $ans->audit_question_id,
                                'answer_value' => $ans->answer_text ?? $ans->answer_choices ?? $ans->answer_yes_no ?? null,
                                'created_at' => $ans->created_at ?? now(),
                                'updated_at' => $ans->updated_at ?? now(),
                            ];
                        }
                    }
                }
            }

            if (!empty($answersToInsert)) {
                DB::table('education.evaluation_answers')->insert($answersToInsert);
            }
            if (!empty($conflicts)) {
                DB::table('legacy_data_conflicts')->insert($conflicts);
            }
        });
    }

    protected function resetSequences()
    {
        $this->info("Resetting sequences for tables...");

        $tables = [
            'education.governorates',
            'education.campuses',
            'education.buildings',
            'education.floors',
            'education.rooms',
            'education.programs',
            'education.tracks',
            'education.job_profiles',
            'education.training_companies',
            'education.session_types',
            'users',
            'education.instructor_profiles',
            'education.trainee_profiles',
            'education.groups',
            'education.lectures',
            'education.attendances',
            'tickets.ticket_statuses',
            'tickets.ticket_priorities',
            'tickets.tickets',
            'tickets.ticket_threads',
            // Add other tables that might have auto-incrementing IDs
        ];

        foreach ($tables as $table) {
            try {
                $maxId = DB::table($table)->max('id');
                if ($maxId !== null) {
                    // For PostgreSQL
                    DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), ?, true)", [$maxId]);
                    // For MySQL (if using auto_increment)
                    // DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = ?", [$maxId + 1]);
                    $this->info("Sequence for {$table} reset to " . ($maxId + 1));
                }
            } catch (\Exception $e) {
                $this->warn("Could not reset sequence for {$table}: " . $e->getMessage());
            }
        }
    }
}
