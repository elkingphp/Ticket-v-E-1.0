<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('education.lecture_form_assignments', function (Blueprint $table) {
            $table->timestamp('red_flag_alert_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('education.lecture_form_assignments', function (Blueprint $table) {
            $table->dropColumn('red_flag_alert_sent_at');
        });
    }
};
