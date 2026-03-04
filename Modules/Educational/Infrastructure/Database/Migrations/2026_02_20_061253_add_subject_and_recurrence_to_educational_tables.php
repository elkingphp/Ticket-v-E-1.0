<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('education.schedule_templates', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('group_id');
            $table->string('recurrence_type')->default('weekly')->after('room_id'); // weekly, biweekly_even, biweekly_odd
        });

        Schema::table('education.lectures', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('group_id');
            $table->string('recurrence_type')->nullable()->after('room_id');
        });
    }

    public function down(): void
    {
        Schema::table('education.schedule_templates', function (Blueprint $table) {
            $table->dropColumn(['subject', 'recurrence_type']);
        });

        Schema::table('education.lectures', function (Blueprint $table) {
            $table->dropColumn(['subject', 'recurrence_type']);
        });
    }
};
