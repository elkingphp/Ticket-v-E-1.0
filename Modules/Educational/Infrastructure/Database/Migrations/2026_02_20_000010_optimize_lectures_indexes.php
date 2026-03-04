<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        // Add partial indexes to optimize dashboard and engine lookups
        // We only care about lightning-fast results for lectures that are NOT historical/cancelled

        DB::statement('
            CREATE INDEX idx_active_lectures_starts_at 
            ON education.lectures (starts_at) 
            WHERE status IN (\'scheduled\', \'running\');
        ');

        DB::statement('
            CREATE INDEX idx_active_lectures_room 
            ON education.lectures (room_id, starts_at) 
            WHERE status IN (\'scheduled\', \'running\');
        ');

        DB::statement('
            CREATE INDEX idx_active_lectures_instructor 
            ON education.lectures (instructor_profile_id, starts_at) 
            WHERE status IN (\'scheduled\', \'running\');
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_active_lectures_starts_at;');
        DB::statement('DROP INDEX IF EXISTS idx_active_lectures_room;');
        DB::statement('DROP INDEX IF EXISTS idx_active_lectures_instructor;');
    }
};
