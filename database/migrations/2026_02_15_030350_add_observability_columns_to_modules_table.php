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
        Schema::table('modules', function (Blueprint $table) {
            $table->unsignedBigInteger('total_requests')->default(0);
            $table->unsignedBigInteger('total_latency_ms')->default(0);
            $table->unsignedBigInteger('uptime_seconds')->default(0);
            $table->timestamp('last_status_change_at')->useCurrent();
            $table->unsignedInteger('degradation_count')->default(0);
            $table->decimal('sla_target', 5, 2)->default(99.90);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn([
                'total_requests',
                'total_latency_ms',
                'uptime_seconds',
                'last_status_change_at',
                'degradation_count',
                'sla_target'
            ]);
        });
    }
};