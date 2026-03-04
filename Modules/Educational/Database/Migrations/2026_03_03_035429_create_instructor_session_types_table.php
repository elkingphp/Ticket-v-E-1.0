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
        Schema::create('education.instructor_session_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_profile_id')->constrained('education.instructor_profiles')->cascadeOnDelete();
            $table->foreignId('session_type_id')->constrained('education.session_types')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['instructor_profile_id', 'session_type_id'], 'inst_sess_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education.instructor_session_types');
    }
};
