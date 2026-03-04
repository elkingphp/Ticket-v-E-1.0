<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add code to tracks
        Schema::table('education.tracks', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
        });

        // Track Responsibles Pivot
        Schema::create('education.track_responsibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained('education.tracks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Job Profile Responsibles Pivot
        Schema::create('education.job_profile_responsibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_profile_id')->constrained('education.job_profiles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education.job_profile_responsibles');
        Schema::dropIfExists('education.track_responsibles');

        Schema::table('education.tracks', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
