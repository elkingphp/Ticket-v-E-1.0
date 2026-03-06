<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'public' => ['users'],
            'education' => [
                'governorates',
                'campuses',
                'buildings',
                'floors',
                'rooms',
                'programs',
                'groups',
                'instructor_profiles',
                'trainee_profiles',
                'session_types',
                'tracks',
                'job_profiles',
                'training_companies',
                'lectures',
                'attendances'
            ],
            'tickets' => [
                'ticket_categories',
                'tickets',
                'ticket_threads'
            ]
        ];

        foreach ($tables as $schema => $schemaTables) {
            foreach ($schemaTables as $table) {
                try {
                    $indexName = "{$schema}_{$table}_legacy_id_unique";
                    DB::connection('pgsql')->statement("
                        CREATE UNIQUE INDEX IF NOT EXISTS {$indexName} 
                        ON {$schema}.{$table} (legacy_id);
                    ");
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Could not create unique legacy_id index for {$schema}.{$table}: " . $e->getMessage());
                }
            }
        }
    }

    public function down(): void
    {
        // 
    }
};
