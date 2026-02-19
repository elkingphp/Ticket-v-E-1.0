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
        Schema::create('module_request_traces', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->unique();
            $table->string('module_slug')->index();
            $table->string('module_state');
            $table->unsignedInteger('latency_ms');
            $table->string('http_method');
            $table->text('url');
            $table->integer('status_code');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            // Indexes for fast lookup in Mission Control
            $table->index(['module_slug', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_request_traces');
    }
};