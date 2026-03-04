<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.evaluation_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lecture_evaluation_id')->constrained('education.lecture_evaluations')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('education.evaluation_questions')->cascadeOnDelete();

            $table->text('answer_value')->nullable(); // For text or boolean ('true'/'false') or multiple choice option
            $table->integer('answer_rating')->nullable(); // For 1-to-5 ratings

            $table->timestamps();

            // One answer per question per evaluation instance
            $table->unique(['lecture_evaluation_id', 'question_id'], 'edu_eval_answer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.evaluation_answers');
    }
};
