<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.evaluation_forms', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->enum('type', ['lecture_feedback', 'course_evaluation', 'instructor_evaluation', 'general'])->default('lecture_feedback');
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.evaluation_forms');
    }
};
