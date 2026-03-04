<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase A · Hardening Step 4  (Phase C DB pre-requisite)
 * ─────────────────────────────────────────────────────────
 * Creates lecture_form_assignments — the bridge between a lecture
 * and the evaluation form assigned to it.
 *
 * Created NOW (Phase A) so the DB schema is coherent from the start,
 * even though the UI for managing assignments is built in Phase C.
 *
 * Key design decisions:
 *  • allow_evaluator_types JSON   — controls WHO can submit (trainee/observer).
 *                                   Policy is the authoritative guard; this is
 *                                   configuration only.
 *  • is_active BOOLEAN             — allows deactivating without deleting.
 *  • UNIQUE (lecture_id, form_id)  — one form per lecture, enforced at DB level.
 *
 * completion_rate will be computed as:
 *   submitted / eligible_evaluators
 * where eligible_evaluators = actual registered trainees (from group enrollments)
 * + observers defined in allow_evaluator_types — NOT simply all lecture attendees.
 */
return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.lecture_form_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lecture_id')
                ->constrained('education.lectures')
                ->cascadeOnDelete();

            $table->foreignId('form_id')
                ->constrained('education.evaluation_forms')
                ->cascadeOnDelete();

            $table->foreignId('assigned_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('assigned_at')->useCurrent();

            // JSON array of allowed evaluator roles: ["trainee"], ["observer"], or ["trainee","observer"]
            // Policy is the AUTHORITATIVE guard — this is only a configuration hint.
            $table->jsonb('allow_evaluator_types')->default('["trainee"]');

            // Allows temporarily disabling an assignment without deleting it
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // One form per lecture — enforced at DB level
            $table->unique(['lecture_id', 'form_id'], 'edu_lfa_lecture_form_unique');

            // Performance indexes
            $table->index('lecture_id', 'edu_lfa_lecture_idx');
            $table->index('form_id', 'edu_lfa_form_idx');
            $table->index('is_active', 'edu_lfa_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.lecture_form_assignments');
    }
};
