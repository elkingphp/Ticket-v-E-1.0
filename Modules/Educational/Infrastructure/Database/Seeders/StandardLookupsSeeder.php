<?php

namespace Modules\Educational\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StandardLookupsSeeder extends Seeder
{
    public function run(): void
    {
        // Governorates
        $governorates = [
            ['name_ar' => 'القاهرة', 'name_en' => 'Cairo', 'status' => 'active'],
            ['name_ar' => 'الجيزة', 'name_en' => 'Giza', 'status' => 'active'],
            ['name_ar' => 'الإسكندرية', 'name_en' => 'Alexandria', 'status' => 'active'],
            ['name_ar' => 'الشرقية', 'name_en' => 'Sharqia', 'status' => 'active'],
            ['name_ar' => 'الدقهلية', 'name_en' => 'Dakahlia', 'status' => 'active'],
        ];

        foreach ($governorates as $gov) {
            DB::table('education.governorates')->updateOrInsert(
                ['name_en' => $gov['name_en']],
                [
                    'name_ar' => $gov['name_ar'],
                    'status' => $gov['status'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // Session Types
        $sessionTypes = [
            ['name' => 'Technical', 'is_active' => true],
            ['name' => 'Soft Skills', 'is_active' => true],
            ['name' => 'English', 'is_active' => true],
            ['name' => 'Exam', 'is_active' => true],
        ];

        foreach ($sessionTypes as $type) {
            DB::table('education.session_types')->updateOrInsert(
                ['name' => $type['name']],
                ['is_active' => $type['is_active'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('✅ Standard lookups (Governorates, Session Types) seeded.');
    }
}
