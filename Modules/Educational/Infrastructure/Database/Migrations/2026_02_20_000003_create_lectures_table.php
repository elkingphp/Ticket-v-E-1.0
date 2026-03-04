<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        // 1. Ensure Postgres extension for GIST indexes mixed with BTREE (equality checks) exists
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist;');

        // 2. Create the lectures table
        Schema::create('education.lectures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('program_id')->constrained('education.programs')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('education.groups')->cascadeOnDelete();

            // Allow null momentarily if an instructor is ill or room is changed temporarily
            $table->foreignId('instructor_profile_id')->nullable()->constrained('education.instructor_profiles')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('education.rooms')->nullOnDelete();

            $table->timestamp('starts_at');
            $table->timestamp('ends_at');

            $table->enum('status', ['scheduled', 'running', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');

            $table->integer('version')->default(1); // Optimistic Locking

            $table->timestamps();

            // Index strategies for lightning-fast queries
            $table->index(['room_id', 'starts_at']);
            $table->index(['instructor_profile_id', 'starts_at']);
            $table->index(['group_id', 'starts_at']);
            $table->index('status');
        });

        // 3. APPLY EXCLUSION CONSTRAINTS (The Database-Level Force Field against Race Conditions)
        // We use 'tsrange(starts_at, ends_at)' because Laravel's timestamp is 'timestamp without time zone' by default. If using timestampTz, use tstzrange.

        // A. Prevent double booking a Room
        DB::statement('
            ALTER TABLE education.lectures 
            ADD CONSTRAINT no_room_double_booking EXCLUDE USING gist (
                room_id WITH =, 
                tsrange(starts_at, ends_at) WITH &&
            ) WHERE (status != \'cancelled\');
        ');

        // B. Prevent double booking an Instructor
        DB::statement('
            ALTER TABLE education.lectures 
            ADD CONSTRAINT no_instructor_double_booking EXCLUDE USING gist (
                instructor_profile_id WITH =, 
                tsrange(starts_at, ends_at) WITH &&
            ) WHERE (status != \'cancelled\');
        ');

        // C. Prevent double booking a Group (They cannot be in two lectures at once)
        DB::statement('
            ALTER TABLE education.lectures 
            ADD CONSTRAINT no_group_double_booking EXCLUDE USING gist (
                group_id WITH =, 
                tsrange(starts_at, ends_at) WITH &&
            ) WHERE (status != \'cancelled\');
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('education.lectures');
        // Extension btree_gist is intentionally not dropped because other DB objects might rely on it.
    }
};
