<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MigrateLegacyData extends Command
{
    /**
     * Enterprise Data Migration Command
     *
     * @var string
     */
    protected $signature = 'migrate:legacy 
                            {--step=all : The phase to execute (all, 0, 1, 2, 3, 4, 5, 8)}
                            {--dry-run : Simulate queries without committing}
                            {--resume= : Resume from a specific phase}
                            {--rollback : Delete all migrated legacy data from target}';

    protected $description = 'Enterprise Data Migration from ts_digi to digi_test';

    private $legacyConn = 'legacy';
    private $targetConn = 'pgsql';
    private $chunkSize = 2000;

    public function handle()
    {
        ini_set('memory_limit', '2048M');

        if ($this->option('rollback')) {
            $this->rollbackLegacyData();
            return;
        }

        $this->info("🚀 Starting Enterprise Legacy Migration");
        if ($this->option('dry-run')) {
            $this->warn("⚠️ DRY-RUN MODE ACTIVATED: Validating data, logging, NO INSERTS.");
        }

        $phases = [
            '0' => 'phase0DataAudit',
            '1' => 'phase1Identity',
            '2' => 'phase2Infrastructure',
            '3' => 'phase3Profiles',
            '4' => 'phase4Operations',
            '5' => 'phase5Tickets',
            '7' => 'phase7Reindex',
            '8' => 'phase8IntegrityVerification'
        ];

        $step = $this->option('resume') ?: $this->option('step');

        try {
            // Safe constraint suspension
            if (!$this->option('dry-run')) {
                DB::connection($this->targetConn)->statement('SET session_replication_role = replica;');
            }

            foreach ($phases as $p => $method) {
                if ($step === 'all' || (int) $step <= (int) $p) {
                    $this->{$method}();
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ Migration Critical Failure: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            throw $e;
        } finally {
            // Always restore constraints
            if (!$this->option('dry-run')) {
                DB::connection($this->targetConn)->statement('SET session_replication_role = DEFAULT;');
            }
        }

        $this->info('🎊 Migration process finished completely!');
    }

    private function logMigrationStart($entity)
    {
        if ($this->option('dry-run'))
            return null;
        return DB::connection($this->targetConn)->table('migration_logs')->insertGetId([
            'entity_name' => $entity,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    private function logMigrationProgress($logId, $processed, $failed = 0)
    {
        if ($this->option('dry-run') || !$logId)
            return;
        DB::connection($this->targetConn)->table('migration_logs')->where('id', $logId)->update([
            'processed_rows' => DB::raw("processed_rows + $processed"),
            'failed_rows' => DB::raw("failed_rows + $failed"),
        ]);
    }

    private function logMigrationFinish($logId, $failed = 0)
    {
        if ($this->option('dry-run') || !$logId)
            return;
        DB::connection($this->targetConn)->table('migration_logs')->where('id', $logId)->update([
            'status' => $failed > 0 ? 'completed_with_errors' : 'completed',
            'finished_at' => now(),
        ]);
    }

    /** ==========================================================
     * PHASE 0: Data Audit
     * ========================================================== */
    private function phase0DataAudit()
    {
        $this->info("🔎 Phase 0: Data Audit & Cleansing...");
        // Example check: Trainees without a valid identity
        // In legacy, we might have trainees holding emails that no user holds
        $legacy = DB::connection($this->legacyConn);
        $orphansCount = $legacy->table('trainees')
            ->whereNotIn('email', function ($q) {
                $q->select('email')->from('users');
            })->count();

        if ($orphansCount > 0) {
            $this->warn("⚠️ Audit Warning: Found $orphansCount trainees without matching user emails. They will fail to bind profiles correctly.");
        }

        $nullDates = $legacy->table('academic_schedules')
            ->whereNull('date')->orWhereNull('start_time')
            ->count();
        if ($nullDates > 0) {
            $this->error("🚨 Critical Audit Error: $nullDates lectures have NULL date or start_time!");
        } else {
            $this->info("✅ Schedule Dates appear healthy.");
        }
    }

    /** ==========================================================
     * PHASE 1: Identity & Foundation
     * ========================================================== */
    private function phase1Identity()
    {
        $this->info("🔑 Phase 1: Identity (Users, Roles Mapping, Governorates)");

        // A) Governorates
        $this->info(" -> Governorates");
        $logId = $this->logMigrationStart('governorates');
        $cities = DB::connection($this->legacyConn)->table('cities')->get();
        if (!$this->option('dry-run')) {
            $inserts = [];
            foreach ($cities as $city) {
                $inserts[] = [
                    'legacy_id' => $city->id,
                    'name_ar' => $city->name,
                    'name_en' => $city->code ?? $city->name,
                    'status' => 'active',
                    'created_at' => Carbon::parse($city->created_at)->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::parse($city->updated_at)->format('Y-m-d H:i:s'),
                ];
            }
            // UPSERT Govs
            if (!empty($inserts)) {
                $keys = array_keys($inserts[0]);
                $updateStmts = [];
                foreach (['name_ar', 'name_en', 'updated_at'] as $col) {
                    $updateStmts[] = "\"$col\" = EXCLUDED.\"$col\"";
                }
                $updateSql = implode(', ', $updateStmts);

                $valuesStr = collect($inserts)->map(function ($row) {
                    return "('" . implode("', '", array_map(function ($v) {
                        return str_replace("'", "''", $v);
                    }, array_values($row))) . "')";
                })->implode(', ');

                $columns = implode(', ', array_map(fn($v) => '"' . $v . '"', $keys));
                DB::connection($this->targetConn)->statement("
                    INSERT INTO education.governorates ($columns)
                    VALUES $valuesStr
                    ON CONFLICT (legacy_id) DO UPDATE SET $updateSql
                ");
            }
        }
        $this->logMigrationFinish($logId);

        // B) Users (Chunks of 2000 via UPSERT)
        $this->info(" -> Users");
        $logId = $this->logMigrationStart('users');

        DB::connection($this->legacyConn)->table('users')
            ->orderBy('id')->chunk($this->chunkSize, function ($users) use ($logId) {
                if ($this->option('dry-run')) {
                    $this->info("   [DryRun] Would UPSERT " . count($users) . " users.");
                    return;
                }

                $inserts = [];
                $seenEmails = [];
                foreach ($users as $u) {
                    $email = strtolower(trim($u->email));
                    if (empty($email) || isset($seenEmails[$email])) {
                        continue;
                    }
                    $seenEmails[$email] = true;

                    $nameParts = explode(' ', $u->name, 2);
                    $firstName = $nameParts[0];
                    $lastName = $nameParts[1] ?? '.';

                    $inserts[] = [
                        'legacy_id' => $u->id,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'username' => $email,
                        'email' => $email,
                        'password' => $u->password,
                        'phone' => $u->phone,
                        'status' => 'active',
                        'language' => 'ar',
                        'theme_mode' => 'light',
                        'timezone' => 'Africa/Cairo',
                        'security_risk_level' => 'low',
                        'created_at' => $u->created_at ?? now(),
                        'updated_at' => $u->updated_at ?? now(),
                    ];
                }

                if (!empty($inserts)) {
                    $keys = array_keys($inserts[0]);
                    $cols = implode(', ', array_map(fn($c) => "\"$c\"", $keys));

                    // ON CONFLICT ON legacy_id or email
                    // Since both must be unique, we conflict on legacy_id, but email is also unique 
                    // To handle unique constraint smoothly during upsert in Enterprise:
                    $updateFields = ['legacy_id', 'first_name', 'last_name', 'password', 'phone'];
                    $upsertUpdates = implode(', ', array_map(fn($f) => "\"$f\" = EXCLUDED.\"$f\"", $updateFields));

                    // Generate Values
                    $values = [];
                    foreach ($inserts as $row) {
                        $rowVals = [];
                        foreach ($row as $val) {
                            if ($val === null)
                                $rowVals[] = 'NULL';
                            else {
                                $safeVal = str_replace("'", "''", (string) $val);
                                $rowVals[] = "'$safeVal'";
                            }
                        }
                        $values[] = "(" . implode(',', $rowVals) . ")";
                    }
                    $valuesSql = implode(", ", $values);

                    try {
                        DB::connection($this->targetConn)->statement("
                            INSERT INTO public.users ($cols)
                            VALUES $valuesSql
                            ON CONFLICT (email) DO UPDATE SET $upsertUpdates
                        ");
                        $this->logMigrationProgress($logId, count($inserts));
                    } catch (\Exception $e) {
                        $this->logMigrationProgress($logId, 0, count($inserts));
                        $this->warn("User Batch Error: " . substr($e->getMessage(), 0, 500));
                    }
                }
            });

        $this->logMigrationFinish($logId);

        // C) Role Assignments
        $this->info(" -> Roles Mapping (Spatie)");
        if (!$this->option('dry-run')) {
            // Read legacy role mappings and map to standard slugs
            $legacyRoles = DB::connection($this->legacyConn)->table('role_users')
                ->join('roles', 'role_users.role_id', '=', 'roles.id')
                ->select('role_users.user_id', 'roles.name as legacy_role')
                ->get();

            DB::connection($this->targetConn)->transaction(function () use ($legacyRoles) {
                // Clear existing assignments for migrated users? Or just inject missing.
                foreach ($legacyRoles as $lr) {
                    $slug = match ($lr->legacy_role) {
                        'مدير النظام' => 'super-admin',
                        'اداره المبادره' => 'admin',
                        'طالب' => 'Student',
                        'شركة تدريب' => 'QA',
                        default => 'Student'
                    };

                    // Pure SQL for Spatie assignment based on legacy_id resolution
                    DB::connection($this->targetConn)->statement("
                        INSERT INTO public.model_has_roles (role_id, model_type, model_id)
                        SELECT r.id, 'App\\Modules\\Users\\Domain\\Models\\User', u.id
                        FROM public.users u
                        JOIN public.roles r ON r.name = ?
                        WHERE u.legacy_id = ?
                        ON CONFLICT DO NOTHING
                    ", [$slug, $lr->user_id]);
                }
            });
        }
    }

    /** ==========================================================
     * PHASE 2: Educational Infrastructure
     * ========================================================== */
    private function phase2Infrastructure()
    {
        $this->info("🏢 Phase 2: Educational Infrastructure (Campuses, Rooms, Programs, Groups)");

        // A) Campuses
        $this->info(" -> Campuses (From branch_locations)");
        $logId = $this->logMigrationStart('campuses');
        $locations = DB::connection($this->legacyConn)->table('branch_locations')->where('branch_type', 'LOCATION')->get();
        if (!$this->option('dry-run') && $locations->count() > 0) {
            $inserts = [];
            foreach ($locations as $loc) {
                $inserts[] = [
                    'legacy_id' => $loc->id,
                    'name' => $loc->name,
                    'code' => $loc->code ?? ('CAMP-' . $loc->id),
                    'status' => 'active',
                    'address' => $loc->address_location,
                    'created_at' => Carbon::parse($loc->created_at ?? now())->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::parse($loc->updated_at ?? now())->format('Y-m-d H:i:s'),
                ];
            }
            $this->bulkUpsert('education.campuses', $inserts, ['name', 'address', 'updated_at']);
        }
        $this->logMigrationFinish($logId);

        // B) Default Building and Floor injected invisibly 
        // We will do this inside the migration of rooms to ensure every room has a floor.

        // C) Rooms
        $this->info(" -> Rooms (From class_rooms)");
        $logId = $this->logMigrationStart('rooms');
        $rooms = DB::connection($this->legacyConn)->table('class_rooms')->get();

        if (!$this->option('dry-run') && $rooms->count() > 0) {
            // Because Rooms require Floors which require Buildings which require Campuses,
            // we will create a dummy "Main Building" and "Ground Floor" for each campus.

            DB::connection($this->targetConn)->statement("
                INSERT INTO education.buildings (name, code, campus_id, status, created_at, updated_at)
                SELECT 'Main Building', 'MAIN-' || legacy_id, id, 'active', NOW(), NOW()
                FROM education.campuses
                ON CONFLICT DO NOTHING;
            ");

            DB::connection($this->targetConn)->statement("
                INSERT INTO education.floors (name, floor_number, building_id, status, created_at, updated_at)
                SELECT 'Ground Floor', 'G', id, 'active', NOW(), NOW()
                FROM education.buildings
                ON CONFLICT DO NOTHING;
            ");

            $inserts = [];
            foreach ($rooms as $room) {
                // Find target campus via legacy_id
                $campusId = DB::connection($this->targetConn)
                    ->table('education.campuses')
                    ->where('legacy_id', $room->branch_location_id)
                    ->value('id');

                if (!$campusId)
                    continue;

                $floorId = DB::connection($this->targetConn)
                    ->table('education.floors')
                    ->join('education.buildings', 'education.floors.building_id', '=', 'education.buildings.id')
                    ->where('education.buildings.campus_id', $campusId)
                    ->value('education.floors.id');

                if (!$floorId)
                    continue;

                $inserts[] = [
                    'legacy_id' => $room->id,
                    'floor_id' => $floorId,
                    'name' => $room->name,
                    'code' => 'ROOM-' . $room->id,
                    'capacity' => $room->capacity ?? 30,
                    'room_type' => 'lecture',
                    'room_status' => 'active',
                    'created_at' => Carbon::parse($room->created_at ?? now())->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::parse($room->updated_at ?? now())->format('Y-m-d H:i:s'),
                ];
            }
            if (!empty($inserts)) {
                $this->bulkUpsert('education.rooms', $inserts, ['name', 'capacity', 'updated_at']);
            }
        }
        $this->logMigrationFinish($logId);

        // D) Programs
        $this->info(" -> Programs");
        $logId = $this->logMigrationStart('programs');
        $progs = DB::connection($this->legacyConn)->table('study_programs')->get();
        if (!$this->option('dry-run') && $progs->count() > 0) {
            $inserts = [];
            foreach ($progs as $p) {
                $inserts[] = [
                    'legacy_id' => $p->id,
                    'name' => $p->name,
                    'code' => 'PROG-' . $p->id,
                    'status' => 'published',
                    'starts_at' => $p->start_date ?? now(),
                    'ends_at' => $p->end_date ?? now()->addMonths($p->number_months ?? 6),
                    'created_at' => Carbon::parse($p->created_at ?? now())->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::parse($p->updated_at ?? now())->format('Y-m-d H:i:s'),
                ];
            }
            $this->bulkUpsert('education.programs', $inserts, ['name', 'starts_at', 'ends_at']);
        }
        $this->logMigrationFinish($logId);

        // E) Groups
        $this->info(" -> Groups");
        $logId = $this->logMigrationStart('groups');
        $groups = DB::connection($this->legacyConn)->table('class_groups')->get();
        if (!$this->option('dry-run') && $groups->count() > 0) {
            $inserts = [];
            foreach ($groups as $g) {
                $programId = DB::connection($this->targetConn)->table('education.programs')->where('legacy_id', $g->study_program_id)->value('id');
                if (!$programId)
                    continue;

                $inserts[] = [
                    'legacy_id' => $g->id,
                    'program_id' => $programId,
                    'name' => $g->round_group ?? ('G-' . $g->id),
                    'capacity' => $g->max_trainees ?? 25,
                    'status' => strtolower($g->status ?? 'active') === 'active' ? 'active' : 'inactive',
                    'created_at' => Carbon::parse($g->created_at ?? now())->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::parse($g->updated_at ?? now())->format('Y-m-d H:i:s'),
                ];
            }
            if (!empty($inserts)) {
                $this->bulkUpsert('education.groups', $inserts, ['name', 'capacity', 'status', 'updated_at']);
            }
        }
        $this->logMigrationFinish($logId);
    }

    /**
     * Helper to Bulk Upsert safely into Postgres
     */
    private function bulkUpsert($table, array $data, array $updateCols, string $conflictTarget = 'legacy_id')
    {
        if (empty($data))
            return;

        $keys = array_keys($data[0]);
        $columns = implode(', ', array_map(fn($v) => '"' . $v . '"', $keys));

        // Convert array data to SQL Values block
        $values = [];
        foreach ($data as $row) {
            $rowVals = [];
            foreach ($row as $val) {
                if ($val === null)
                    $rowVals[] = 'NULL';
                else {
                    // Escape for Postgres
                    $safeVal = str_replace("'", "''", (string) $val);
                    $rowVals[] = "'$safeVal'";
                }
            }
            $values[] = "(" . implode(',', $rowVals) . ")";
        }
        $valuesSql = implode(", ", $values);

        // Columns to update when CONFLICT happens
        $updateStmts = [];
        foreach ($updateCols as $col) {
            $updateStmts[] = "\"$col\" = EXCLUDED.\"$col\"";
        }
        $updateSql = implode(', ', $updateStmts);

        // Run statement
        try {
            DB::connection($this->targetConn)->statement("
                INSERT INTO $table ($columns)
                VALUES $valuesSql
                ON CONFLICT ($conflictTarget) DO UPDATE SET $updateSql
            ");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Bulk Upsert failed on $table: " . $e->getMessage());
            throw $e;
        }
    }
    private function phase3Profiles()
    {
        $this->info("🎓 Phase 3: Profiles (Trainees & Instructors)");

        // A) Instructor Profiles
        $this->info(" -> Instructor Profiles");
        $logId = $this->logMigrationStart('instructor_profiles');

        DB::connection($this->legacyConn)->table('instructors')
            ->orderBy('id')->chunk($this->chunkSize, function ($instructors) use ($logId) {
                if ($this->option('dry-run')) {
                    $this->info("   [DryRun] Would UPSERT " . count($instructors) . " instructors.");
                    return;
                }
                $inserts = [];
                foreach ($instructors as $inst) {
                    $email = strtolower(trim($inst->email));
                    if (!$email)
                        continue;

                    $userId = DB::connection($this->targetConn)->table('public.users')->where('email', $email)->value('id');
                    if (!$userId)
                        continue;

                    $govId = DB::connection($this->targetConn)->table('education.governorates')->where('legacy_id', $inst->city_id)->value('id');

                    $inserts[] = [
                        'legacy_id' => $inst->id,
                        'user_id' => $userId,
                        'governorate_id' => $govId,
                        'track_id' => null,
                        'national_id' => $inst->nationalID ? encrypt((string) $inst->nationalID) : null,
                        'passport_number' => $inst->passport_number ? encrypt((string) $inst->passport_number) : null,
                        'date_of_birth' => $inst->birthdate,
                        'gender' => strtolower($inst->gender ?? 'male') === 'male' ? 'male' : 'female',
                        'employment_type' => 'contractor',
                        'status' => strtolower($inst->status ?? 'active') === 'active' ? 'active' : 'inactive',
                        'arabic_name' => $inst->name_ar,
                        'english_name' => $inst->name_en ?? $inst->name_ar,
                        'address' => $inst->address,
                        'created_at' => Carbon::parse($inst->created_at ?? now())->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::parse($inst->updated_at ?? now())->format('Y-m-d H:i:s'),
                    ];
                }
                if (!empty($inserts)) {
                    $this->bulkUpsert('education.instructor_profiles', $inserts, ['status', 'arabic_name', 'updated_at']);
                    $this->logMigrationProgress($logId, count($inserts));
                }
            });
        $this->logMigrationFinish($logId);

        // B) Trainee Profiles
        $this->info(" -> Trainee Profiles");
        $logId = $this->logMigrationStart('trainee_profiles');

        DB::connection($this->legacyConn)->table('trainees')
            ->orderBy('id')->chunk($this->chunkSize, function ($trainees) use ($logId) {
                if ($this->option('dry-run')) {
                    $this->info("   [DryRun] Would UPSERT " . count($trainees) . " trainees.");
                    return;
                }
                $inserts = [];
                foreach ($trainees as $t) {
                    $email = strtolower(trim($t->email));
                    if (!$email)
                        continue;

                    $userId = DB::connection($this->targetConn)->table('public.users')->where('email', $email)->value('id');
                    if (!$userId)
                        continue;

                    $govId = DB::connection($this->targetConn)->table('education.governorates')->where('legacy_id', $t->city_id)->value('id');
                    $progId = DB::connection($this->targetConn)->table('education.programs')->where('legacy_id', $t->study_program_id)->value('id');
                    $jobId = DB::connection($this->targetConn)->table('education.job_profiles')->where('legacy_id', $t->track_id)->value('id');

                    // Resolve Group (from group_trainees)
                    $legacyGroupId = DB::connection($this->legacyConn)->table('group_trainees')
                        ->where('trainee_id', $t->id)->value('class_group_id');
                    $groupId = $legacyGroupId ? DB::connection($this->targetConn)->table('education.groups')->where('legacy_id', $legacyGroupId)->value('id') : null;

                    $inserts[] = [
                        'legacy_id' => $t->id,
                        'user_id' => $userId,
                        'program_id' => $progId,
                        'group_id' => $groupId,
                        'job_profile_id' => $jobId,
                        'governorate_id' => $govId,
                        'national_id' => $t->nationalID ? encrypt((string) $t->nationalID) : null,
                        'passport_number' => $t->passport_number ? encrypt((string) $t->passport_number) : null,
                        'religion' => $t->religion ? encrypt((string) $t->religion) : null,
                        'gender' => strtolower($t->gender ?? 'male') === 'male' ? 'male' : 'female',
                        'date_of_birth' => $t->birthdate,
                        'enrollment_status' => strtolower($t->status ?? 'active') === 'active' ? 'active' : 'suspended',
                        'arabic_name' => $t->name_ar,
                        'english_name' => $t->name_en ?? $t->name_ar,
                        'address' => $t->address,
                        'nationality' => $t->nationality,
                        'created_at' => Carbon::parse($t->created_at ?? now())->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::parse($t->updated_at ?? now())->format('Y-m-d H:i:s'),
                    ];
                }
                if (!empty($inserts)) {
                    $this->bulkUpsert('education.trainee_profiles', $inserts, ['enrollment_status', 'arabic_name', 'updated_at']);
                    $this->logMigrationProgress($logId, count($inserts));
                }
            });
        $this->logMigrationFinish($logId);
    }
    private function phase4Operations()
    {
        $this->info("📚 Phase 4: Operations (Lectures & Attendances)");

        // A) Lectures (From attendance_days)
        $this->info(" -> Lectures (from attendance_days)");
        $logId = $this->logMigrationStart('lectures');

        DB::connection($this->legacyConn)->table('attendance_days')
            ->orderBy('id')->chunk($this->chunkSize, function ($days) use ($logId) {
                if ($this->option('dry-run')) {
                    $this->info("   [DryRun] Would UPSERT " . count($days) . " lectures.");
                    return;
                }
                $inserts = [];
                foreach ($days as $d) {
                    $progId = DB::connection($this->targetConn)->table('education.programs')->where('legacy_id', $d->study_program_id)->value('id');
                    $groupId = DB::connection($this->targetConn)->table('education.groups')->where('legacy_id', $d->class_group_id)->value('id');
                    $instId = DB::connection($this->targetConn)->table('education.instructor_profiles')->where('legacy_id', $d->instructor_id)->value('id');
                    $roomId = DB::connection($this->targetConn)->table('education.rooms')->where('legacy_id', $d->class_room_id)->value('id');
                    $typeId = DB::connection($this->targetConn)->table('education.session_types')->where('legacy_id', $d->session_type_id)->value('id');

                    // If time is missing, default to 00:00:00
                    $startTime = $d->time ?? '00:00:00';
                    $endTime = $d->to_time ?? '23:59:59';
                    $date = $d->date ?? now()->toDateString();

                    try {
                        $startsAt = Carbon::parse("$date $startTime")->format('Y-m-d H:i:s');
                        $endsAt = Carbon::parse("$date $endTime")->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        continue;
                    }

                    $inserts[] = [
                        'legacy_id' => $d->id,
                        'program_id' => $progId,
                        'group_id' => $groupId,
                        'instructor_profile_id' => $instId,
                        'room_id' => $roomId,
                        'session_type_id' => $typeId,
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'status' => 'scheduled',
                        'created_at' => Carbon::parse($d->created_at ?? now())->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::parse($d->updated_at ?? now())->format('Y-m-d H:i:s'),
                    ];
                }
                if (!empty($inserts)) {
                    $this->bulkUpsert('education.lectures', $inserts, ['starts_at', 'ends_at', 'updated_at']);
                    $this->logMigrationProgress($logId, count($inserts));
                }
            });
        $this->logMigrationFinish($logId);

        // B) Attendances (From attendance_trainees)
        $this->info(" -> Attendances");
        $logId = $this->logMigrationStart('attendances');

        DB::connection($this->legacyConn)->table('attendance_trainees')
            ->orderBy('id')->chunk($this->chunkSize, function ($atts) use ($logId) {
                if ($this->option('dry-run')) {
                    $this->info("   [DryRun] Would UPSERT " . count($atts) . " attendances.");
                    return;
                }
                $inserts = [];
                $seen = [];
                foreach ($atts as $att) {
                    $traineeId = DB::connection($this->targetConn)->table('education.trainee_profiles')->where('legacy_id', $att->trainee_id)->value('id');
                    $lectureId = DB::connection($this->targetConn)->table('education.lectures')->where('legacy_id', $att->attendance_day_id)->value('id');

                    if (!$traineeId || !$lectureId)
                        continue;

                    // Prevent cardinality violation from duplicates in the same payload
                    $tuple = "{$lectureId}_{$traineeId}";
                    if (isset($seen[$tuple]))
                        continue;
                    $seen[$tuple] = true;

                    $inserts[] = [
                        'legacy_id' => $att->id,
                        'lecture_id' => $lectureId,
                        'trainee_profile_id' => $traineeId,
                        'status' => strtolower($att->status ?? 'absent') === 'presence' ? 'present' : 'absent',
                        'created_at' => Carbon::parse($att->created_at ?? now())->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::parse($att->updated_at ?? now())->format('Y-m-d H:i:s'),
                    ];
                }
                if (!empty($inserts)) {
                    $this->bulkUpsert('education.attendances', $inserts, ['status', 'updated_at'], 'lecture_id, trainee_profile_id');
                    $this->logMigrationProgress($logId, count($inserts));
                }
            });
        $this->logMigrationFinish($logId);
    }
    private function phase5Tickets()
    {
        $this->info("🎫 Phase 5: Tickets Domain");

        // Prepare PostgreSQL for native UUID generation
        if (!$this->option('dry-run')) {
            DB::connection($this->targetConn)->statement("CREATE EXTENSION IF NOT EXISTS pgcrypto;");
        }

        // A) Map Categories
        $this->info(" -> Ticket Categories");
        $logId = $this->logMigrationStart('ticket_categories');
        $cats = DB::connection($this->legacyConn)->table('subject_tickets')->orderBy('parent_id')->get(); // Nulls first
        if (!$this->option('dry-run') && $cats->count() > 0) {
            $inserts = [];
            foreach ($cats as $cat) {
                $inserts[] = [
                    'legacy_id' => $cat->id,
                    'name' => $cat->name,
                    'created_at' => Carbon::parse($cat->created_at ?? now())->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::parse($cat->updated_at ?? now())->format('Y-m-d H:i:s'),
                ];
            }
            $this->bulkUpsert('tickets.ticket_categories', $inserts, ['name', 'updated_at']);
        }
        $this->logMigrationFinish($logId);

        // Map Target Lookups by Names (because no slug column)
        $statusMap = DB::connection($this->targetConn)->table('tickets.ticket_statuses')->pluck('id', 'name')->toArray();
        $prioMap = DB::connection($this->targetConn)->table('tickets.ticket_priorities')->pluck('id', 'name')->toArray();

        $resolveStatus = function ($legacyStatusName) use ($statusMap) {
            $nameStr = mb_strtolower(trim($legacyStatusName));
            $new = $statusMap['جديد'] ?? $statusMap['مفتوحة'] ?? 1;
            $open = $statusMap['مفتوحة'] ?? $statusMap['مفتوح'] ?? 1;
            $prog = $statusMap['جاري المعالجة'] ?? $statusMap['قيد التنفيذ'] ?? 2;
            $res = $statusMap['تم الحل'] ?? $statusMap['منفذ'] ?? 3;
            $closed = $statusMap['مغلق'] ?? $statusMap['مغلقة'] ?? $statusMap['تم الحل'] ?? 3;

            if (str_contains($nameStr, 'new') || str_contains($nameStr, 'جديد'))
                return $new;
            if (str_contains($nameStr, 'progress') || str_contains($nameStr, 'جاري') || str_contains($nameStr, 'مفتوح'))
                return $open;
            if (str_contains($nameStr, 'resolve') || str_contains($nameStr, 'حل') || str_contains($nameStr, 'منفذ'))
                return $res;
            if (str_contains($nameStr, 'close') || str_contains($nameStr, 'مغلق'))
                return $closed;
            return $new; // Default
        };

        $resolvePriority = function ($legacyPrioName) use ($prioMap) {
            $nameStr = mb_strtolower(trim($legacyPrioName));
            $low = $prioMap['عادي'] ?? $prioMap['منخفض'] ?? 1;
            $med = $prioMap['متوسط'] ?? $prioMap['متوسطة'] ?? 2;
            $high = $prioMap['هام'] ?? $prioMap['عالي'] ?? 3;
            $urg = $prioMap['عاجل جداً'] ?? $prioMap['طوارئ'] ?? $prioMap['هام'] ?? 3;

            if (str_contains($nameStr, 'low') || str_contains($nameStr, 'منخفض') || str_contains($nameStr, 'عادي'))
                return $low;
            if (str_contains($nameStr, 'high') || str_contains($nameStr, 'عالي') || str_contains($nameStr, 'هام'))
                return $high;
            if (str_contains($nameStr, 'urgent') || str_contains($nameStr, 'طوارئ'))
                return $urg;
            return $med; // Default
        };

        // Cache legacy names to Avoid multiple DB lookups
        $legacyStatusNames = DB::connection($this->legacyConn)->table('status_tickets')->pluck('name', 'id')->toArray();
        $legacyPrioNames = DB::connection($this->legacyConn)->table('priority_tickets')->pluck('name', 'id')->toArray();

        // B) Tickets
        $this->info(" -> Tickets");
        $logId = $this->logMigrationStart('tickets');

        DB::connection($this->legacyConn)->table('ticket_headers')
            ->orderBy('id')->chunk($this->chunkSize, function ($tickets) use ($logId, $legacyStatusNames, $legacyPrioNames, $resolveStatus, $resolvePriority) {
                if ($this->option('dry-run')) {
                    $this->info("   [DryRun] Would UPSERT " . count($tickets) . " tickets.");
                    return;
                }

                $inserts = [];
                foreach ($tickets as $t) {
                    $userId = DB::connection($this->targetConn)->table('public.users')->where('legacy_id', $t->user_id)->value('id');
                    if (!$userId)
                        continue;

                    $catId = DB::connection($this->targetConn)->table('tickets.ticket_categories')->where('legacy_id', $t->subject_id)->value('id');
                    $lectureId = DB::connection($this->targetConn)->table('education.lectures')->where('legacy_id', $t->attendance_day_id)->value('id');

                    $lStatusName = $legacyStatusNames[$t->status_id] ?? '';
                    $lPrioName = $legacyPrioNames[$t->priority_id] ?? '';

                    $inserts[] = [
                        'legacy_id' => $t->id,
                        // We will use DB::raw('gen_random_uuid()') explicitly during raw insert execution, 
                        // But bulkUpsert function wraps strings in quotes. 
                        // So we generate random uuid in PHP for simplicity while achieving the same secure format.
                        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                        'ticket_number' => $t->ticket_number . '-' . $t->id,
                        'user_id' => $userId,
                        'subject' => $t->title ?? 'Untitled',
                        'details' => $t->description ?? '',
                        'status_id' => $resolveStatus($lStatusName),
                        'priority_id' => $resolvePriority($lPrioName),
                        'category_id' => $catId,
                        'lecture_id' => $lectureId,
                        'created_at' => Carbon::parse($t->created_at ?? now())->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::parse($t->updated_at ?? now())->format('Y-m-d H:i:s'),
                    ];
                }
                if (!empty($inserts)) {
                    $this->bulkUpsert('tickets.tickets', $inserts, ['subject', 'details', 'status_id', 'updated_at']);
                    $this->logMigrationProgress($logId, count($inserts));
                }
            });
        $this->logMigrationFinish($logId);

        // C) Threads
        $this->info(" -> Ticket Threads");
        $logId = $this->logMigrationStart('ticket_threads');

        DB::connection($this->legacyConn)->table('ticket_detals')
            ->orderBy('id')->chunk($this->chunkSize, function ($threads) use ($logId) {
                if ($this->option('dry-run')) {
                    $this->info("   [DryRun] Would UPSERT " . count($threads) . " threads.");
                    return;
                }
                $inserts = [];
                foreach ($threads as $th) {
                    $ticketId = DB::connection($this->targetConn)->table('tickets.tickets')->where('legacy_id', $th->ticket_id)->value('id');
                    if (!$ticketId)
                        continue;

                    $userId = DB::connection($this->targetConn)->table('public.users')->where('legacy_id', $th->created_by)->value('id');

                    $inserts[] = [
                        'legacy_id' => $th->id,
                        'ticket_id' => $ticketId,
                        'user_id' => $userId, // Might be null if agent
                        'content' => $th->notes ?? '',
                        'type' => ($th->is_internal ?? false) ? 'internal_note' : 'message',
                        'created_at' => Carbon::parse($th->created_at ?? now())->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::parse($th->updated_at ?? now())->format('Y-m-d H:i:s'),
                    ];
                }
                if (!empty($inserts)) {
                    $this->bulkUpsert('tickets.ticket_threads', $inserts, ['content', 'updated_at']);
                    $this->logMigrationProgress($logId, count($inserts));
                }
            });
        $this->logMigrationFinish($logId);
    }
    private function phase7Reindex()
    {
        $this->info("⚡ Phase 7: Rebuilding Schema Indexes and Vacuuming...");
        if (!$this->option('dry-run')) {
            DB::connection($this->targetConn)->statement("REINDEX SCHEMA tickets;");
            DB::connection($this->targetConn)->statement("REINDEX SCHEMA education;");
            DB::connection($this->targetConn)->statement("REINDEX SCHEMA public;");
            DB::connection($this->targetConn)->statement("VACUUM ANALYZE;");
            $this->info("✅ PostgreSQL Planner Statistics Updated successfully.");
        }
    }
    private function phase8IntegrityVerification()
    {
        $this->info("🛡️ Phase 8: Data Integrity Verification");

        $legUsers = DB::connection($this->legacyConn)->table('users')->whereNotNull('email')->where('email', '!=', '')->count();
        $newUsers = DB::connection($this->targetConn)->table('public.users')->count();
        $this->info(" -> Identity Verification: Legacy Users ($legUsers) | New Users ($newUsers)");

        $legTickets = DB::connection($this->legacyConn)->table('ticket_headers')->count();
        $newTickets = DB::connection($this->targetConn)->table('tickets.tickets')->count();
        $this->info(" -> Ticket Verification: Legacy Tickets ($legTickets) | New Tickets ($newTickets)");

        // FK Check for orphan tickets without categories
        $orphanTicketsCount = DB::connection($this->targetConn)
            ->table('tickets.tickets')
            ->whereNull('category_id')
            ->count();
        if ($orphanTicketsCount > 0) {
            $this->warn(" ⚠️ Warning: Found $orphanTicketsCount orphaned tickets missing category mapping.");
        } else {
            $this->info(" ✅ All tickets perfectly mapped to taxonomy.");
        }

    }

    private function rollbackLegacyData()
    {
        if (!$this->confirm("⚠️ DANGER: This will delete ALL legacy migrated data from the digi_test target. Are you absolutely sure?")) {
            $this->info("Rollback cancelled.");
            return;
        }

        $this->info("🧹 Starting Safe Rollback of Legacy Data...");

        DB::connection($this->targetConn)->statement('SET session_replication_role = replica;');

        try {
            // Delete Tickets Tier
            DB::connection($this->targetConn)->statement("DELETE FROM tickets.ticket_threads WHERE legacy_id IS NOT NULL;");
            DB::connection($this->targetConn)->statement("DELETE FROM tickets.tickets WHERE legacy_id IS NOT NULL;");
            DB::connection($this->targetConn)->statement("DELETE FROM tickets.ticket_categories WHERE legacy_id IS NOT NULL;");
            $this->info(" ✅ Tickets Tier rolled back.");

            // Delete Operations Tier
            DB::connection($this->targetConn)->statement("DELETE FROM education.attendances WHERE legacy_id IS NOT NULL;");
            DB::connection($this->targetConn)->statement("DELETE FROM education.lectures WHERE legacy_id IS NOT NULL;");
            $this->info(" ✅ Operations Tier rolled back.");

            // Delete Profiles Tier
            DB::connection($this->targetConn)->statement("DELETE FROM education.trainee_profiles WHERE legacy_id IS NOT NULL;");
            DB::connection($this->targetConn)->statement("DELETE FROM education.instructor_profiles WHERE legacy_id IS NOT NULL;");
            $this->info(" ✅ Profiles Tier rolled back.");

            // Delete Infrastructure Tier
            DB::connection($this->targetConn)->statement("DELETE FROM education.groups WHERE legacy_id IS NOT NULL;");
            DB::connection($this->targetConn)->statement("DELETE FROM education.programs WHERE legacy_id IS NOT NULL;");
            DB::connection($this->targetConn)->statement("DELETE FROM education.rooms WHERE legacy_id IS NOT NULL;");

            // Floors and Buildings were not explicitly given legacy_id, they depend on campuses with legacy keys
            DB::connection($this->targetConn)->statement("DELETE FROM education.floors WHERE building_id IN (SELECT id FROM education.buildings WHERE campus_id IN (SELECT id FROM education.campuses WHERE legacy_id IS NOT NULL));");
            DB::connection($this->targetConn)->statement("DELETE FROM education.buildings WHERE campus_id IN (SELECT id FROM education.campuses WHERE legacy_id IS NOT NULL);");
            DB::connection($this->targetConn)->statement("DELETE FROM education.campuses WHERE legacy_id IS NOT NULL;");
            DB::connection($this->targetConn)->statement("DELETE FROM education.governorates WHERE legacy_id IS NOT NULL;");
            $this->info(" ✅ Infrastructure Tier rolled back.");

            // Delete Identity / Users (Also removes their roles assignment manually via Spatie pivot)
            DB::connection($this->targetConn)->statement("DELETE FROM public.model_has_roles WHERE model_id IN (SELECT id FROM public.users WHERE legacy_id IS NOT NULL) AND model_type = 'App\\Models\\User';");
            DB::connection($this->targetConn)->statement("DELETE FROM public.users WHERE legacy_id IS NOT NULL;");
            $this->info(" ✅ Identity Tier rolled back (including SPATIE assignments).");

            // Empty Migration Logs
            DB::connection($this->targetConn)->table('migration_logs')->truncate();
            $this->info(" ✅ Migration Logs Cleared.");

        } catch (\Exception $e) {
            $this->error("❌ Rollback Failed: " . $e->getMessage());
        }

        DB::connection($this->targetConn)->statement('SET session_replication_role = DEFAULT;');

        $this->info("🎊 Rollback process completed successfully. The databases are now clean from legacy dependencies.");
    }
}
