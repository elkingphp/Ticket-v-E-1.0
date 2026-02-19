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
        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('version')->nullable();
            $table->enum('status', ['registered', 'installed', 'active', 'degraded', 'maintenance', 'disabled'])->default('registered');
            $table->boolean('is_core')->default(false);
            $table->integer('priority')->default(0);
            $table->json('feature_flags')->nullable();
            $table->json('metadata')->nullable();
            $table->string('health_status')->default('healthy');
            $table->integer('max_concurrent_requests')->default(20);
            $table->unsignedBigInteger('state_version')->default(1);
            $table->timestamps();
        });

        Schema::create('module_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignUuid('depends_on_id')->constrained('modules')->onDelete('cascade');
            $table->unique(['module_id', 'depends_on_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_dependencies');
        Schema::dropIfExists('modules');
    }
};