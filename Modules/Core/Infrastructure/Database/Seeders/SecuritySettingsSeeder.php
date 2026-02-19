<?php

namespace Modules\Core\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Application\Services\SettingsService;

class SecuritySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(SettingsService $settings): void
    {
        // 2FA Settings
        $settings->set('2fa_required', false, 'Core', false);
        $settings->set('2fa_allow_recovery', true, 'Core', false);

        // Audit Retention
        $settings->set('audit_retention_days', 90, 'Core', false);

        // Password Policy
        $settings->set('password_min_length', 12, 'Core', false);
        $settings->set('password_require_special', true, 'Core', false);

        // Session
        $settings->set('session_lifetime', 120, 'Core', false);
    }
}