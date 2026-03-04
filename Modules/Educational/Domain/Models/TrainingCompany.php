<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Domain\Traits\MustBeApproved;

use Modules\Core\Application\Traits\Auditable;

class TrainingCompany extends Model
{
    use HasFactory, SoftDeletes, MustBeApproved, Auditable;

    protected $connection = 'pgsql';
    protected $table = 'education.training_companies';

    protected $fillable = [
        'name',
        'registration_number',
        'contact_email',
        'website',
        'address',
        'logo_path',
        'logo_disk',
        'status'
    ];

    /**
     * Relationship: Job Profiles offered by the company.
     */
    public function jobProfiles()
    {
        return $this->belongsToMany(
            JobProfile::class,
            'education.company_job_profiles',
            'company_id',
            'job_profile_id'
        );
    }

    /**
     * Virtual Relationship: Tracks associated with the company via its job profiles.
     */
    public function getTracksAttribute()
    {
        return Track::whereHas('jobProfiles', function ($q) {
            $q->whereIn('education.job_profiles.id', $this->jobProfiles()->pluck('education.job_profiles.id'));
        })->get();
    }
}
