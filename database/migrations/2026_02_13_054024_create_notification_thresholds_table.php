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
        Schema::create('notification_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->unique(); // audit_critical, failed_login, etc.
            $table->integer('max_count')->default(5); // Maximum occurrences
            $table->integer('time_window')->default(3600); // Time window in seconds
            $table->enum('severity', ['info', 'warning', 'critical'])->default('warning');
            $table->boolean('enabled')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_thresholds');
    }
};