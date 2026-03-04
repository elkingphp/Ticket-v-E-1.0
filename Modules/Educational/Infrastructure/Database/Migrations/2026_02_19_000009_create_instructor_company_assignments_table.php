<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.instructor_company_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('instructor_profile_id')->constrained('education.instructor_profiles')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('education.training_companies')->cascadeOnDelete();
            $table->foreignId('job_profile_id')->constrained('education.job_profiles')->cascadeOnDelete();

            $table->timestamp('assigned_at')->useCurrent();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            // The crucial composite unique constraint for ternary relationship
            $table->unique(
                ['instructor_profile_id', 'company_id', 'job_profile_id'],
                'education_instructor_comp_job_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.instructor_company_assignments');
    }
};
