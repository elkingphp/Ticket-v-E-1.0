<?php

return [
    'modules' => [
        'Users' => 'Staff & Security',
        'Core' => 'Audit & Analytics',
        'Settings' => 'Global Settings',
        'Educational' => 'Academic Management',
        'Tickets' => 'Support Center',
    ],
    'resources' => [
        'users' => 'User Management',
        'roles' => 'Role Management',
        'permissions' => 'Permission Management',
        'students' => 'Student Management',
        'instructors' => 'Instructor Management',
        'groups' => 'Group Management',
        'lectures' => 'Lecture & Schedule Management',
        'attendance' => 'Attendance Management',
        'programs' => 'Academic Programs',
        'tracks' => 'Training Tracks',
        'job_profiles' => 'Career Profiles',
        'campus_structure' => 'Infrastructure & Rooms',
        'evaluations' => 'Evaluations & Quality',
        'evaluation_results' => 'Evaluation Analytics',
        'tickets' => 'Helpdesk System',
        'tickets.templates' => 'Ticket Templates',
        'tickets.stages' => 'Resolution Stages',
        'tickets.categories' => 'Support Categories',
        'tickets.complaints' => 'Complaint Types',
        'tickets.statuses' => 'Ticket Statuses',
        'tickets.priorities' => 'Priority Levels',
        'tickets.groups' => 'Support Teams',
        'tickets.delete_requests' => 'Ticket Deletion Requests',
        'audit' => 'System Audit',
        'analytics' => 'Data Analytics',
        'notifications' => 'Central Notifications',
        'profile' => 'User Profile',
        'settings' => 'Global Configuration',
        'integrity_widget' => 'Data Integrity',
    ],
    'list' => [
        'users' => [
            'view' => [
                'title' => 'View User Records',
                'description' => 'Ability to browse the list of all system users and employees. [User Management](/users)',
            ],
            'create' => [
                'title' => 'Add New User',
                'description' => 'Create new employee accounts and define their login credentials.',
            ],
            'edit' => [
                'title' => 'Edit Staff Profiles',
                'description' => 'Update user info, change passwords, or modify personal profiles.',
            ],
            'delete' => [
                'title' => 'Delete User Accounts',
                'description' => 'Permanently remove user accounts from the database (Highly sensitive).',
            ],
        ],
        'roles' => [
            'view' => [
                'title' => 'View Access Roles',
                'description' => 'See the organizational structure and security rules applied. [Role Management](/roles)',
            ],
            'manage' => [
                'title' => 'Manage Roles & Permissions',
                'description' => 'Create job roles and distribute permissions to control data access.',
            ],
        ],
        'permissions' => [
            'view' => [
                'title' => 'View System Permissions',
                'description' => 'See all technical control points available in the application code.',
            ],
            'manage' => [
                'title' => 'Manage Global Permissions',
                'description' => 'Add or modify technical permission strings for developers/admins.',
            ],
        ],
        'students' => [
            'view' => [
                'title' => 'Access Student Database',
                'description' => 'Full access to student records, enrollment status, and reports. [Student Page](/educational/students)',
            ],
            'create' => [
                'title' => 'Enroll New Trainee',
                'description' => 'Add academic profiles for new students and link them to programs.',
            ],
            'edit' => [
                'title' => 'Edit Student Records',
                'description' => 'Update training data, student levels, enrollment status, and results.',
            ],
            'delete' => [
                'title' => 'Delete Student Profiles',
                'description' => 'Remove students from the system (Leads to loss of attendance history).',
            ],
            'import' => [
                'title' => 'Bulk Student Import',
                'description' => 'Upload Excel files to add hundreds of students in seconds.',
            ],
            'export' => [
                'title' => 'Export Student Data',
                'description' => 'Extract student data reports to external formats for offline analysis.',
            ],
        ],
        'instructors' => [
            'view' => [
                'title' => 'View Instructor Registry',
                'description' => 'Monitor instructors list, their specialties, and schedules. [Instructors](/educational/instructors)',
            ],
            'create' => [
                'title' => 'Add New Instructor',
                'description' => 'Create instructor accounts and link them to specialties/expertise.',
            ],
            'edit' => [
                'title' => 'Modify Instructor Records',
                'description' => 'Update instructor personal info or academic specialties.',
            ],
            'delete' => [
                'title' => 'Remove Instructors',
                'description' => 'Deactivate or remove instructor contracts from the academic system.',
            ],
            'import' => [
                'title' => 'Import Instructors (Excel)',
                'description' => 'Upload bulk instructor data via formatted Excel files.',
            ],
            'export' => [
                'title' => 'Export Instructor Data',
                'description' => 'Extract instructor lists and professional profiles for reports.',
            ],
        ],
        'groups' => [
            'view' => [
                'title' => 'View Study Groups/Classes',
                'description' => 'See classes, groups, and assigned students for each. [Groups](/educational/groups)',
            ],
            'create' => [
                'title' => 'Form New Group',
                'description' => 'Establish a new class and link it to a training program and schedule.',
            ],
            'edit' => [
                'title' => 'Edit Group Properties',
                'description' => 'Change group properties, update capacities, or modify programs.',
            ],
            'delete' => [
                'title' => 'Delete Study Groups',
                'description' => 'Cancel an entire class or study group from the system.',
            ],
            'import' => [
                'title' => 'Bulk Group Import',
                'description' => 'Upload class/group data in bulk using Excel templates.',
            ],
            'export' => [
                'title' => 'Export Group Progress',
                'description' => 'Extract reports regarding groups and student distributions.',
            ],
        ],
        'lectures' => [
            'view' => [
                'title' => 'View Lecture Schedules',
                'description' => 'Monitor daily and weekly lecture plans for rooms and instructors. [Schedule](/educational/lectures)',
            ],
            'create' => [
                'title' => 'Schedule New Sessions',
                'description' => 'Define sessions/lectures and link them to instructors and location.',
            ],
            'edit' => [
                'title' => 'Modify Session Timings',
                'description' => 'Update time or location for pre-scheduled lectures before they start.',
            ],
            'delete' => [
                'title' => 'Cancel Scheduled Sessions',
                'description' => 'Remove a session from the calendar (Parties notified automatically).',
            ],
            'manage' => [
                'title' => 'Lecture Review & Approval',
                'description' => 'Approve requests for changing lectures or scheduling make-ups.',
            ],
        ],
        'attendance' => [
            'manage' => [
                'title' => 'Manage Session Attendance',
                'description' => 'Ability to record session attendance (Present/Absent/Late).',
            ],
            'report' => [
                'title' => 'Attendance Analytics',
                'description' => 'See aggregated absence stats and flags for low attendance.',
            ],
        ],
        'programs' => [
            'view' => [
                'title' => 'Browse Programs',
                'description' => 'View the list of available diplomas and training courses.',
            ],
            'manage' => [
                'title' => 'Manage Programs & Path',
                'description' => 'Control educational programs and specialties. [Programs](/educational/programs)',
            ],
        ],
        'tracks' => [
            'view' => [
                'title' => 'View Training Tracks',
                'description' => 'See detailed specialties and academic paths available.',
            ],
            'manage' => [
                'title' => 'Manage Training Tracks',
                'description' => 'Define specialties and sub-tracks within each program.',
            ],
        ],
        'job_profiles' => [
            'view' => [
                'title' => 'View Career Profiles',
                'description' => 'See target job titles for graduates in the marketplace.',
            ],
            'manage' => [
                'title' => 'Manage Career Profiles',
                'description' => 'Define target job roles for graduates and link to training.',
            ],
        ],
        'campus_structure' => [
            'manage' => [
                'title' => 'Manage Infrastructure',
                'description' => 'Control buildings, floors, and rooms across all campus locations.',
            ],
        ],
        'evaluations' => [
            'manage' => [
                'title' => 'Manage Quality Surveys',
                'description' => 'Design surveys to evaluate instructor performance and content.',
            ],
        ],
        'evaluation_results' => [
            'view' => [
                'title' => 'View Quality Analytics',
                'description' => 'Analyze student feedback and overall satisfaction reports.',
            ],
        ],
        'tickets' => [
            'view_desk' => [
                'title' => 'View Helpdesk',
                'description' => 'Ability to view the helpdesk page without accessing ticket details.',
            ],
            'reply' => [
                'title' => 'Reply to Ticket',
                'description' => 'Add replies, change status and priority (self-assignment only).',
            ],
            'distribute' => [
                'title' => 'Ticket Distribution',
                'description' => 'Assign tickets to a group or another agent.',
            ],
            'delete_requires_approval' => [
                'title' => 'Delete (Needs Approval)',
                'description' => 'Submit a deletion request for managerial review.',
            ],
            'bulk_close' => [
                'title' => 'Bulk Close Tickets',
                'description' => 'Change status of multiple tickets and add a unified response.',
            ],
        ],
        'tickets.templates' => [
            'manage' => [
                'title' => 'Manage Email Templates',
                'description' => 'Prepare email and notification templates for support.',
            ],
        ],
        'tickets.stages' => [
            'view' => ['title' => 'View Resolution Stages', 'description' => 'See ticket workflows. [Stages](/admin/tickets/stages)'],
            'create' => ['title' => 'Create Stage', 'description' => 'Add new resolution stage definitions.'],
            'update' => ['title' => 'Update Stage', 'description' => 'Modify stage SLAs.'],
            'delete_requires_approval' => ['title' => 'Delete Stage (Requires Approval)', 'description' => 'Request stage removal.'],
            'delete' => ['title' => 'Delete Stage', 'description' => 'Force delete a resolution stage.'],
        ],
        'tickets.categories' => [
            'view' => ['title' => 'View Categories', 'description' => 'See support groups and categories. [Categories](/admin/tickets/categories)'],
            'create' => ['title' => 'Create Category', 'description' => 'Set up new classifications.'],
            'update' => ['title' => 'Update Category', 'description' => 'Edit classification details.'],
            'delete_requires_approval' => ['title' => 'Delete Category (Requires Approval)', 'description' => 'Request category deletion.'],
            'delete' => ['title' => 'Delete Category', 'description' => 'Permanently remove a category.'],
        ],
        'tickets.complaints' => [
            'view' => ['title' => 'View Complaints', 'description' => 'Access complaint types. [Complaints](/admin/tickets/complaints)'],
            'create' => ['title' => 'Create Complaint', 'description' => 'Add new complaint formats.'],
            'update' => ['title' => 'Update Complaint', 'description' => 'Modify complaint metrics.'],
            'delete_requires_approval' => ['title' => 'Delete Complaint (Requires Approval)', 'description' => 'Request type removal.'],
            'delete' => ['title' => 'Delete Complaint', 'description' => 'Force remove a complaint type.'],
        ],
        'tickets.statuses' => [
            'view' => ['title' => 'View Statuses', 'description' => 'Monitor dynamic ticket statuses. [Statuses](/admin/tickets/statuses)'],
            'create' => ['title' => 'Create Status', 'description' => 'Establish new flow statuses.'],
            'update' => ['title' => 'Update Status', 'description' => 'Modify status colors/labels.'],
            'delete_requires_approval' => ['title' => 'Delete Status (Requires Approval)', 'description' => 'Request label deletion.'],
            'delete' => ['title' => 'Delete Status', 'description' => 'Remove a specific state outright.'],
        ],
        'tickets.priorities' => [
            'view' => ['title' => 'View Priorities', 'description' => 'See urgency levels. [Priorities](/admin/tickets/priorities)'],
            'create' => ['title' => 'Create Priority', 'description' => 'Set up new urgency metrics.'],
            'update' => ['title' => 'Update Priority', 'description' => 'Edit priority indicators.'],
            'delete_requires_approval' => ['title' => 'Delete Priority (Requires Approval)', 'description' => 'Request priority drop.'],
            'delete' => ['title' => 'Delete Priority', 'description' => 'Drop priority level immediately.'],
        ],
        'tickets.groups' => [
            'view' => ['title' => 'View Helpdesk Groups', 'description' => 'Access team distributions. [Teams](/admin/tickets/groups)'],
            'create' => ['title' => 'Create Group', 'description' => 'Establish a new response team.'],
            'update' => ['title' => 'Update Group', 'description' => 'Manage group agents/queues.'],
            'delete_requires_approval' => ['title' => 'Delete Group (Requires Approval)', 'description' => 'Request team disbandment.'],
            'delete' => ['title' => 'Delete Group', 'description' => 'Disband a complete helpdesk.'],
        ],
        'audit' => [
            'view' => [
                'title' => 'Global Audit Logs',
                'description' => 'Monitor all system operations and track user actions. [Audit Logs](/audit-logs)',
            ],
        ],
        'analytics' => [
            'view' => [
                'title' => 'Access Data Analytics',
                'description' => 'View charts summarizing system performance and student progress.',
            ],
        ],
        'notifications' => [
            'view' => [
                'title' => 'Manage Central Alerts',
                'description' => 'Monitor and control notifications sent to users.',
            ],
        ],
        'profile' => [
            'update' => [
                'title' => 'Update Personal Profiles',
                'description' => 'Allow users to modify their personal details and avatars.',
            ],
        ],
        'settings' => [
            'view' => [
                'title' => 'View Global Settings',
                'description' => 'See company branding and core system configurations.',
            ],
            'manage' => [
                'title' => 'Manage Core Configuration',
                'description' => 'Modify logos, payment gateways, and advanced setup. [Settings](/settings)',
            ],
        ],
        'integrity_widget' => [
            'view' => [
                'title' => 'View Data Integrity Widget',
                'description' => 'Ability to see data integration status and quality in the dashboard.',
            ],
        ],
    ],
];