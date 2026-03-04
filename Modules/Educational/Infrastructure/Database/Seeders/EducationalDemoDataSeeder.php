<?php

namespace Modules\Educational\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EducationalDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Programs
        $programs = [
            ['name' => 'Fullstack Development', 'status' => 'published', 'code' => 'FS-01', 'description' => 'Web development program'],
            ['name' => 'Data Science', 'status' => 'published', 'code' => 'DS-01', 'description' => 'Data analytics and ML'],
            ['name' => 'UI/UX Design', 'status' => 'published', 'code' => 'UI-01', 'description' => 'Product design program'],
        ];

        foreach ($programs as $prog) {
            \Modules\Educational\Domain\Models\Program::updateOrCreate(
                ['code' => $prog['code']],
                ['name' => $prog['name'], 'status' => $prog['status'], 'description' => $prog['description']]
            );
        }

        // Tracks
        $tracks = [
            ['name' => 'PHP/Laravel', 'is_active' => true, 'slug' => 'php-laravel', 'code' => 'T-PHP'],
            ['name' => 'React/Node', 'is_active' => true, 'slug' => 'react-node', 'code' => 'T-RN'],
            ['name' => 'Python Analytics', 'is_active' => true, 'slug' => 'python-analytics', 'code' => 'T-PY'],
        ];

        foreach ($tracks as $track) {
            \Modules\Educational\Domain\Models\Track::updateOrCreate(
                ['code' => $track['code']],
                ['name' => $track['name'], 'slug' => $track['slug'], 'is_active' => $track['is_active']]
            );
        }

        $this->command->info('✅ Educational demo data (Programs, Tracks) seeded.');
    }

}
