<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MigrateLegacyData extends Command
{
    protected $signature = 'app:migrate-legacy-data {--fresh : Whether to run migrate:fresh before starting} {--cleanup : Truncate target tables before migration}';
    protected $description = 'Professional migration from legacy PostgreSQL (ts_digi) to the current system (digi_test)';

    private $legacyConn = 'legacy';
    private $targetConn = 'pgsql';

    // To prevent deep cycles or repeated lookups, we store ID mappings
    private $mappings = [
        'users' => [],
        'users_by_email' => [],
        'governorates' => [],
        'session_types' => [],
        'tracks' => [],
        'job_profiles' => [],
        'training_companies' => [],
        'locations' => [], // campuses
        'buildings' => [],
        'floors' => [],
        'rooms' => [],
        'programs' => [],
        'groups' => [],
        'schedule_templates' => [],
        'lectures' => [],
        'instructor_profiles' => [], // instructor_id -> profile_id
        'trainee_profiles' => [], // trainee_id -> profile_id
        'ticket_categories' => [],
        'ticket_priorities' => [],
        'ticket_statuses' => [],
        'tickets' => [],
    ];

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $this->info('🚀 Starting professional legacy data migration...');

        if ($this->option('fresh')) {
            $this->warn('⚠️ Running migrate:fresh...');
            $this->call('migrate:fresh');
            $this->info('✅ Database reset completed.');
        } elseif ($this->option('cleanup')) {
            $this->cleanupData();
        }

        try {
            // 0. Environment Setup
            $this->syncModules();
            $this->seedInitialData();

            // 1. Core Lookups
            $this->migrateGovernorates();
            $this->migrateSessionTypes();

            // 2. Educational Structure
            $this->migrateTracksAndJobProfiles();
            $this->migrateTrainingCompanies();
            $this->migrateAcademicStructure();
            $this->migratePrograms();
            $this->migrateGroups();

            // 3. People & Profiles
            $this->migrateUsersAndProfiles();

            // 4. Scheduling & Attendance
            $this->migrateScheduleTemplates();
            $this->migrateLectures();
            $this->migrateAttendances();

            // 5. Tickets & Activity
            $this->migrateTicketLookups();
            $this->migrateTickets();
            $this->migrateTicketActivities();

            $this->info('🧹 Clearing permission cache...');
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $this->info('🎊 Migration completed successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Migration failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            throw $e;
        }
    }

    private function syncModules()
    {
        $this->info('🔧 Activating modules...');
        app(\Modules\Core\Application\Services\ModuleManagerService::class)->syncFromFilesystem();

        DB::connection($this->targetConn)->table('modules')
            ->where('slug', 'core')
            ->update(['is_core' => true, 'status' => 'active']);

        DB::connection($this->targetConn)->table('modules')
            ->where('status', 'registered')
            ->update(['status' => 'active']);

        app(\Modules\Core\Application\Services\ModuleManagerService::class)->refreshCache();
    }

    private function seedInitialData()
    {
        $this->info('🌱 Seeding core roles and permissions...');
        $this->call('db:seed', ['--class' => 'Database\Seeders\DatabaseSeeder']);
        $this->call('db:seed', ['--class' => 'Modules\Educational\Database\Seeders\EducationalRolesAndPermissionsSeeder']);
    }

    private function cleanupData()
    {
        $this->warn('🧹 Cleaning up existing data (Truncate Cascade)...');
        $tables = [
            'tickets.ticket_activities',
            'tickets.ticket_threads',
            'tickets.tickets',
            'tickets.ticket_categories',
            'tickets.ticket_statuses',
            'tickets.ticket_priorities',
            'education.attendances',
            'education.lectures',
            'education.schedule_templates',
            'education.groups',
            'education.programs',
            'education.job_profile_responsibles',
            'education.company_job_profiles',
            'education.job_profiles',
            'education.tracks',
            'education.instructor_company_assignments',
            'education.trainee_profiles',
            'education.instructor_profiles',
            'education.training_companies',
            'education.rooms',
            'education.floors',
            'education.buildings',
            'education.campuses',
            'education.session_types',
            'education.governorates',
            'model_has_roles',
            'users',
        ];

        foreach ($tables as $table) {
            DB::connection($this->targetConn)->statement("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE");
        }
        $this->info('✅ Clean-up done.');
    }

    private function migrateGovernorates()
    {
        $this->info('📍 Migrating governorates...');
        $legacyCities = DB::connection($this->legacyConn)->table('cities')->get();
        foreach ($legacyCities as $city) {
            $id = DB::connection($this->targetConn)->table('education.governorates')->insertGetId([
                'name_ar' => $city->name,
                'name_en' => $city->code, // Fallback
                'status' => 'active',
                'created_at' => $city->created_at ?? now(),
                'updated_at' => $city->updated_at ?? now(),
            ]);
            $this->mappings['governorates'][$city->id] = $id;
        }
    }

    private function migrateSessionTypes()
    {
        $this->info('📅 Migrating session types...');
        $legacyTypes = DB::connection($this->legacyConn)->table('session_types')->get();
        foreach ($legacyTypes as $type) {
            $id = DB::connection($this->targetConn)->table('education.session_types')->insertGetId([
                'name' => $type->name,
                'is_active' => strtolower($type->status) === 'active',
                'created_at' => $type->created_at ?? now(),
                'updated_at' => $type->updated_at ?? now(),
            ]);
            $this->mappings['session_types'][$type->id] = $id;
        }
    }

    private function migrateTracksAndJobProfiles()
    {
        $this->info('🛤️ Migrating tracks and job profiles...');

        // 1. Tracks first (parent_id is null and type = TRACK)
        $legacyTracks = DB::connection($this->legacyConn)->table('tracks')
            ->where('type', 'TRACK')
            ->get();

        foreach ($legacyTracks as $lTrack) {
            $id = DB::connection($this->targetConn)->table('education.tracks')->insertGetId([
                'name' => $lTrack->name,
                'code' => $lTrack->code ? ($lTrack->code . '-' . $lTrack->id) : 'TRK-' . $lTrack->id,
                'slug' => Str::slug($lTrack->name) . '-' . $lTrack->id,
                'is_active' => strtolower($lTrack->status) === 'active',
                'created_at' => $lTrack->created_at ?? now(),
                'updated_at' => $lTrack->updated_at ?? now(),
            ]);
            $this->mappings['tracks'][$lTrack->id] = $id;
        }

        // 2. Job Profiles (type = JOB_PROFILE, parent_id links to TRACK)
        $legacyJobProfiles = DB::connection($this->legacyConn)->table('tracks')
            ->where('type', 'JOB_PROFILE')
            ->get();

        foreach ($legacyJobProfiles as $lJob) {
            $trackId = $this->mappings['tracks'][$lJob->parent_id] ?? null;
            $id = DB::connection($this->targetConn)->table('education.job_profiles')->insertGetId([
                'track_id' => $trackId,
                'name' => $lJob->name,
                'code' => $lJob->code ? ($lJob->code . '-' . $lJob->id) : 'JOB-' . $lJob->id,
                'status' => strtolower($lJob->status) === 'active' ? 'active' : 'inactive',
                'created_at' => $lJob->created_at ?? now(),
                'updated_at' => $lJob->updated_at ?? now(),
            ]);
            $this->mappings['job_profiles'][$lJob->id] = $id;
        }
    }

    private function migrateTrainingCompanies()
    {
        $this->info('🏢 Migrating training companies...');
        $legacyProviders = DB::connection($this->legacyConn)->table('training_providers')->get();
        foreach ($legacyProviders as $lProv) {
            $id = DB::connection($this->targetConn)->table('education.training_companies')->insertGetId([
                'name' => $lProv->name,
                'registration_number' => 'REG-' . $lProv->id, // Fallback if missing
                'contact_email' => $lProv->email,
                'website' => $lProv->website,
                'address' => $lProv->address,
                'status' => strtolower($lProv->status) === 'active' ? 'active' : 'inactive',
                'created_at' => $lProv->created_at ?? now(),
                'updated_at' => $lProv->updated_at ?? now(),
            ]);
            $this->mappings['training_companies'][$lProv->id] = $id;

            // Migrate relationships to Tracks/JobProfiles if any (via training_provider_tracks)
            $legacyTPT = DB::connection($this->legacyConn)->table('training_provider_tracks')
                ->where('training_provider_id', $lProv->id)
                ->get();

            foreach ($legacyTPT as $tpt) {
                $jobProfileId = $this->mappings['job_profiles'][$tpt->track_id] ?? null;
                if ($jobProfileId) {
                    $exists = DB::connection($this->targetConn)->table('education.company_job_profiles')
                        ->where('company_id', $id)
                        ->where('job_profile_id', $jobProfileId)
                        ->exists();

                    if (!$exists) {
                        DB::connection($this->targetConn)->table('education.company_job_profiles')->insert([
                            'company_id' => $id,
                            'job_profile_id' => $jobProfileId,
                            'created_at' => $tpt->created_at ?? now(),
                            'updated_at' => $tpt->updated_at ?? now(),
                        ]);
                    }
                }
            }
        }
    }

    private function migrateAcademicStructure()
    {
        $this->info('🏫 Migrating campuses/buildings/floors/rooms...');
        $legacyAllLocations = DB::connection($this->legacyConn)->table('branch_locations')->get();

        // 1. Campuses
        foreach ($legacyAllLocations->where('branch_type', 'LOCATION') as $lLoc) {
            $id = DB::connection($this->targetConn)->table('education.campuses')->insertGetId([
                'name' => $lLoc->name,
                'code' => $lLoc->code ?? ('CAMP-' . $lLoc->id),
                'status' => 'active',
                'address' => $lLoc->address_location,
                'created_at' => $lLoc->created_at ?? now(),
                'updated_at' => $lLoc->updated_at ?? now(),
            ]);
            $this->mappings['locations'][$lLoc->id] = $id;
        }

        // 2. Buildings
        foreach ($legacyAllLocations->where('branch_type', 'BUILDING') as $lBuild) {
            $campusId = $this->mappings['locations'][$lBuild->parent_id] ?? null;
            if (!$campusId)
                continue;

            $id = DB::connection($this->targetConn)->table('education.buildings')->insertGetId([
                'campus_id' => $campusId,
                'name' => $lBuild->name,
                'code' => $lBuild->code ?? ('BUILD-' . $lBuild->id),
                'status' => 'active',
                'created_at' => $lBuild->created_at ?? now(),
                'updated_at' => $lBuild->updated_at ?? now(),
            ]);
            $this->mappings['buildings'][$lBuild->id] = $id;
        }

        // 3. Floors
        foreach ($legacyAllLocations->where('branch_type', 'FLOOR') as $lFloor) {
            $buildingId = $this->mappings['buildings'][$lFloor->parent_id] ?? null;
            if (!$buildingId)
                continue;

            $floorNumber = $lFloor->code ?? (string) $lFloor->id;

            // Handle duplicates within building
            $existingId = DB::connection($this->targetConn)->table('education.floors')
                ->where('building_id', $buildingId)
                ->where('floor_number', $floorNumber)
                ->value('id');

            if ($existingId) {
                $this->mappings['floors'][$lFloor->id] = $existingId;
                continue;
            }

            $id = DB::connection($this->targetConn)->table('education.floors')->insertGetId([
                'building_id' => $buildingId,
                'floor_number' => $floorNumber,
                'name' => $lFloor->name,
                'status' => 'active',
                'created_at' => $lFloor->created_at ?? now(),
                'updated_at' => $lFloor->updated_at ?? now(),
            ]);
            $this->mappings['floors'][$lFloor->id] = $id;
        }

        // 4. Rooms
        $legacyRooms = DB::connection($this->legacyConn)->table('class_rooms')->get();
        foreach ($legacyRooms as $lRoom) {
            $floorId = $this->mappings['floors'][$lRoom->branch_location_id] ?? null;
            if (!$floorId)
                continue;

            $code = 'ROOM-' . $lRoom->id;

            // Handle duplicates within floor
            $existingId = DB::connection($this->targetConn)->table('education.rooms')
                ->where('floor_id', $floorId)
                ->where('code', $code)
                ->value('id');

            if ($existingId) {
                $this->mappings['rooms'][$lRoom->id] = $existingId;
                continue;
            }

            $id = DB::connection($this->targetConn)->table('education.rooms')->insertGetId([
                'floor_id' => $floorId,
                'name' => $lRoom->name,
                'code' => $code,
                'capacity' => $lRoom->capacity ?? 30,
                'room_type' => 'lecture',
                'room_status' => 'active',
                'created_at' => $lRoom->created_at ?? now(),
                'updated_at' => $lRoom->updated_at ?? now(),
            ]);
            $this->mappings['rooms'][$lRoom->id] = $id;
        }
    }

    private function migratePrograms()
    {
        $this->info('📚 Migrating programs...');
        $legacyProgs = DB::connection($this->legacyConn)->table('study_programs')->get();
        foreach ($legacyProgs as $lProg) {
            $id = DB::connection($this->targetConn)->table('education.programs')->insertGetId([
                'name' => $lProg->name,
                'code' => 'PROG-' . $lProg->id,
                'status' => 'published',
                'starts_at' => $lProg->start_date,
                'ends_at' => $lProg->end_date,
                'created_at' => $lProg->created_at ?? now(),
                'updated_at' => $lProg->updated_at ?? now(),
            ]);
            $this->mappings['programs'][$lProg->id] = $id;
        }
    }

    private function migrateGroups()
    {
        $this->info('👥 Migrating groups...');
        $legacyGroups = DB::connection($this->legacyConn)->table('class_groups')->get();
        foreach ($legacyGroups as $lGroup) {
            $programId = $this->mappings['programs'][$lGroup->study_program_id] ?? null;
            if (!$programId)
                continue;

            $id = DB::connection($this->targetConn)->table('education.groups')->insertGetId([
                'program_id' => $programId,
                'name' => $lGroup->round_group ?? ('G-' . $lGroup->id),
                'capacity' => $lGroup->max_trainees ?? 25,
                'status' => strtolower($lGroup->status) === 'active' ? 'active' : 'inactive',
                'created_at' => $lGroup->created_at ?? now(),
                'updated_at' => $lGroup->updated_at ?? now(),
            ]);
            $this->mappings['groups'][$lGroup->id] = $id;
        }
    }

    private function migrateUsersAndProfiles()
    {
        $this->info('👤 Migrating users and profiles (Instructors & Trainees)...');

        $legacyUsers = DB::connection($this->legacyConn)->table('users')->get();
        $this->output->progressStart(count($legacyUsers));

        foreach ($legacyUsers as $lUser) {
            $email = strtolower(trim($lUser->email));
            if (empty($email)) {
                $this->output->progressAdvance();
                continue;
            }

            // Handle duplicates in migration source
            if (isset($this->mappings['users_by_email'][$email])) {
                $this->mappings['users'][$lUser->id] = $this->mappings['users_by_email'][$email];
                $this->output->progressAdvance();
                continue;
            }

            $nameParts = explode(' ', $lUser->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '.';

            $newUserId = DB::connection($this->targetConn)->table('users')->insertGetId([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $email,
                'email' => $email,
                'password' => $lUser->password,
                'phone' => $lUser->phone,
                'status' => 'active',
                'language' => 'ar',
                'theme_mode' => 'light',
                'timezone' => 'Africa/Cairo',
                'security_risk_level' => 'low',
                'joined_at' => $lUser->created_at ?? now(),
                'created_at' => $lUser->created_at ?? now(),
                'updated_at' => $lUser->updated_at ?? now(),
            ]);

            $this->mappings['users'][$lUser->id] = $newUserId;
            $this->mappings['users_by_email'][$email] = $newUserId;

            // Role assignment
            $this->assignUserRole($newUserId, $lUser);

            // Handle Instructor Profile
            $lInstructor = DB::connection($this->legacyConn)->table('instructors')
                ->where('email', $email)->first();
            if ($lInstructor) {
                $profileId = DB::connection($this->targetConn)->table('education.instructor_profiles')->insertGetId([
                    'user_id' => $newUserId,
                    'governorate_id' => $this->mappings['governorates'][$lInstructor->city_id] ?? null,
                    'track_id' => $this->mappings['tracks'][$lInstructor->track_id] ?? null,
                    'national_id' => encrypt($lInstructor->nationalID),
                    'passport_number' => encrypt($lInstructor->passport_number),
                    'date_of_birth' => $lInstructor->birthdate,
                    'gender' => strtolower($lInstructor->gender) === 'male' ? 'male' : 'female',
                    'employment_type' => 'external',
                    'status' => 'active',
                    'arabic_name' => $lInstructor->name_ar,
                    'english_name' => $lInstructor->name_en,
                    'address' => $lInstructor->address,
                    'created_at' => $lInstructor->created_at ?? now(),
                    'updated_at' => $lInstructor->updated_at ?? now(),
                ]);
                $this->mappings['instructor_profiles'][$lInstructor->id] = $profileId;
            }

            // Handle Trainee Profile
            $lTrainee = DB::connection($this->legacyConn)->table('trainees')
                ->where('email', $email)->first();
            if ($lTrainee) {
                $lTGT = DB::connection($this->legacyConn)->table('group_trainees')
                    ->where('trainee_id', $lTrainee->id)->first();

                $profileId = DB::connection($this->targetConn)->table('education.trainee_profiles')->insertGetId([
                    'user_id' => $newUserId,
                    'program_id' => $this->mappings['programs'][$lTrainee->study_program_id] ?? null,
                    'group_id' => $lTGT ? ($this->mappings['groups'][$lTGT->class_group_id] ?? null) : null,
                    'job_profile_id' => $this->mappings['job_profiles'][$lTrainee->track_id] ?? null,
                    'governorate_id' => $this->mappings['governorates'][$lTrainee->city_id] ?? null,
                    'national_id' => encrypt($lTrainee->nationalID),
                    'passport_number' => encrypt($lTrainee->passport_number),
                    'religion' => encrypt($lTrainee->religion ?? ''),
                    'gender' => strtolower($lTrainee->gender) === 'male' ? 'male' : 'female',
                    'date_of_birth' => $lTrainee->birthdate,
                    'enrollment_status' => 'active',
                    'arabic_name' => $lTrainee->name_ar,
                    'english_name' => $lTrainee->name_en,
                    'address' => $lTrainee->address,
                    'nationality' => $lTrainee->nationality,
                    'created_at' => $lTrainee->created_at ?? now(),
                    'updated_at' => $lTrainee->updated_at ?? now(),
                ]);
                $this->mappings['trainee_profiles'][$lTrainee->id] = $profileId;
            }

            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
    }

    private function assignUserRole($userId, $lUser)
    {
        $roleName = match ($lUser->type) {
            'ADMINISTRATOR' => 'super-admin',
            'AGENT' => 'QA',
            'USER' => 'Trainee',
            default => 'Trainee'
        };

        // Custom mappings from previous version
        $lRole = DB::connection($this->legacyConn)->table('role_users')
            ->join('roles', 'role_users.role_id', '=', 'roles.id')
            ->where('role_users.user_id', $lUser->id)
            ->select('roles.name')->first();

        if ($lRole) {
            $roleName = match ($lRole->name) {
                'مدير النظام' => 'super-admin',
                'اداره المبادره' => 'admin',
                'طالب' => 'Trainee',
                'شركة تدريب' => 'QA',
                default => $roleName
            };
        }

        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        DB::connection($this->targetConn)->table('model_has_roles')->insert([
            'role_id' => $role->id,
            'model_type' => 'user',
            'model_id' => $userId
        ]);
    }

    private function migrateScheduleTemplates()
    {
        $this->info('🗓️ Migrating schedule templates (academic_schedules)...');
        $legacyScheds = DB::connection($this->legacyConn)->table('academic_schedules')->get();
        foreach ($legacyScheds as $lSched) {
            // Attempt to find an instructor for this schedule from attendance_days
            $instructorId = DB::connection($this->legacyConn)->table('attendance_days')
                ->where('academic_schedule_id', $lSched->id)
                ->whereNotNull('instructor_id')
                ->value('instructor_id');

            $targetInstructorId = $this->mappings['instructor_profiles'][$instructorId] ?? null;

            if (!$targetInstructorId) {
                // Fallback: pick any active instructor if this template was never used
                $targetInstructorId = DB::connection($this->targetConn)->table('education.instructor_profiles')->value('id');
            }

            if (!$targetInstructorId)
                continue;

            try {
                $id = DB::connection($this->targetConn)->table('education.schedule_templates')->insertGetId([
                    'program_id' => $this->mappings['programs'][$lSched->study_program_id] ?? null,
                    'group_id' => $this->mappings['groups'][$lSched->class_group_id] ?? null,
                    'instructor_profile_id' => $targetInstructorId,
                    'room_id' => $this->mappings['rooms'][$lSched->class_room_id] ?? null,
                    'session_type_id' => $this->mappings['session_types'][$lSched->session_type_id] ?? null,
                    'day_of_week' => $lSched->number_day ?? 0,
                    'start_time' => $lSched->start_time,
                    'end_time' => $lSched->end_time,
                    'is_active' => true,
                    'created_at' => $lSched->created_at ?? now(),
                    'updated_at' => $lSched->updated_at ?? now(),
                ]);
                $this->mappings['schedule_templates'][$lSched->id] = $id;
            } catch (\Exception $e) {
                // Skip if error
            }
        }
    }

    private function migrateLectures()
    {
        $this->info('📖 Migrating lectures (attendance_days)...');
        $legacyLectures = DB::connection($this->legacyConn)->table('attendance_days')->cursor();
        foreach ($legacyLectures as $lDay) {
            try {
                $id = DB::connection($this->targetConn)->table('education.lectures')->insertGetId([
                    'program_id' => $this->mappings['programs'][$lDay->study_program_id] ?? null,
                    'group_id' => $this->mappings['groups'][$lDay->class_group_id] ?? null,
                    'instructor_profile_id' => $this->mappings['instructor_profiles'][$lDay->instructor_id] ?? null,
                    'room_id' => $this->mappings['rooms'][$lDay->class_room_id] ?? null,
                    'session_type_id' => $this->mappings['session_types'][$lDay->session_type_id] ?? null,
                    'starts_at' => Carbon::parse($lDay->date . ' ' . $lDay->time),
                    'ends_at' => Carbon::parse($lDay->date . ' ' . $lDay->to_time),
                    'status' => 'scheduled',
                    'created_at' => $lDay->created_at ?? now(),
                    'updated_at' => $lDay->updated_at ?? now(),
                ]);
                $this->mappings['lectures'][$lDay->id] = $id;
            } catch (\Exception $e) {
                // Skip conflicts
            }
        }
    }

    private function migrateAttendances()
    {
        $this->info('✅ Migrating attendances...');
        $legacyAtt = DB::connection($this->legacyConn)->table('attendance_trainees')->cursor();
        foreach ($legacyAtt as $lAtt) {
            $traineeProfileId = $this->mappings['trainee_profiles'][$lAtt->trainee_id] ?? null;
            $lectureId = $this->mappings['lectures'][$lAtt->attendance_day_id] ?? null;

            if ($traineeProfileId && $lectureId) {
                $exists = DB::connection($this->targetConn)->table('education.attendances')
                    ->where('lecture_id', $lectureId)
                    ->where('trainee_profile_id', $traineeProfileId)
                    ->exists();

                if (!$exists) {
                    DB::connection($this->targetConn)->table('education.attendances')->insert([
                        'lecture_id' => $lectureId,
                        'trainee_profile_id' => $traineeProfileId,
                        'status' => strtolower($lAtt->status) === 'presence' ? 'present' : 'absent',
                        'created_at' => $lAtt->created_at ?? now(),
                        'updated_at' => $lAtt->updated_at ?? now(),
                    ]);
                }
            }
        }
    }

    private function migrateTicketLookups()
    {
        $this->info('🎫 Migrating ticket lookups...');

        $lStatuses = DB::connection($this->legacyConn)->table('status_tickets')->get();
        foreach ($lStatuses as $ls) {
            $id = DB::connection($this->targetConn)->table('tickets.ticket_statuses')->insertGetId([
                'name' => $ls->name,
                'color' => $ls->color ?? '#000000',
                'is_default' => $ls->is_default ?? false,
                'is_final' => $ls->is_final ?? false,
                'created_at' => $ls->created_at ?? now(),
                'updated_at' => $ls->updated_at ?? now(),
            ]);
            $this->mappings['ticket_statuses'][$ls->id] = $id;
        }

        $lPrios = DB::connection($this->legacyConn)->table('priority_tickets')->get();
        foreach ($lPrios as $lp) {
            $id = DB::connection($this->targetConn)->table('tickets.ticket_priorities')->insertGetId([
                'name' => $lp->name,
                'color' => $lp->color ?? '#000000',
                'sla_multiplier' => 1.0,
                'created_at' => $lp->created_at ?? now(),
                'updated_at' => $lp->updated_at ?? now(),
            ]);
            $this->mappings['ticket_priorities'][$lp->id] = $id;
        }

        $lCats = DB::connection($this->legacyConn)->table('subject_tickets')->get();
        foreach ($lCats as $lc) {
            $id = DB::connection($this->targetConn)->table('tickets.ticket_categories')->insertGetId([
                'name' => $lc->name,
                'created_at' => $lc->created_at ?? now(),
                'updated_at' => $lc->updated_at ?? now(),
            ]);
            $this->mappings['ticket_categories'][$lc->id] = $id;
        }
    }

    private function migrateTickets()
    {
        $this->info('🎫 Migrating tickets and threads...');
        $lTickets = DB::connection($this->legacyConn)->table('ticket_headers')->cursor();

        foreach ($lTickets as $lt) {
            $userId = $this->mappings['users'][$lt->user_id] ?? null;
            if (!$userId)
                continue;

            $id = DB::connection($this->targetConn)->table('tickets.tickets')->insertGetId([
                'uuid' => Str::uuid(),
                'ticket_number' => $lt->ticket_number . '-' . $lt->id,
                'user_id' => $userId,
                'subject' => $lt->title,
                'details' => $lt->description ?? 'No description',
                'status_id' => $this->mappings['ticket_statuses'][$lt->status_id] ?? 1,
                'priority_id' => $this->mappings['ticket_priorities'][$lt->priority_id] ?? 1,
                'category_id' => $this->mappings['ticket_categories'][$lt->subject_id] ?? null,
                'lecture_id' => $this->mappings['lectures'][$lt->attendance_day_id] ?? null,
                'created_at' => $lt->created_at ?? now(),
                'updated_at' => $lt->updated_at ?? now(),
            ]);
            $this->mappings['tickets'][$lt->id] = $id;

            // Migrate Threads
            $lDetails = DB::connection($this->legacyConn)->table('ticket_detals')
                ->where('ticket_id', $lt->id)->get();
            foreach ($lDetails as $ld) {
                DB::connection($this->targetConn)->table('tickets.ticket_threads')->insert([
                    'ticket_id' => $id,
                    'user_id' => $this->mappings['users'][$ld->created_by] ?? $userId,
                    'content' => $ld->notes ?? '',
                    'type' => 'message',
                    'created_at' => $ld->created_at ?? now(),
                    'updated_at' => $ld->updated_at ?? now(),
                ]);
            }
        }
    }

    private function migrateTicketActivities()
    {
        $this->info('📜 Migrating ticket activity logs...');
        $lHistories = DB::connection($this->legacyConn)->table('ticket_histories')->cursor();
        foreach ($lHistories as $lh) {
            $ticketId = $this->mappings['tickets'][$lh->ticket_id] ?? null;
            $userId = $this->mappings['users'][$lh->created_by] ?? null;

            if ($ticketId) {
                DB::connection($this->targetConn)->table('tickets.ticket_activities')->insert([
                    'ticket_id' => $ticketId,
                    'user_id' => $userId,
                    'activity_type' => strtolower($lh->type),
                    'description' => "Changed {$lh->category} from '{$lh->old_value}' to '{$lh->new_value}'",
                    'properties' => json_encode(['old' => $lh->old_value, 'new' => $lh->new_value, 'category' => $lh->category]),
                    'created_at' => $lh->created_at ?? now(),
                    'updated_at' => $lh->updated_at ?? now(),
                ]);
            }
        }
    }
}
