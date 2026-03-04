<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::table('education.groups', function (Blueprint $table) {
            $table->string('term')->nullable();
            $table->foreignId('job_profile_id')->nullable()->constrained('education.job_profiles');
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('transferred_to_group_id')->nullable()->constrained('education.groups');
        });

        Schema::table('education.groups', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('education.groups', function (Blueprint $table) {
            $table->string('status', 50)->default('active');
        });
    }

    public function down(): void
    {
        Schema::table('education.groups', function (Blueprint $table) {
            $table->dropColumn(['term', 'job_profile_id', 'cancellation_reason', 'transferred_to_group_id']);
        });

        Schema::table('education.groups', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('education.groups', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active');
        });
    }
};
