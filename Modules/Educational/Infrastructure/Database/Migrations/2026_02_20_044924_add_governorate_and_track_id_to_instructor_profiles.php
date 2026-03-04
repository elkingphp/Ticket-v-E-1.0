<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('education.instructor_profiles', function (Blueprint $table) {
            $table->dropColumn('governorate');
            $table->foreignId('governorate_id')->nullable()->constrained('education.governorates')->nullOnDelete();
            $table->foreignId('track_id')->nullable()->constrained('education.tracks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education.instructor_profiles', function (Blueprint $table) {
            $table->string('governorate')->nullable();
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['track_id']);
            $table->dropColumn(['governorate_id', 'track_id']);
        });
    }
};
