<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.instructor_profiles', function (Blueprint $table) {
            $table->id();

            // FK cross schema to public.users
            $table->foreignId('user_id')->unique()->constrained('public.users')->cascadeOnDelete();

            $table->text('bio')->nullable();
            $table->text('specialization_notes')->nullable();

            $table->enum('employment_type', ['full_time', 'part_time', 'contractor'])->default('contractor');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.instructor_profiles');
    }
};
