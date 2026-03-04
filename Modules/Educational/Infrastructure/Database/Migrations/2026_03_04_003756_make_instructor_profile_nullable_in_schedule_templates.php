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
        Schema::table('education.schedule_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('instructor_profile_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education.schedule_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('instructor_profile_id')->nullable(false)->change();
        });
    }
};
