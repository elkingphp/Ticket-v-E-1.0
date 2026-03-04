<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::table('education.attendances', function (Blueprint $table) {
            $table->time('check_in_time')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('education.attendances', function (Blueprint $table) {
            $table->dropColumn('check_in_time');
        });
    }
};
