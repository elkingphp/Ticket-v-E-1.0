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
        // 1. Add external_name to ticket_stages
        Schema::table('tickets.ticket_stages', function (Blueprint $table) {
            $table->string('external_name')->nullable()->after('name')->comment('Name to show to students/external users');
        });

        // 2. ticket_category_role pivot
        Schema::create('tickets.ticket_category_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_category_id')->constrained('tickets.ticket_categories')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ticket_category_id', 'role_id']);
        });

        // 3. ticket_stage_role pivot
        Schema::create('tickets.ticket_stage_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_stage_id')->constrained('tickets.ticket_stages')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ticket_stage_id', 'role_id']);
        });

        // 4. ticket_complaint_role pivot
        Schema::create('tickets.ticket_complaint_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_complaint_id')->constrained('tickets.ticket_complaints')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ticket_complaint_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets.ticket_complaint_role');
        Schema::dropIfExists('tickets.ticket_stage_role');
        Schema::dropIfExists('tickets.ticket_category_role');

        Schema::table('tickets.ticket_stages', function (Blueprint $table) {
            $table->dropColumn('external_name');
        });
    }
};
