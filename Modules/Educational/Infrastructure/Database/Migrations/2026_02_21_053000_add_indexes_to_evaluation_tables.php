<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('education.lecture_form_assignments', function (Blueprint $table) {
            $table->index(['lecture_id', 'is_active']);
            $table->index(['form_id', 'is_active']);
        });

        Schema::table('education.lecture_evaluations', function (Blueprint $table) {
            $table->index(['lecture_id', 'form_id']);
            $table->index(['evaluator_type', 'evaluator_id'], 'evaluator_poly_index');
            $table->index('submitted_at');
        });

        Schema::table('education.evaluation_answers', function (Blueprint $table) {
            $table->index('lecture_evaluation_id');
            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::table('education.lecture_form_assignments', function (Blueprint $table) {
            $table->dropIndex(['lecture_id', 'is_active']);
            $table->dropIndex(['form_id', 'is_active']);
        });

        Schema::table('education.lecture_evaluations', function (Blueprint $table) {
            $table->dropIndex(['lecture_id', 'form_id']);
            $table->dropIndex('evaluator_poly_index');
            $table->dropIndex(['submitted_at']);
        });

        Schema::table('education.evaluation_answers', function (Blueprint $table) {
            $table->dropIndex(['lecture_evaluation_id']);
            $table->dropIndex(['question_id']);
        });
    }
};
