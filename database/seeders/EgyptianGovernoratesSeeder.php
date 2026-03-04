<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EgyptianGovernoratesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $governorates = [
            ['name_ar' => 'القاهرة', 'name_en' => 'Cairo', 'status' => 'active'],
            ['name_ar' => 'الجيزة', 'name_en' => 'Giza', 'status' => 'active'],
            ['name_ar' => 'الإسكندرية', 'name_en' => 'Alexandria', 'status' => 'active'],
            ['name_ar' => 'الدقهلية', 'name_en' => 'Dakahlia', 'status' => 'active'],
            ['name_ar' => 'البحر الأحمر', 'name_en' => 'Red Sea', 'status' => 'active'],
            ['name_ar' => 'البحيرة', 'name_en' => 'Beheira', 'status' => 'active'],
            ['name_ar' => 'الفيوم', 'name_en' => 'Fayoum', 'status' => 'active'],
            ['name_ar' => 'الغربية', 'name_en' => 'Gharbia', 'status' => 'active'],
            ['name_ar' => 'الإسماعيلية', 'name_en' => 'Ismailia', 'status' => 'active'],
            ['name_ar' => 'المنوفية', 'name_en' => 'Menofia', 'status' => 'active'],
            ['name_ar' => 'المنيا', 'name_en' => 'Minya', 'status' => 'active'],
            ['name_ar' => 'القليوبية', 'name_en' => 'Qalyubia', 'status' => 'active'],
            ['name_ar' => 'الوادي الجديد', 'name_en' => 'New Valley', 'status' => 'active'],
            ['name_ar' => 'السويس', 'name_en' => 'Suez', 'status' => 'active'],
            ['name_ar' => 'أسوان', 'name_en' => 'Aswan', 'status' => 'active'],
            ['name_ar' => 'أسيوط', 'name_en' => 'Assiut', 'status' => 'active'],
            ['name_ar' => 'بني سويف', 'name_en' => 'Beni Suef', 'status' => 'active'],
            ['name_ar' => 'بورسعيد', 'name_en' => 'Port Said', 'status' => 'active'],
            ['name_ar' => 'دمياط', 'name_en' => 'Damietta', 'status' => 'active'],
            ['name_ar' => 'الشرقية', 'name_en' => 'Sharkia', 'status' => 'active'],
            ['name_ar' => 'جنوب سيناء', 'name_en' => 'South Sinai', 'status' => 'active'],
            ['name_ar' => 'كفر الشيخ', 'name_en' => 'Kafr El Sheikh', 'status' => 'active'],
            ['name_ar' => 'مطروح', 'name_en' => 'Matrouh', 'status' => 'active'],
            ['name_ar' => 'الأقصر', 'name_en' => 'Luxor', 'status' => 'active'],
            ['name_ar' => 'قنا', 'name_en' => 'Qena', 'status' => 'active'],
            ['name_ar' => 'شمال سيناء', 'name_en' => 'North Sinai', 'status' => 'active'],
            ['name_ar' => 'سوهاج', 'name_en' => 'Sohag', 'status' => 'active'],
        ];

        foreach ($governorates as $governorate) {
            DB::table('education.governorates')->updateOrInsert(
                ['name_ar' => $governorate['name_ar']],
                [
                    'name_en' => $governorate['name_en'],
                    'status' => $governorate['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
