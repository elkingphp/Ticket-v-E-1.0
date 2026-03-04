<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Domain\Traits\MustBeApproved;

class Group extends Model
{
    use HasFactory, MustBeApproved;

    protected $connection = 'pgsql';
    protected $table = 'education.groups';

    protected $guarded = ['id'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class);
    }

    public function transferredToGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'transferred_to_group_id');
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }

    public function scheduleTemplates()
    {
        return $this->hasMany(ScheduleTemplate::class);
    }

    public function trainees()
    {
        return $this->hasMany(TraineeProfile::class);
    }
}
