<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Modules\Core\Application\Traits\Auditable;

class JobProfile extends Model
{
    use SoftDeletes, Auditable;

    protected $connection = 'pgsql';
    protected $table = 'education.job_profiles';

    protected $fillable = ['name', 'code', 'status', 'track_id'];

    /**
     * Scope for active job profiles.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Relationship: Linked Track.
     */
    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Relationship: Training Companies offering this profile.
     */
    public function companies()
    {
        return $this->belongsToMany(
            TrainingCompany::class,
            'education.company_job_profiles',
            'job_profile_id',
            'company_id'
        );
    }

    /**
     * Relationship: Responsible users for this job profile.
     */
    public function responsibles()
    {
        return $this->belongsToMany(\Modules\Users\Domain\Models\User::class, 'education.job_profile_responsibles', 'job_profile_id', 'user_id')->withTimestamps();
    }
}
