<?php

return [
    'tickets' => [
        'label' => 'settings::settings.tickets',
        'description' => 'settings::settings.tickets_desc',
        'icon' => 'ticket-2-line',
        'settings' => [
            'tickets_auto_close_after_days' => [
                'type' => 'integer',
                'label' => 'settings::settings.tickets_auto_close_days',
                'description' => 'settings::settings.tickets_auto_close_days_help',
                'default' => 7,
            ],
            'tickets_support_group_roles' => [
                'type' => 'multiselect',
                'label' => 'settings::settings.tickets_support_group_roles',
                'description' => 'settings::settings.tickets_support_group_roles_help',
                'default' => [],
                'options_source' => 'roles',
            ],
            'tickets_admin_role' => [
                'type' => 'select',
                'label' => 'الدور الإداري المرجعي/الافتراضي (للتذاكر)',
                'description' => 'هذا الدور يمتلك صلاحية افتراضية لمشاهدة كافة التذاكر والمراحل المستحدثة (صلاحية تخطي الحجب).',
                'default' => 'admin',
                'options_source' => 'roles',
            ],
            'tickets_notification_roles' => [
                'type' => 'multiselect',
                'label' => 'settings::settings.tickets_notification_roles',
                'description' => 'الادوار التي سيصلها اشعار عند انشاء تذكرة جديدة',
                'default' => [],
                'options_source' => 'roles',
            ],
            'tickets_allow_reopen' => [
                'type' => 'boolean',
                'label' => 'settings::settings.tickets_allow_reopen',
                'description' => 'settings::settings.tickets_allow_reopen_help',
                'default' => true,
            ],
            'tickets_max_per_user_per_day' => [
                'type' => 'integer',
                'label' => 'settings::settings.tickets_max_per_user',
                'description' => 'settings::settings.tickets_max_per_user_help',
                'default' => 5,
            ],
            'tickets_number_format' => [
                'type' => 'string',
                'label' => 'settings::settings.tickets_number_format',
                'description' => 'settings::settings.tickets_number_format_help',
                'default' => 'TICK-{ID}',
            ],
        ],
    ],
];
