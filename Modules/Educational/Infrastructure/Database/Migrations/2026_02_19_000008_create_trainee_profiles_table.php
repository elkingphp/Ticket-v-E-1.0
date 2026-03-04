<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.trainee_profiles', function (Blueprint $table) {
            $table->id();

            // FK cross schema to public.users
            $table->foreignId('user_id')->unique()->constrained('public.users')->cascadeOnDelete();

            // Sensitive Encrypted Fields
            $table->text('national_id')->nullable();
            $table->text('religion')->nullable();
            $table->text('medical_notes')->nullable();

            $table->enum('enrollment_status', ['active', 'on_leave', 'graduated', 'withdrawn', 'suspended'])->default('active');

            $table->timestamps();

            $table->index('enrollment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.trainee_profiles');
    }
};
