<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        // 1. Update Schedule Templates
        Schema::table('education.schedule_templates', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
        });

        // 2. Update Lectures
        Schema::table('education.lectures', function (Blueprint $table) {
            $table->softDeletes();
        });

        // 3. Update Exclusion Constraints in Lectures to account for Soft Deletes
        // We must drop existing ones and re-create with 'deleted_at IS NULL'

        // A. Room
        DB::statement('ALTER TABLE education.lectures DROP CONSTRAINT no_room_double_booking;');
        DB::statement('
            ALTER TABLE education.lectures 
            ADD CONSTRAINT no_room_double_booking EXCLUDE USING gist (
                room_id WITH =, 
                tsrange(starts_at, ends_at) WITH &&
            ) WHERE (status != \'cancelled\' AND deleted_at IS NULL);
        ');

        // B. Instructor
        DB::statement('ALTER TABLE education.lectures DROP CONSTRAINT no_instructor_double_booking;');
        DB::statement('
            ALTER TABLE education.lectures 
            ADD CONSTRAINT no_instructor_double_booking EXCLUDE USING gist (
                instructor_profile_id WITH =, 
                tsrange(starts_at, ends_at) WITH &&
            ) WHERE (status != \'cancelled\' AND deleted_at IS NULL);
        ');

        // C. Group
        DB::statement('ALTER TABLE education.lectures DROP CONSTRAINT no_group_double_booking;');
        DB::statement('
            ALTER TABLE education.lectures 
            ADD CONSTRAINT no_group_double_booking EXCLUDE USING gist (
                group_id WITH =, 
                tsrange(starts_at, ends_at) WITH &&
            ) WHERE (status != \'cancelled\' AND deleted_at IS NULL);
        ');
    }

    public function down(): void
    {
        Schema::table('education.schedule_templates', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropSoftDeletes();
        });

        Schema::table('education.lectures', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Reverting constraints to previous state (without deleted_at check)
        DB::statement('ALTER TABLE education.lectures DROP CONSTRAINT no_room_double_booking;');
        DB::statement('
            ALTER TABLE education.lectures 
            ADD CONSTRAINT no_room_double_booking EXCLUDE USING gist (
                room_id WITH =, 
                tsrange(starts_at, ends_at) WITH &&
            ) WHERE (status != \'cancelled\');
        ');

        DB::statement('ALTER TABLE education.lectures DROP CONSTRAINT no_instructor_double_booking;');
        DB::statement('
            ALTER TABLE education.lectures 
            ADD CONSTRAINT no_instructor_double_booking EXCLUDE USING gist (
                instructor_profile_id WITH =, 
                tsrange(starts_at, ends_at) WITH &&
            ) WHERE (status != \'cancelled\');
        ');

        DB::statement('ALTER TABLE education.lectures DROP CONSTRAINT no_group_double_booking;');
        DB::statement('
            ALTER TABLE education.lectures 
            ADD CONSTRAINT no_group_double_booking EXCLUDE USING gist (
                group_id WITH =, 
                tsrange(starts_at, ends_at) WITH &&
            ) WHERE (status != \'cancelled\');
        ');
    }
};
