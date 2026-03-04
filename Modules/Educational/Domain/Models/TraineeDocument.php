<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class TraineeDocument extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'education.trainee_documents';

    protected $fillable = ['trainee_profile_id', 'name', 'file_path', 'file_type', 'file_size'];

    public function traineeProfile()
    {
        return $this->belongsTo(TraineeProfile::class);
    }
}
