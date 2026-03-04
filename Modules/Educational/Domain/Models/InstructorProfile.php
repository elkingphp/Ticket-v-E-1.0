<?php

namespace Modules\Educational\Domain\Models;

use Modules\Users\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Domain\Traits\MustBeApproved;

use Modules\Core\Application\Traits\Auditable;

class InstructorProfile extends Model
{
    use HasFactory, MustBeApproved, Auditable;

    protected $connection = 'pgsql';
    protected $table = 'education.instructor_profiles';

    protected $guarded = ['id'];

    /**
     * Encryption casting ensures data is encrypted at rest automatically
     */
    protected $casts = [
        'national_id' => 'encrypted',
        'passport_number' => 'encrypted',
        'date_of_birth' => 'date',
    ];

    /**
     * Define hidden fields.
     */
    protected $hidden = [
        'national_id',
        'passport_number',
    ];

    /**
     * Helper to retrieve sensitive attributes manually, bypassing the hidden array.
     */
    public function getSensitiveData(string $key)
    {
        if (in_array($key, $this->hidden)) {
            return $this->{$key} ?? null;
        }
        return null;
    }

    /**
     * Proper accessor for unencrypted viewing, which bypasses hidden if explicitly requested
     */
    public function revealSensitive(string $key)
    {
        return $this->{$key};
    }

    /**
     * Relationship: Training companies assigned to this instructor.
     */
    public function companies()
    {
        return $this->belongsToMany(
            TrainingCompany::class,
            'education.instructor_company_assignments',
            'instructor_profile_id',
            'company_id'
        )->withPivot(['job_profile_id', 'status', 'assigned_at'])->withTimestamps();
    }

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the governorate
     */
    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    /**
     * Get the track (specialization)
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Relationship: Session types this instructor can teach
     */
    public function sessionTypes()
    {
        return $this->belongsToMany(
            SessionType::class,
            'education.instructor_session_types',
            'instructor_profile_id',
            'session_type_id'
        )->withTimestamps();
    }
}
