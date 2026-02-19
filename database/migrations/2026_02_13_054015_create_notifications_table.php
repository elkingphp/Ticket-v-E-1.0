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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type')->index();
            $table->morphs('notifiable'); // notifiable_type + notifiable_id
            $table->json('data');
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamp('created_at')->index();
            $table->timestamp('expires_at')->nullable()->index();

            // Composite indexes for optimal performance
            $table->index(['notifiable_id', 'notifiable_type', 'read_at'], 'notifiable_read_idx');
            $table->index(['created_at', 'read_at'], 'created_read_idx');
            $table->index(['type', 'created_at'], 'type_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};