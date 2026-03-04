<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();

            // Polymorphic relation with schema support
            $table->string('approvable_schema')->nullable(); // e.g., 'education.programs'
            $table->morphs('approvable'); // creates approvable_type, approvable_id

            // Approval details
            $table->string('action')->default('other'); // e.g., 'create', 'update', 'delete', 'cancel_lecture', 'delete_lecture'
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');

            // Related users
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();

            // State
            $table->integer('approval_level')->default(1);
            $table->json('metadata')->nullable(); // stores old_values, new_values, reasons, etc.
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index('approvable_schema');
            $table->index(['status', 'approvable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
