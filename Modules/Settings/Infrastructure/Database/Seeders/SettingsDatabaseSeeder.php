<?php

namespace Modules\Settings\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Domain\Models\Setting;

class SettingsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_name',
                'value' => 'Digilians',
                'group' => 'general',
                'type' => 'string',
                'label' => 'Site Name',
                'description' => 'The name of the application.',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'site_description',
                'value' => 'Premium Laravel Modular Application',
                'group' => 'general',
                'type' => 'text',
                'label' => 'Site Description',
                'description' => 'A brief description of the application.',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'contact_email',
                'value' => 'contact@digilians.com',
                'group' => 'general',
                'type' => 'string',
                'label' => 'Contact Email',
                'description' => 'The primary contact email address.',
                'is_public' => true,
                'sort_order' => 3,
            ],

            [
                'key' => 'logo_light',
                'value' => 'assets/images/logo-light.png',
                'group' => 'branding',
                'type' => 'image',
                'label' => 'Logo (Light Mode)',
                'description' => 'The logo used in light theme.',
                'is_public' => true,
                'sort_order' => 4,
            ],
            [
                'key' => 'logo_dark',
                'value' => 'assets/images/logo-dark.png',
                'group' => 'branding',
                'type' => 'image',
                'label' => 'Logo (Dark Mode)',
                'description' => 'The logo used in dark theme.',
                'is_public' => true,
                'sort_order' => 5,
            ],
            [
                'key' => 'logo_sm',
                'value' => 'assets/images/logo-sm.png',
                'group' => 'branding',
                'type' => 'image',
                'label' => 'Small Logo (Icon)',
                'description' => 'The small icon logo used in collapsed sidebar.',
                'is_public' => true,
                'sort_order' => 6,
            ],


            [
                'key' => 'favicon',
                'value' => 'assets/images/favicon.ico',
                'group' => 'branding',
                'type' => 'image',
                'label' => 'Favicon',
                'description' => 'The icon used in browser tabs.',
                'is_public' => true,
                'sort_order' => 6,
            ],

            // Security
            [
                'key' => 'enable_registration',
                'value' => '1',
                'group' => 'security',
                'type' => 'boolean',
                'label' => 'Enable User Registration',
                'description' => 'Allow new users to register on the site.',
                'is_public' => false,
                'sort_order' => 7,
            ],
            [
                'key' => 'enable_2fa',
                'value' => '0',
                'group' => 'security',
                'type' => 'boolean',
                'label' => 'Enforce 2FA',
                'description' => 'Force all users to enable two-factor authentication.',
                'is_public' => false,
                'sort_order' => 8,
            ],
            [
                'key' => '2fa_app_name',
                'value' => 'Digilians',
                'group' => 'security',
                'type' => 'string',
                'label' => '2FA Application Name',
                'description' => 'The application name shown in authenticator apps.',
                'is_public' => false,
                'sort_order' => 9,
            ],
            [
                'key' => 'google_client_id',
                'value' => '',
                'group' => 'google',
                'type' => 'string',
                'label' => 'Google Client ID',
                'description' => 'Client ID for Google integration.',
                'is_public' => false,
                'sort_order' => 10,
            ],
            [
                'key' => 'google_client_secret',
                'value' => '',
                'group' => 'google',
                'type' => 'string',
                'label' => 'Google Client Secret',
                'description' => 'Client Secret for Google integration.',
                'is_public' => false,
                'sort_order' => 11,
            ],
            [
                'key' => 'google_redirect',
                'value' => '',
                'group' => 'google',
                'type' => 'string',
                'label' => 'Google Redirect URL',
                'description' => 'Redirect URL for Google integration.',
                'is_public' => false,
                'sort_order' => 12,
            ],

            // Mail Settings
            [
                'key' => 'mail_mailer',
                'value' => 'smtp',
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Mail Mailer',
                'description' => 'The mailer driver (e.g., smtp, sendmail, log).',
                'is_public' => false,
                'sort_order' => 13,
            ],
            [
                'key' => 'mail_host',
                'value' => 'smtp.mailtrap.io',
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Mail Host',
                'description' => 'The SMTP server address.',
                'is_public' => false,
                'sort_order' => 14,
            ],
            [
                'key' => 'mail_port',
                'value' => '2525',
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Mail Port',
                'description' => 'The SMTP server port.',
                'is_public' => false,
                'sort_order' => 15,
            ],
            [
                'key' => 'mail_username',
                'value' => '',
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Mail Username',
                'description' => 'The SMTP server username.',
                'is_public' => false,
                'sort_order' => 16,
            ],
            [
                'key' => 'mail_password',
                'value' => '',
                'group' => 'mail',
                'type' => 'string', // Should be encrypted in real app, but for settings table it is string
                'label' => 'Mail Password',
                'description' => 'The SMTP server password.',
                'is_public' => false,
                'sort_order' => 17,
            ],
            [
                'key' => 'mail_encryption',
                'value' => 'tls',
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Mail Encryption',
                'description' => 'The encryption protocol (tls, ssl).',
                'is_public' => false,
                'sort_order' => 18,
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'no-reply@digilians.com',
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Mail From Address',
                'description' => 'The email address that outgoing emails are sent from.',
                'is_public' => false,
                'sort_order' => 19,
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'Digilians System',
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Mail From Name',
                'description' => 'The name that outgoing emails are sent from.',
                'is_public' => false,
                'sort_order' => 20,
            ],

        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}