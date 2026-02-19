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
        Schema::table('tickets.ticket_statuses', function (Blueprint $table) {
            $table->boolean('is_final')->default(false)->after('is_default');
        });

        Schema::table('tickets.ticket_priorities', function (Blueprint $table) {
            $table->decimal('sla_multiplier', 5, 2)->default(1.00)->after('is_default');
        });

        Schema::table('tickets.tickets', function (Blueprint $table) {
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('auto_close_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets.tickets', function (Blueprint $table) {
            $table->dropForeign(['locked_by']);
            $table->dropColumn(['locked_by', 'locked_at', 'auto_close_at']);
        });

        Schema::table('tickets.ticket_priorities', function (Blueprint $table) {
            $table->dropColumn('sla_multiplier');
        });

        Schema::table('tickets.ticket_statuses', function (Blueprint $table) {
            $table->dropColumn('is_final');
        });
    }
};
