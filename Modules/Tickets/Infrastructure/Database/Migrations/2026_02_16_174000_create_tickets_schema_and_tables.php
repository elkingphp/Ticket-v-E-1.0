<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Schema
        DB::statement('CREATE SCHEMA IF NOT EXISTS tickets');

        // 2. Lookup Tables
        Schema::create('tickets.ticket_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('sla_hours')->nullable();
            $table->timestamps();
        });

        Schema::create('tickets.ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('stage_id')->nullable()->constrained('tickets.ticket_stages')->nullOnDelete();
            $table->integer('sla_hours')->nullable();
            $table->timestamps();
        });

        Schema::create('tickets.ticket_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained('tickets.ticket_categories')->cascadeOnDelete();
            $table->integer('sla_hours')->nullable();
            $table->timestamps();
        });

        Schema::create('tickets.ticket_sub_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('complaint_id')->constrained('tickets.ticket_complaints')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('tickets.ticket_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('secondary');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('tickets.ticket_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('secondary');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 3. Routing Tables
        Schema::create('tickets.ticket_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('tickets.ticket_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('tickets.ticket_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('tickets.ticket_routing', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // Stage, Category, Complaint
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('group_id')->constrained('tickets.ticket_groups')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
        });

        // 4. Operational Tables
        Schema::create('tickets.tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('tickets.ticket_stages')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('tickets.ticket_categories')->nullOnDelete();
            $table->foreignId('complaint_id')->nullable()->constrained('tickets.ticket_complaints')->nullOnDelete();
            $table->string('subject');
            $table->text('details');
            $table->foreignId('status_id')->constrained('tickets.ticket_statuses');
            $table->foreignId('priority_id')->constrained('tickets.ticket_priorities');
            $table->foreignId('assigned_group_id')->nullable()->constrained('tickets.ticket_groups')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('reopened_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('status_id');
            $table->index('priority_id');
            $table->index('assigned_group_id');
            $table->index('assigned_to');
            $table->index('created_at');
        });

        Schema::create('tickets.ticket_sub_complaint_pivot', function (Blueprint $table) {
            $table->foreignId('ticket_id')->constrained('tickets.tickets')->cascadeOnDelete();
            $table->foreignId('sub_complaint_id')->constrained('tickets.ticket_sub_complaints')->cascadeOnDelete();
            $table->primary(['ticket_id', 'sub_complaint_id']); // Composite Key
        });

        Schema::create('tickets.ticket_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets.tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->enum('type', ['message', 'internal_note'])->default('message');
            $table->boolean('is_read_by_staff')->default(false);
            $table->boolean('is_read_by_user')->default(false);
            $table->timestamps();
        });

        Schema::create('tickets.ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets.tickets')->cascadeOnDelete();
            $table->foreignId('thread_id')->nullable()->constrained('tickets.ticket_threads')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });

        Schema::create('tickets.ticket_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets.tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamps();
        });

        Schema::create('tickets.ticket_email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique();
            $table->string('subject');
            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets.ticket_email_templates');
        Schema::dropIfExists('tickets.ticket_audit_logs');
        Schema::dropIfExists('tickets.ticket_attachments');
        Schema::dropIfExists('tickets.ticket_threads');
        Schema::dropIfExists('tickets.ticket_sub_complaint_pivot');
        Schema::dropIfExists('tickets.tickets');
        Schema::dropIfExists('tickets.ticket_routing');
        Schema::dropIfExists('tickets.ticket_group_members');
        Schema::dropIfExists('tickets.ticket_groups');
        Schema::dropIfExists('tickets.ticket_priorities');
        Schema::dropIfExists('tickets.ticket_statuses');
        Schema::dropIfExists('tickets.ticket_sub_complaints');
        Schema::dropIfExists('tickets.ticket_complaints');
        Schema::dropIfExists('tickets.ticket_categories');
        Schema::dropIfExists('tickets.ticket_stages');

        // No DROP SCHEMA to be safe, or explicit drop if you want
        // DB::statement('DROP SCHEMA IF EXISTS tickets CASCADE');
    }
};
