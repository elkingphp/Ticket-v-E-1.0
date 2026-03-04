<?php

return [
    'general' => [
        'label' => 'settings::settings.general',
        'description' => 'settings::settings.general_desc',
        'icon' => 'settings-4-line',
        'settings' => [
            'approval_notification_roles' => [
                'type' => 'multiselect',
                'label' => 'settings::settings.approval_notification_roles',
                'description' => 'settings::settings.approval_notification_roles_help',
                'default' => [],
                'options_source' => 'roles',
            ],
            'site_name' => [
                'type' => 'string',
                'label' => 'settings::settings.site_name',
                'description' => 'settings::settings.site_name_help',
                'default' => 'Digilians',
            ],
            'site_description' => [
                'type' => 'text',
                'label' => 'settings::settings.site_description',
                'description' => 'settings::settings.site_description_help',
                'default' => 'Premium Laravel Modular Application',
            ],
            'contact_email' => [
                'type' => 'string',
                'label' => 'settings::settings.contact_email',
                'description' => 'settings::settings.contact_email_help',
                'default' => 'contact@digilians.com',
            ],
            'maintenance_mode' => [
                'type' => 'boolean',
                'label' => 'settings::settings.maintenance_mode',
                'description' => 'settings::settings.maintenance_mode_help',
                'default' => false,
            ],
            'maintenance_message' => [
                'type' => 'text',
                'label' => 'settings::settings.maintenance_message',
                'description' => 'settings::settings.maintenance_message_help',
                'default' => 'System is currently under maintenance. Please check back later.',
            ],
        ],
    ],
    'branding' => [
        'label' => 'settings::settings.branding',
        'description' => 'settings::settings.branding_desc',
        'icon' => 'palette-line',
        'settings' => [
            'logo_light' => [
                'type' => 'image',
                'label' => 'settings::settings.logo_light',
                'description' => 'settings::settings.logo_light_help',
                'default' => 'assets/images/logo-light.png',
            ],
            'logo_dark' => [
                'type' => 'image',
                'label' => 'settings::settings.logo_dark',
                'description' => 'settings::settings.logo_dark_help',
                'default' => 'assets/images/logo-dark.png',
            ],
            'logo_sm' => [
                'type' => 'image',
                'label' => 'settings::settings.logo_sm',
                'description' => 'settings::settings.logo_sm_help',
                'default' => 'assets/images/logo-sm.png',
            ],
            'favicon' => [
                'type' => 'image',
                'label' => 'settings::settings.favicon',
                'description' => 'settings::settings.favicon_help',
                'default' => 'assets/images/favicon.ico',
            ],
        ],
    ],
    'security' => [
        'label' => 'settings::settings.security',
        'description' => 'settings::settings.security_desc',
        'icon' => 'shield-keyhole-line',
        'settings' => [
            'enable_registration' => [
                'type' => 'boolean',
                'label' => 'settings::settings.enable_registration',
                'description' => 'settings::settings.enable_registration_help',
                'default' => true,
            ],
            'enable_2fa' => [
                'type' => 'boolean',
                'label' => 'settings::settings.enable_2fa',
                'description' => 'settings::settings.enable_2fa_help',
                'default' => false,
            ],
            '2fa_app_name' => [
                'type' => 'string',
                'label' => 'settings::settings.2fa_app_name',
                'description' => 'settings::settings.2fa_app_name_help',
                'default' => 'Digilians',
            ],
        ],
    ],
    'google' => [
        'label' => 'settings::settings.google',
        'description' => 'settings::settings.google_desc',
        'icon' => 'google-line',
        'settings' => [
            'google_client_id' => [
                'type' => 'string',
                'label' => 'settings::settings.google_client_id',
                'description' => 'settings::settings.google_client_id_help',
                'default' => '',
            ],
            'google_client_secret' => [
                'type' => 'string',
                'label' => 'settings::settings.google_client_secret',
                'description' => 'settings::settings.google_client_secret_help',
                'default' => '',
            ],
            'google_redirect' => [
                'type' => 'string',
                'label' => 'settings::settings.google_redirect',
                'description' => 'settings::settings.google_redirect_help',
                'default' => '',
            ],
        ],
    ],
    'mail' => [
        'label' => 'settings::settings.mail',
        'description' => 'settings::settings.mail_desc',
        'icon' => 'mail-line',
        'settings' => [
            'mail_mailer' => [
                'type' => 'select',
                'label' => 'settings::settings.mail_mailer',
                'description' => 'settings::settings.mail_mailer_help',
                'default' => 'smtp',
                'options' => [
                    'smtp' => 'SMTP',
                    'sendmail' => 'Sendmail',
                    'mailgun' => 'Mailgun',
                    'ses' => 'Amazon SES',
                    'postmark' => 'Postmark',
                    'log' => 'Log (Development)',
                ],
            ],
            'mail_host' => [
                'type' => 'string',
                'label' => 'settings::settings.mail_host',
                'description' => 'settings::settings.mail_host_help',
                'default' => 'smtp.mailtrap.io',
            ],
            'mail_port' => [
                'type' => 'string',
                'label' => 'settings::settings.mail_port',
                'description' => 'settings::settings.mail_port_help',
                'default' => '2525',
            ],
            'mail_username' => [
                'type' => 'string',
                'label' => 'settings::settings.mail_username',
                'description' => 'settings::settings.mail_username_help',
                'default' => '',
            ],
            'mail_password' => [
                'type' => 'string',
                'label' => 'settings::settings.mail_password',
                'description' => 'settings::settings.mail_password_help',
                'default' => '',
            ],
            'mail_encryption' => [
                'type' => 'select',
                'label' => 'settings::settings.mail_encryption',
                'description' => 'settings::settings.mail_encryption_help',
                'default' => 'tls',
                'options' => [
                    'tls' => 'TLS',
                    'ssl' => 'SSL',
                    'none' => 'None',
                ],
            ],
            'mail_from_address' => [
                'type' => 'string',
                'label' => 'settings::settings.mail_from_address',
                'description' => 'settings::settings.mail_from_address_help',
                'default' => 'no-reply@digilians.com',
            ],
            'mail_from_name' => [
                'type' => 'string',
                'label' => 'settings::settings.mail_from_name',
                'description' => 'settings::settings.mail_from_name_help',
                'default' => 'Digilians System',
            ],
        ],
    ],
];
