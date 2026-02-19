<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_event_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('category')->default('system'); // system, security, audit, etc.
            $table->boolean('is_mandatory')->default(false);
            $table->json('available_channels'); // ['database', 'mail', 'broadcast', 'sms', etc.]
            $table->timestamps();
        });

        // Seed initial data
        DB::table('notification_event_types')->insert([
            [
                'key' => 'audit_critical',
                'title' => 'Critical Audit Events',
                'description' => 'Tells you when sensitive records are deleted or modified.',
                'category' => 'security',
                'is_mandatory' => true,
                'available_channels' => json_encode(['database', 'mail', 'broadcast']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'system_health',
                'title' => 'System Health Alerts',
                'description' => 'Alerts concerning the overall performance and health of the server.',
                'category' => 'system',
                'is_mandatory' => false,
                'available_channels' => json_encode(['database', 'mail']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'user_registered',
                'title' => 'New User Registrations',
                'description' => 'Get notified when someone creates a new account.',
                'category' => 'admin',
                'is_mandatory' => false,
                'available_channels' => json_encode(['database', 'mail']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'threshold_exceeded',
                'title' => 'Resource Threshold Exceeded',
                'description' => 'Alerts when CPU, RAM or other resources cross the set limits.',
                'category' => 'system',
                'is_mandatory' => true,
                'available_channels' => json_encode(['database', 'mail', 'broadcast']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'security_login',
                'title' => 'New Login Detected',
                'description' => 'Alerts when a login occurs from a new device or location.',
                'category' => 'security',
                'is_mandatory' => true,
                'available_channels' => json_encode(['database', 'mail', 'broadcast']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_event_types');
    }
};