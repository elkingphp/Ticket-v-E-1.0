<?php

namespace Modules\Core\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Domain\Models\NotificationThreshold;

class NotificationThresholdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $thresholds = [
            [
                'event_type' => 'audit_critical',
                'max_count' => 5,
                'time_window' => 3600, // 1 hour
                'severity' => 'critical',
                'description' => 'Alert when more than 5 critical audit events occur within 1 hour',
                'enabled' => true,
            ],
            [
                'event_type' => 'failed_login',
                'max_count' => 3,
                'time_window' => 300, // 5 minutes
                'severity' => 'warning',
                'description' => 'Alert when more than 3 failed login attempts from same IP within 5 minutes',
                'enabled' => true,
            ],
            [
                'event_type' => 'system_health_degraded',
                'max_count' => 2,
                'time_window' => 600, // 10 minutes
                'severity' => 'critical',
                'description' => 'Alert when system health check fails 2 times within 10 minutes',
                'enabled' => true,
            ],
            [
                'event_type' => 'database_size_warning',
                'max_count' => 1,
                'time_window' => 86400, // 24 hours
                'severity' => 'warning',
                'description' => 'Alert once per day when database size exceeds threshold',
                'enabled' => true,
            ],
            [
                'event_type' => 'user_registered',
                'max_count' => 10,
                'time_window' => 3600, // 1 hour
                'severity' => 'info',
                'description' => 'Alert when more than 10 users register within 1 hour (potential spam)',
                'enabled' => true,
            ],
        ];

        foreach ($thresholds as $threshold) {
            NotificationThreshold::updateOrCreate(
            ['event_type' => $threshold['event_type']],
                $threshold
            );
        }

        $this->command->info('✅ Notification thresholds seeded successfully.');
    }
}