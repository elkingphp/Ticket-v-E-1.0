<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('approval_request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->integer('step_level'); // The hierarchy level of this step
            $table->string('required_permission')->nullable(); // e.g., 'education.approvals.manage'

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comments')->nullable();
            $table->timestamp('acted_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['approval_request_id', 'step_level', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_steps');
    }
};
