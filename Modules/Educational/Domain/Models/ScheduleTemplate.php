<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Domain\Traits\MustBeApproved;

class ScheduleTemplate extends Model
{
    use HasFactory, SoftDeletes, MustBeApproved;

    protected $connection = 'pgsql';
    protected $table = 'education.schedule_templates';

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
        'day_of_week' => 'integer',
        'allow_evaluator_types' => 'array',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function instructorProfile()
    {
        return $this->belongsTo(InstructorProfile::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function sessionType()
    {
        return $this->belongsTo(SessionType::class);
    }

    public function evaluationForm()
    {
        return $this->belongsTo(EvaluationForm::class);
    }

    protected static function booted()
    {
        static::deleting(function ($template) {
            // Delete all lectures associated with this template
            Lecture::where('schedule_template_id', $template->id)->delete();
        });
    }
}
