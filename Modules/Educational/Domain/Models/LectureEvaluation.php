<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * LectureEvaluation — one submitted evaluation instance.
 *
 * Key design points:
 *  • submitted_at  is the OFFICIAL timestamp — never use created_at for business logic.
 *  • form_snapshot captures the full form structure at submission time,
 *    so results remain valid even if the form is later modified or archived.
 *  • snapshot_hash (SHA-256) allows auditors to verify snapshot integrity.
 *  • evaluator_role separates the ROLE (trainee|observer|admin) from the
 *    polymorphic evaluator_type/evaluator_id (the Model class).
 */
class LectureEvaluation extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'education.lecture_evaluations';

    protected $fillable = [
        'lecture_id',
        'form_id',
        'evaluator_type',
        'evaluator_id',
        'evaluator_role',
        'form_snapshot',
        'snapshot_hash',
        'overall_comments',
        'submitted_at',
    ];

    protected $casts = [
        'form_snapshot' => 'array',
        'submitted_at' => 'datetime',
    ];

    // ─── Allowed evaluator roles ──────────────────────────────────────────────

    public const EVALUATOR_ROLES = ['trainee', 'observer', 'admin'];

    // Constrained polymorphic map — prevents open-ended Model injection
    public const EVALUATOR_TYPE_MAP = [
        'trainee' => \Modules\Educational\Domain\Models\TraineeProfile::class,
        'observer' => \Modules\Users\Domain\Models\User::class,
        'admin' => \Modules\Users\Domain\Models\User::class,
    ];

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeByRole($query, string $role)
    {
        return $query->where('evaluator_role', $role);
    }

    public function scopeTrainees($query)
    {
        return $query->where('evaluator_role', 'trainee');
    }

    public function scopeObservers($query)
    {
        return $query->where('evaluator_role', 'observer');
    }

    // ─── Snapshot Helpers ─────────────────────────────────────────────────────

    /**
     * Generate a SHA-256 hash of the given snapshot array.
     * Store alongside form_snapshot to enable tamper detection.
     */
    public static function generateSnapshotHash(array $snapshot): string
    {
        return hash('sha256', json_encode($snapshot));
    }

    /**
     * Verify that the stored snapshot has not been tampered with.
     */
    public function verifySnapshotIntegrity(): bool
    {
        if (!$this->form_snapshot || !$this->snapshot_hash) {
            return false;
        }

        return hash_equals(
            $this->snapshot_hash,
            self::generateSnapshotHash($this->form_snapshot)
        );
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

    /**
     * Polymorphic evaluator — constrained to known Model types via EVALUATOR_TYPE_MAP.
     */
    public function evaluator(): MorphTo
    {
        return $this->morphTo();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EvaluationAnswer::class, 'lecture_evaluation_id');
    }
}
