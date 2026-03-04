<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.company_job_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained('education.training_companies')->cascadeOnDelete();
            $table->foreignId('job_profile_id')->constrained('education.job_profiles')->cascadeOnDelete();

            $table->timestamps();

            // Composite unique constraint
            $table->unique(['company_id', 'job_profile_id'], 'education_comp_job_prof_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.company_job_profiles');
    }
};
