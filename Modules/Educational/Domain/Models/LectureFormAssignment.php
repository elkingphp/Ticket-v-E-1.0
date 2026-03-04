<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LectureFormAssignment — bridges a Lecture to an EvaluationForm.
 *
 * allow_evaluator_types is configuration ONLY.
 * EvaluationFormPolicy is the authoritative guard.
 *
 * completion_rate (computed, not stored):
 *   submitted / eligible_evaluators
 * where eligible_evaluators =
 *   actual registered trainees in the lecture's group
 *   + observer count derived from allow_evaluator_types configuration
 * NOT simply all lecture attendees.
 */
class LectureFormAssignment extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'education.lecture_form_assignments';

    protected $fillable = [
        'lecture_id',
        'form_id',
        'assigned_by',
        'assigned_at',
        'allow_evaluator_types',
        'is_active',
    ];

    protected $casts = [
        'allow_evaluator_types' => 'array',
        'is_active' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function allowsTrainees(): bool
    {
        return in_array('trainee', $this->allow_evaluator_types ?? [], true);
    }

    public function allowsObservers(): bool
    {
        return in_array('observer', $this->allow_evaluator_types ?? [], true);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(EvaluationForm::class, 'form_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Domain\Models\User::class, 'assigned_by');
    }
}
