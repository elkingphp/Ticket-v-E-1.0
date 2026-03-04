<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase A · Hardening Step 2
 * ─────────────────────────────────────────────────────────
 * Adds soft-delete to evaluation_questions.
 * Questions with existing answers must NEVER be physically removed
 * — soft-delete guarantees answer history remains coherent.
 */
return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::table('education.evaluation_questions', function (Blueprint $table) {
            $table->softDeletes()->after('is_required');
        });
    }

    public function down(): void
    {
        Schema::table('education.evaluation_questions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
