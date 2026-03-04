<?php

return [
    'educational' => [
        'label' => 'educational::settings.educational',
        'description' => 'educational::settings.educational_desc',
        'icon' => 'bookmark-3-line',
        'settings' => [
            'educational_students_per_page' => [
                'type' => 'integer',
                'label' => 'educational::settings.students_per_page',
                'description' => 'educational::settings.students_per_page_help',
                'default' => 12,
            ],
            'educational_instructors_per_page' => [
                'type' => 'integer',
                'label' => 'educational::settings.instructors_per_page',
                'description' => 'educational::settings.instructors_per_page_help',
                'default' => 12,
            ],
            'educational_attendance_lock_hours' => [
                'type' => 'integer',
                'label' => 'educational::settings.attendance_lock_hours',
                'description' => 'educational::settings.attendance_lock_hours_help',
                'default' => 24,
            ],
            'educational_supervisor_roles' => [
                'type' => 'multiselect',
                'label' => 'educational::settings.supervisor_roles',
                'description' => 'educational::settings.supervisor_roles_help',
                'default' => [],
                'options_source' => 'roles',
            ],
            'educational_global_supervisor_roles' => [
                'type' => 'multiselect',
                'label' => 'educational::settings.global_supervisor_roles',
                'description' => 'educational::settings.global_supervisor_roles_help',
                'default' => [],
                'options_source' => 'roles',
            ],
            'educational_default_displayed_programs' => [
                'type' => 'multiselect',
                'label' => 'educational::settings.default_displayed_programs',
                'description' => 'educational::settings.default_displayed_programs_help',
                'default' => [],
                'options_source' => 'programs',
            ],
            'educational_default_displayed_tracks' => [
                'type' => 'multiselect',
                'label' => 'educational::settings.default_displayed_tracks',
                'description' => 'educational::settings.default_displayed_tracks_help',
                'default' => [],
                'options_source' => 'tracks',
            ],
            'educational_track_responsible_roles' => [
                'type' => 'multiselect',
                'label' => 'educational::settings.track_responsible_roles',
                'description' => 'educational::settings.track_responsible_roles_help',
                'default' => [],
                'options_source' => 'roles',
            ],
        ],
    ],
];
