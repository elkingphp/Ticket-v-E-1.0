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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->index();
            $table->string('event_type')->index(); // audit_critical, user_registered, system_health, etc.
            $table->json('channels'); // ['database', 'mail', 'broadcast']
            $table->boolean('sound_enabled')->default(false);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            // Ensure one preference per user per event type
            $table->unique(['user_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};