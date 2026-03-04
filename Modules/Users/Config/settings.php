<?php

return [
    'user_profile' => [
        'label' => 'settings::settings.user_profile',
        'description' => 'settings::settings.user_profile_desc',
        'icon' => 'user-settings-line',
        'settings' => [
            'profile_weight_first_name' => [
                'type' => 'integer',
                'label' => 'settings::settings.profile_weight_first_name',
                'description' => 'settings::settings.profile_weight_first_name_help',
                'default' => 5,
            ],
            'profile_weight_last_name' => [
                'type' => 'integer',
                'label' => 'settings::settings.profile_weight_last_name',
                'description' => 'settings::settings.profile_weight_last_name_help',
                'default' => 5,
            ],
            'profile_weight_email_verified' => [
                'type' => 'integer',
                'label' => 'settings::settings.profile_weight_email_verified',
                'description' => 'settings::settings.profile_weight_email_verified_help',
                'default' => 20,
            ],
            'profile_weight_2fa_active' => [
                'type' => 'integer',
                'label' => 'settings::settings.profile_weight_2fa_active',
                'description' => 'settings::settings.profile_weight_2fa_active_help',
                'default' => 20,
            ],
            'profile_weight_phone' => [
                'type' => 'integer',
                'label' => 'settings::settings.profile_weight_phone',
                'description' => 'settings::settings.profile_weight_phone_help',
                'default' => 10,
            ],
            'profile_weight_avatar' => [
                'type' => 'integer',
                'label' => 'settings::settings.profile_weight_avatar',
                'description' => 'settings::settings.profile_weight_avatar_help',
                'default' => 5,
            ],
            'profile_weight_language' => [
                'type' => 'integer',
                'label' => 'settings::settings.profile_weight_language',
                'description' => 'settings::settings.profile_weight_language_help',
                'default' => 5,
            ],
            'profile_weight_timezone' => [
                'type' => 'integer',
                'label' => 'settings::settings.profile_weight_timezone',
                'description' => 'settings::settings.profile_weight_timezone_help',
                'default' => 5,
            ],
            'profile_weight_sessions_count' => [
                'type' => 'integer',
                'label' => 'settings::settings.profile_weight_sessions_count',
                'description' => 'settings::settings.profile_weight_sessions_count_help',
                'default' => 25,
            ],
            'profile_risk_threshold_low' => [
                'type' => 'integer',
                'label' => 'settings::settings.profile_risk_threshold_low',
                'description' => 'settings::settings.profile_risk_threshold_low_help',
                'default' => 80,
            ],
            'profile_risk_threshold_medium' => [
                'type' => 'integer',
                'label' => 'settings::settings.profile_risk_threshold_medium',
                'description' => 'settings::settings.profile_risk_threshold_medium_help',
                'default' => 50,
            ],
        ],
    ],
];
