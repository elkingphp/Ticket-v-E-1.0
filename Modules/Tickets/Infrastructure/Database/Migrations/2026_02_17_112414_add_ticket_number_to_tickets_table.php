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
        Schema::table('tickets.tickets', function (Blueprint $table) {
            $table->string('ticket_number')->nullable()->unique()->after('uuid');
        });

        // Set default format if not exists in seeder or elsewhere
        // But we'll handle it in the service/settings
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets.tickets', function (Blueprint $table) {
            $table->dropColumn('ticket_number');
        });
    }
};
