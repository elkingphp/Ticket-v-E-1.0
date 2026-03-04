<?php

namespace Modules\Educational\Domain\Models;

use Modules\Users\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Domain\Traits\MustBeApproved;

use Modules\Core\Application\Traits\Auditable;

class TraineeProfile extends Model
{
    use HasFactory, MustBeApproved, Auditable;

    protected $connection = 'pgsql';
    protected $table = 'education.trainee_profiles';

    protected $guarded = ['id'];

    /**
     * Encryption casting ensures data is encrypted at rest automatically
     */
    protected $casts = [
        'national_id' => 'encrypted',
        'religion' => 'encrypted',
        'medical_notes' => 'encrypted',
        'passport_number' => 'encrypted',
        'date_of_birth' => 'date',
    ];

    /**
     * Define hidden fields.
     */
    protected $hidden = [
        'national_id',
        'religion',
        'medical_notes',
        'passport_number',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to retrieve sensitive attributes manually, bypassing the hidden array.
     * This ensures the developer must explicitly call this function, rather than
     * accidentally dumping it in an API response or log.
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

    public function emergencyContacts()
    {
        return $this->hasMany(TraineeEmergencyContact::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function documents()
    {
        return $this->hasMany(TraineeDocument::class);
    }
}
