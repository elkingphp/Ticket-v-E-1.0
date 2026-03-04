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
            'education.session_types',
            'education.schedule_templates',
            'education.lectures',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (!Schema::hasColumn($table, 'legacy_id')) {
                    $blueprint->unsignedBigInteger('legacy_id')->nullable()->index();
                }
            });
        }

        // Optional: Relax instructor_profile_id if needed, but let's try to keep it strict first.
        // Schema::table('education.schedule_templates', function (Blueprint $blueprint) {
        //     $blueprint->unsignedBigInteger('instructor_profile_id')->nullable()->change();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'education.session_types',
            'education.schedule_templates',
            'education.lectures',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('legacy_id');
            });
        }
    }
};
