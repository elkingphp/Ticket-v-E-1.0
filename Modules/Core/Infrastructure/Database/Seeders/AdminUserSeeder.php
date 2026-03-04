<?php

namespace Modules\Core\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Users\Domain\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primary Admin User requested by User
        $primaryAdmin = User::firstOrCreate(
            ['email' => 'elkingphp@gmail.com'],
            [
                'first_name' => 'System',
                'last_name' => 'Manager',
                'username' => 'elkingphp',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'language' => 'ar',
                'theme_mode' => 'dark',
                'joined_at' => now(),
            ]
        );
        $primaryAdmin->assignRole('super-admin');

        // Check if user already exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@digilians.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'language' => 'ar',
                'theme_mode' => 'dark',
                'joined_at' => now(),
            ]
        );

        $admin->assignRole('super-admin');

        // Editor User
        $editor = User::firstOrCreate(
            ['email' => 'editor@digilians.com'],
            [
                'first_name' => 'System',
                'last_name' => 'Editor',
                'username' => 'editor',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'language' => 'en',
                'theme_mode' => 'light',
                'joined_at' => now(),
            ]
        );
        $editor->assignRole('editor');

        // Regular User
        $user = User::firstOrCreate(
            ['email' => 'user@digilians.com'],
            [
                'first_name' => 'Regular',
                'last_name' => 'User',
                'username' => 'user',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'language' => 'ar',
                'theme_mode' => 'light',
                'joined_at' => now(),
            ]
        );
        $user->assignRole('regular-user');

        $this->command->info('Test users (Admin, Editor, User) created successfully.');
    }
}