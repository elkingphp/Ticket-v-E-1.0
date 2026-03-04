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
        Schema::table('education.attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
        });
        Schema::table('education.lecture_evaluations', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
        });
        Schema::table('education.evaluation_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education.attendances', function (Blueprint $table) {
            $table->dropColumn('legacy_id');
        });
        Schema::table('education.lecture_evaluations', function (Blueprint $table) {
            $table->dropColumn('legacy_id');
        });
        Schema::table('education.evaluation_answers', function (Blueprint $table) {
            $table->dropColumn('legacy_id');
        });
    }
};
