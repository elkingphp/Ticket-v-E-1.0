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
                            {--resume= : Resume from a specific phase}';

    protected $description = 'Enterprise Data Migration from ts_digi to digi_test';

    private $legacyConn = 'legacy';
    private $targetConn = 'pgsql';
    private $chunkSize = 2000;

    public function handle()
    {
        ini_set('memory_limit', '2048M');

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
                foreach ($users as $u) {
                    $email = strtolower(trim($u->email));
                    if (empty($email))
                        continue;

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
                    $updateFields = ['first_name', 'last_name', 'password', 'phone'];
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
                        $this->warn("User Batch Error: " . $e->getMessage());
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
    private function bulkUpsert($table, array $data, array $updateCols)
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
                    $safeVal = str_replace("'", "''", $val);
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
        DB::connection($this->targetConn)->statement("
            INSERT INTO $table ($columns)
            VALUES $valuesSql
            ON CONFLICT (legacy_id) DO UPDATE SET $updateSql
        ");
    }
    private function phase3Profiles()
    {
    }
    private function phase4Operations()
    {
    }
    private function phase5Tickets()
    {
    }
    private function phase7Reindex()
    {
        $this->info("⚡ Phase 7: Rebuilding Indexes and Vacuuming...");
        if (!$this->option('dry-run')) {
            DB::connection($this->targetConn)->statement("VACUUM ANALYZE public.users;");
            DB::connection($this->targetConn)->statement("VACUUM ANALYZE education.governorates;");
        }
    }
    private function phase8IntegrityVerification()
    {
    }
}
