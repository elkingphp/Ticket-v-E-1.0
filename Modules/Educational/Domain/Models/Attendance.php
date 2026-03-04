<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Domain\Traits\MustBeApproved;

class Attendance extends Model
{
    use HasFactory, MustBeApproved;

    protected $connection = 'pgsql';
    protected $table = 'education.attendances';

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'locked_at' => 'datetime',
    ];

    /**
     * Check if the attendance record is locked for modifications.
     */
    public function isLocked(): bool
    {
        if (!is_null($this->locked_at)) {
            return true;
        }

        $settings = app(\Modules\Educational\Application\Services\EducationalSettings::class);
        $lockHours = $settings->attendanceLockHours();

        if ($lockHours < 0) {
            return false;
        }

        if ($this->relationLoaded('lecture') && $this->lecture) {
            return now()->isAfter($this->lecture->ends_at->copy()->addHours($lockHours));
        }

        // Fallback or fetch from DB if not loaded
        $lecture = $this->lecture()->first();
        if ($lecture && $lecture->ends_at) {
            return now()->isAfter($lecture->ends_at->copy()->addHours($lockHours));
        }

        return false;
    }

    /**
     * Overrides MustBeApproved method to conditionally require approval
     */
    public function requiresApproval(): bool
    {
        // Require approval ONLY if the attendance has been initially locked by the instructor/system
        return $this->isLocked();
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function traineeProfile(): BelongsTo
    {
        return $this->belongsTo(TraineeProfile::class);
    }
}
