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
        // 1. Add parent_id to lectures for delay/reschedule tracing
        Schema::table('education.lectures', function (Blueprint $table) {
            if (!Schema::hasColumn('education.lectures', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->foreign('parent_id')->references('id')->on('education.lectures')->onDelete('SET NULL');
            }
        });

        // 2. Add legacy_id to other tables
        $tables = [
            'education.evaluation_forms',
            'education.evaluation_questions',
            'education.lecture_form_assignments',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'legacy_id')) {
                    $table->unsignedBigInteger('legacy_id')->nullable()->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education.lectures', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });

        $tables = [
            'education.evaluation_forms',
            'education.evaluation_questions',
            'education.lecture_form_assignments',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('legacy_id');
            });
        }
    }
};
