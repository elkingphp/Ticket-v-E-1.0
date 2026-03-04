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
            'education.training_companies',
            'education.tracks',
            'education.job_profiles',
            'education.instructor_profiles',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (!Schema::hasColumn($table, 'legacy_id')) {
                    $blueprint->unsignedBigInteger('legacy_id')->nullable()->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'education.training_companies',
            'education.tracks',
            'education.job_profiles',
            'education.instructor_profiles',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('legacy_id');
            });
        }
    }
};
