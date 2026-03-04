<?php

namespace Modules\Educational\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Application\Services\SettingsService;

class EvaluationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(SettingsService $settings): void
    {
        $defaults = [
            'evaluation_analytics_threshold' => 3.0,
            'evaluation_analytics_red_flag_enabled' => true,
            'evaluation_analytics_cache_seconds' => 600,
            'evaluation_notifications_channel' => 'mail_and_database',
            'evaluation_notifications_assignment_enabled' => true,
            'evaluation_scheduler_report_day' => 'fridays',
            'evaluation_scheduler_report_time' => '08:00',
        ];

        foreach ($defaults as $key => $value) {
            // Only set if not already exists to avoid overwriting production configs
            if ($settings->get($key) === null) {
                $settings->set($key, $value, 'Educational');
            }
        }
    }
}
