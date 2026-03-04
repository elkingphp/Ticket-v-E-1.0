<?php

namespace Modules\Educational\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Educational\Domain\Models\EvaluationType;

class EvaluationFormTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'تغذية راجعة للمحاضرة',
                'slug' => 'lecture_feedback',
                'target_type' => 'lecture',
                'allowed_roles' => ['trainee', 'observer', 'admin'],
            ],
            [
                'name' => 'تقييم المقرر',
                'slug' => 'course_evaluation',
                'target_type' => 'course',
                'allowed_roles' => ['trainee', 'admin'],
            ],
            [
                'name' => 'تقييم المدرب',
                'slug' => 'instructor_evaluation',
                'target_type' => 'instructor',
                'allowed_roles' => ['trainee', 'observer', 'admin'],
            ],
            [
                'name' => 'عام',
                'slug' => 'general',
                'target_type' => 'general',
                'allowed_roles' => ['admin'],
            ],
        ];

        foreach ($types as $type) {
            EvaluationType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
