<?php

namespace Modules\Educational\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Modules\Users\Domain\Models\User;
use Modules\Educational\Domain\Models\InstructorProfile;
use Modules\Educational\Domain\Models\TraineeProfile;

class EducationalRolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // 1. Define Permissions
        $permissions = [
            'education.attendance.view' => 'View attendance schedules',
            'education.attendance.override' => 'Override locked attendance (Approval)',
            'education.lectures.create' => 'Generate lectures from templates',
            'education.lectures.update' => 'Update lecture status (cancel/reschedule)',
            'education.lectures.view' => 'View all lectures',
            'education.evaluations.submit' => 'Submit lecture evaluations',
            'education.evaluations.manage' => 'Manage evaluation forms and answers',
            'education.schedule_templates.manage' => 'Manage schedule templates',
            'education.approvals.approve' => 'Approve educational requests',
            'education.notifications.view' => 'View educational notifications',
            'education.tickets.manage' => 'Manage and open critical tickets',
            'education.access' => 'Access educational management section',
        ];

        // Ensure permissions exist
        foreach ($permissions as $permissionName => $description) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['module' => 'educational'] // Depending on custom migration column
            );
        }

        // 2. Define Roles and Assign Permissions
        $roles = [
            'Admin' => [
                'education.attendance.view',
                'education.attendance.override',
                'education.lectures.create',
                'education.lectures.update',
                'education.lectures.view',
                'education.evaluations.manage',
                'education.schedule_templates.manage',
                'education.approvals.approve',
                'education.notifications.view',
                'education.tickets.manage'
            ],
            'Instructor' => [
                'education.attendance.view',
                'education.lectures.view',
                'education.evaluations.submit',
                'education.notifications.view'
            ],
            'Trainee' => [
                'education.attendance.view',
                'education.lectures.view',
                'education.evaluations.submit',
                'education.notifications.view'
            ],
            'QA' => [
                'education.attendance.view',
                'education.lectures.view',
                'education.evaluations.manage',
                'education.tickets.manage',
                'education.notifications.view'
            ]
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            // Sync permissions to the role
            $role->syncPermissions($rolePermissions);
        }

        // 3. Create Demo Users and assign Profiles
        $this->createDemoUser('admin1@digilians.com', 'Admin 1', 'Admin');

        $instructorUser = $this->createDemoUser('instructor1@digilians.com', 'Instructor 1', 'Instructor');
        InstructorProfile::firstOrCreate(
            ['user_id' => $instructorUser->id],
            [
                'bio' => 'Demo Instructor',
                'employment_type' => 'full_time',
                'status' => 'active'
            ]
        );

        $traineeUser = $this->createDemoUser('trainee1@digilians.com', 'Trainee 1', 'Trainee');
        TraineeProfile::firstOrCreate(
            ['user_id' => $traineeUser->id],
            [
                'national_id' => '123456789' . rand(10, 99),
                'enrollment_status' => 'active'
            ]
        );

        $this->createDemoUser('qa1@digilians.com', 'QA Supervisor 1', 'QA');
    }

    /**
     * Create or retrieve a demo user and assign a role.
     */
    private function createDemoUser($email, $name, $roleName)
    {
        $nameParts = explode(' ', $name);
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'username' => strtolower($nameParts[0]) . rand(100, 999),
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        if (!$user->hasRole($roleName)) {
            $user->assignRole($roleName);
        }

        return $user;
    }
}
