<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.campus_program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('education.campuses')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('education.programs')->cascadeOnDelete();

            $table->unique(['campus_id', 'program_id'], 'education_campus_program_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.campus_program');
    }
};
