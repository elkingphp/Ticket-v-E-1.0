<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.schedule_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('program_id')->constrained('education.programs')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('education.groups')->cascadeOnDelete();
            $table->foreignId('instructor_profile_id')->constrained('education.instructor_profiles')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('education.rooms')->cascadeOnDelete();

            $table->integer('day_of_week'); // 0 = Sunday, 1 = Monday, etc.

            $table->time('start_time');
            $table->time('end_time');

            $table->date('effective_from');
            $table->date('effective_until')->nullable();

            $table->timestamps();

            // To ensure rapid lookup during generator sweeps
            $table->index(['program_id', 'day_of_week']);
            $table->index(['effective_from', 'effective_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.schedule_templates');
    }
};
