<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Educational\Domain\Models\Track;
use Modules\Educational\Domain\Models\JobProfile;

class EducationalSpecialtiesSeeder extends Seeder
{
    public function run(): void
    {
        $tracks = [
            'Programming' => ['Web Developer', 'Mobile Developer', 'Full Stack Engineer'],
            'Data Science' => ['Data Analyst', 'AI Engineer', 'Machine Learning Expert'],
            'Cyber Security' => ['Pentester', 'SOC Analyst', 'Security Architect'],
            'Design' => ['UI/UX Designer', 'Graphic Designer', 'Motion Artist'],
        ];

        foreach ($tracks as $trackTitle => $profiles) {
            $track = Track::create([
                'name' => $trackTitle,
                'is_active' => true
            ]);

            foreach ($profiles as $pTitle) {
                JobProfile::create([
                    'name' => $pTitle,
                    'code' => strtoupper(substr($trackTitle, 0, 1) . '-' . substr($pTitle, 0, 3)),
                    'status' => 'active',
                    'track_id' => $track->id
                ]);
            }
        }
    }
}
