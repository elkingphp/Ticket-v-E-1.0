<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        // 1. Create Tracks table
        Schema::create('education.tracks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        // 2. Refine Job Profiles
        Schema::table('education.job_profiles', function (Blueprint $table) {
            $table->foreignId('track_id')->nullable()->constrained('education.tracks')->onDelete('restrict');
            $table->softDeletes();
        });

        // 3. Expand Training Companies
        Schema::table('education.training_companies', function (Blueprint $table) {
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('logo_disk')->default('public');
        });

        // 4. Ensure composite unique on many-to-many intermediate (if not already strictly enforced)
        // Note: 2026_02_19_000006 already has a unique constraint, but we verify or refine if needed.
    }

    public function down(): void
    {
        Schema::table('education.training_companies', function (Blueprint $table) {
            $table->dropColumn(['website', 'address', 'logo_path', 'logo_disk']);
        });

        Schema::table('education.job_profiles', function (Blueprint $table) {
            $table->dropForeign(['track_id']);
            $table->dropColumn(['track_id', 'deleted_at']);
        });

        Schema::dropIfExists('education.tracks');
    }
};
