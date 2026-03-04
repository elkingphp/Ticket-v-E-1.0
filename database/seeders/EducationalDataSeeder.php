<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Educational\Domain\Models\Governorate;
use Modules\Educational\Domain\Models\Track;
use Modules\Educational\Domain\Models\JobProfile;
use Illuminate\Support\Str;

class EducationalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Governorates (Egypt Examples)
        $governorates = [
            ['name_ar' => 'القاهرة', 'name_en' => 'Cairo', 'status' => 'active'],
            ['name_ar' => 'الإسكندرية', 'name_en' => 'Alexandria', 'status' => 'active'],
            ['name_ar' => 'الجيزة', 'name_en' => 'Giza', 'status' => 'active'],
            ['name_ar' => 'الدقهلية', 'name_en' => 'Dakahlia', 'status' => 'active'],
            ['name_ar' => 'البحر الأحمر', 'name_en' => 'Red Sea', 'status' => 'active'],
            ['name_ar' => 'أسيوط', 'name_en' => 'Asyut', 'status' => 'active'],
            ['name_ar' => 'القليوبية', 'name_en' => 'Qalyubia', 'status' => 'active'],
        ];

        foreach ($governorates as $gov) {
            Governorate::firstOrCreate(['name_ar' => $gov['name_ar']], $gov);
        }

        // 2. Seed Tracks
        $tracksData = [
            [
                'name' => 'Software Engineering',
                'is_active' => true,
                'job_profiles' => [
                    ['name' => 'Frontend Developer', 'code' => 'FE-01', 'description' => 'Vue, React, Angular devs', 'is_active' => true],
                    ['name' => 'Backend Developer', 'code' => 'BE-01', 'description' => 'PHP, Laravel, Node.js devs', 'is_active' => true],
                    ['name' => 'Fullstack Developer', 'code' => 'FS-01', 'description' => 'End-to-End developers', 'is_active' => true],
                ]
            ],
            [
                'name' => 'Data Science & AI',
                'is_active' => true,
                'job_profiles' => [
                    ['name' => 'Data Analyst', 'code' => 'DA-01', 'description' => 'SQL, Python, PowerBI', 'is_active' => true],
                    ['name' => 'Machine Learning Engineer', 'code' => 'MLE-01', 'description' => 'TensorFlow, PyTorch', 'is_active' => true],
                ]
            ],
            [
                'name' => 'Digital Marketing',
                'is_active' => true,
                'job_profiles' => [
                    ['name' => 'SEO Specialist', 'code' => 'SEO-01', 'description' => 'Search engine optimization', 'is_active' => true],
                    ['name' => 'Social Media Manager', 'code' => 'SMM-01', 'description' => 'Content and campaigns', 'is_active' => true],
                ]
            ],
        ];

        foreach ($tracksData as $trackItem) {
            $track = Track::firstOrCreate(
                ['name' => $trackItem['name']],
                [
                    'slug' => Str::slug($trackItem['name']),
                    'is_active' => $trackItem['is_active']
                ]
            );

            // Seed Job Profiles for this Track
            foreach ($trackItem['job_profiles'] as $profileItem) {
                JobProfile::firstOrCreate(
                    ['code' => $profileItem['code']],
                    [
                        'track_id' => $track->id,
                        'name' => $profileItem['name'],
                        'status' => 'active'
                    ]
                );
            }
        }
    }
}
