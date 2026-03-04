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
        $tables = [
            'tickets.ticket_categories',
            'tickets.ticket_complaints',
            'tickets.ticket_sub_complaints',
            'tickets.tickets',
            'tickets.ticket_threads',
            'tickets.ticket_statuses',
            'tickets.ticket_priorities',
            'tickets.ticket_activities',
            'tickets.ticket_audit_logs',
            'tickets.ticket_stages'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                // To avoid errors if already exists, check if column exists 
                if (!Schema::hasColumn($table->getTable(), 'legacy_id')) {
                    $table->unsignedBigInteger('legacy_id')->nullable()->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'tickets.ticket_categories',
            'tickets.ticket_complaints',
            'tickets.ticket_sub_complaints',
            'tickets.tickets',
            'tickets.ticket_threads',
            'tickets.ticket_statuses',
            'tickets.ticket_priorities',
            'tickets.ticket_activities',
            'tickets.ticket_audit_logs',
            'tickets.ticket_stages'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'legacy_id')) {
                    $table->dropColumn('legacy_id');
                }
            });
        }
    }
};
