<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.evaluation_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('form_id')->constrained('education.evaluation_forms')->cascadeOnDelete();

            $table->string('question_text');
            $table->enum('type', ['rating_1_to_5', 'text', 'boolean', 'multiple_choice'])->default('rating_1_to_5');

            $table->json('options')->nullable(); // For multiple choice choices

            $table->integer('order_index')->default(0);
            $table->boolean('is_required')->default(true);

            $table->timestamps();

            $table->index(['form_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.evaluation_questions');
    }
};
