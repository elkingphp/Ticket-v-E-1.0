<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.lecture_evaluations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lecture_id')->constrained('education.lectures')->cascadeOnDelete();
            $table->foreignId('form_id')->constrained('education.evaluation_forms')->cascadeOnDelete();

            // Polymorphic relation to find who submitted this evaluation (e.g. TraineeProfile, User acting as Observer)
            $table->string('evaluator_type');
            $table->unsignedBigInteger('evaluator_id');

            $table->text('overall_comments')->nullable();

            $table->timestamps();

            // One evaluation per entity per lecture
            $table->unique(['lecture_id', 'form_id', 'evaluator_type', 'evaluator_id'], 'edu_evals_unique');
            $table->index(['evaluator_type', 'evaluator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.lecture_evaluations');
    }
};
