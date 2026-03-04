<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase A · Hardening Step 3
 * ─────────────────────────────────────────────────────────
 * Enhances lecture_evaluations with:
 *
 *  • form_snapshot     JSON   — full copy of form structure (questions + options)
 *                               captured at the moment of FIRST submission.
 *                               Dashboard always reads from snapshot, never from
 *                               the live form (which may change or be archived).
 *
 *  • snapshot_hash     CHAR   — SHA-256 of json_encode(form_snapshot).
 *                               Allows future audit to detect tampering.
 *
 *  • submitted_at      TS     — OFFICIAL submission time.
 *                               Never rely on created_at for business logic.
 *
 *  • evaluator_role    ENUM   — Tracks the ROLE of the evaluator regardless of
 *                               their Model type (trainee | observer | admin).
 *                               Enables split dashboard comparison.
 *
 * NOTE: The UNIQUE constraint edu_evals_unique already exists in the original
 * migration — we intentionally do NOT re-add it here.
 */
return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::table('education.lecture_evaluations', function (Blueprint $table) {
            // Snapshot of the form at the time of submission
            $table->jsonb('form_snapshot')->nullable()->after('evaluator_id');

            // SHA-256 of json_encode(form_snapshot) — for audit integrity checks
            $table->char('snapshot_hash', 64)->nullable()->after('form_snapshot');

            // The role the evaluator is playing — separate from their Model type
            $table->enum('evaluator_role', ['trainee', 'observer', 'admin'])
                ->default('trainee')
                ->after('snapshot_hash');

            // Official submission timestamp — authoritative for all business queries
            $table->timestamp('submitted_at')->nullable()->after('overall_comments');

            // Performance: index evaluator_role for dashboard split queries
            $table->index('evaluator_role', 'edu_evals_evaluator_role_idx');
            $table->index('submitted_at', 'edu_evals_submitted_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('education.lecture_evaluations', function (Blueprint $table) {
            $table->dropIndex('edu_evals_evaluator_role_idx');
            $table->dropIndex('edu_evals_submitted_at_idx');
            $table->dropColumn(['form_snapshot', 'snapshot_hash', 'evaluator_role', 'submitted_at']);
        });
    }
};
