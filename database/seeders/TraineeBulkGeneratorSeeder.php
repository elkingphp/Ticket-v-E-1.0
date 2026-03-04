<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Users\Domain\Models\User;
use Modules\Educational\Domain\Models\TraineeProfile;
use Modules\Educational\Domain\Models\Group;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class TraineeBulkGeneratorSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ar_SA');
        $groups = Group::all();

        if ($groups->isEmpty()) {
            $this->command->error('لا توجد مجموعات متاحة لتوزيع الطلاب عليها.');
            return;
        }

        $this->command->info('بدء عملية إنشاء 1000 طالب وتوزيعهم...');

        $count = 1000;
        $bar = $this->command->getOutput()->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            // Create User
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $username = 'std_' . Str::random(5) . '_' . time() . '_' . $i;

            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $username,
                'email' => $username . '@example.com',
                'password' => Hash::make('password'),
                'status' => 'active',
                'joined_at' => now(),
            ]);

            // Pick a random group and its related data
            $group = $groups->random();

            // Create Trainee Profile
            TraineeProfile::create([
                'user_id' => $user->id,
                'arabic_name' => $firstName . ' ' . $lastName,
                'national_id' => $faker->numerify('##############'),
                'enrollment_status' => 'active',
                'gender' => $faker->randomElement(['male', 'female']),
                'program_id' => $group->program_id,
                'group_id' => $group->id,
                'job_profile_id' => $group->job_profile_id,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->command->info("\nتم إنشاء 1000 طالب وتوزيعهم على المجموعات بنجاح.");
    }
}
