<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationType extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'education.evaluation_types';

    protected $guarded = ['id'];

    protected $casts = [
        'allowed_roles' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Target types for evaluations.
     */
    public const TARGET_TYPES = [
        'lecture' => 'محاضرة / جلسة',
        'instructor' => 'مدرب',
        'trainee' => 'متدرب',
        'program' => 'برنامج تدريبي',
        'course' => 'مقرر دراسي',
        'general' => 'عام',
    ];

    public function forms()
    {
        return $this->hasMany(EvaluationForm::class, 'form_type_id');
    }
}
