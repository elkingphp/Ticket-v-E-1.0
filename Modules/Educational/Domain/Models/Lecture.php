<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Domain\Traits\MustBeApproved;

class Lecture extends Model
{
    use HasFactory, MustBeApproved, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'education.lectures';

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
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

    public function supervisor()
    {
        return $this->belongsTo(\Modules\Users\Domain\Models\User::class, 'supervisor_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function scheduleTemplate()
    {
        return $this->belongsTo(ScheduleTemplate::class);
    }

    public function sessionType()
    {
        return $this->belongsTo(SessionType::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function formAssignments()
    {
        return $this->hasMany(LectureFormAssignment::class);
    }

    public function evaluations()
    {
        return $this->hasMany(LectureEvaluation::class);
    }

    /**
     * Count eligible trainee evaluators:
     * trainees registered in the same group as this lecture.
     * NOT all system trainees — only those enrolled in the lecture's group.
     */
    public function getEligibleTraineesCountAttribute(): int
    {
        if (!$this->group_id)
            return 0;
        return TraineeProfile::where('group_id', $this->group_id)->count();
    }
}
