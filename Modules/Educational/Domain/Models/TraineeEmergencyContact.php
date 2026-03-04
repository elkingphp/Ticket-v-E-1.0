<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class TraineeEmergencyContact extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'education.trainee_emergency_contacts';

    protected $guarded = ['id'];

    public function traineeProfile()
    {
        return $this->belongsTo(TraineeProfile::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
}
