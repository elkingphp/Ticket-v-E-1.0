<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'pgsql';

    public function up(): void
    {
        Schema::create('education.attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lecture_id')->constrained('education.lectures')->cascadeOnDelete();
            $table->foreignId('trainee_profile_id')->constrained('education.trainee_profiles')->cascadeOnDelete();

            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent');

            $table->text('notes')->nullable();

            // Once attendance is "locked", overriding it sends an ApprovalRequest
            $table->timestamp('locked_at')->nullable();

            // For locking mechanism internally
            $table->integer('version')->default(1);

            $table->timestamps();

            // No student can have two attendance records for the same lecture
            $table->unique(['lecture_id', 'trainee_profile_id']);

            $table->index(['locked_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education.attendances');
    }
};
