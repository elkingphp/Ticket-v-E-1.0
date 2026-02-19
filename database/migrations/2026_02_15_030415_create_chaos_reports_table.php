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
        Schema::create('chaos_reports', function (Blueprint $table) {
            $table->id();
            $table->string('test_name');
            $table->string('type'); // burst, redis_loss, saturation
            $table->enum('result', ['passed', 'failed', 'partial']);
            $table->json('metrics')->nullable();
            $table->text('summary')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chaos_reports');
    }
};