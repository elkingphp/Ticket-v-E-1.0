<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EvaluationQuestion — a single question within an EvaluationForm.
 *
 * Soft-deleted only: questions that have existing answers must never be
 * physically removed — soft-delete preserves historical answer integrity.
 *
 * Question types:
 *   rating_1_to_5  → integer 1–5, stored in answer_rating
 *   text           → free text,   stored in answer_value
 *   boolean        → نعم/لا,       stored in answer_value ('1'/'0')
 *   multiple_choice→ single pick,  stored in answer_value (one of options[] values)
 */
class EvaluationQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'education.evaluation_questions';

    protected $fillable = [
        'form_id',
        'question_text',
        'type',
        'options',
        'order_index',
        'is_required',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'order_index' => 'integer',
    ];

    public const TYPES = [
        'rating_1_to_5' => '⭐ تقييم 1-5',
        'text' => '📝 نص حر',
        'boolean' => '✅ نعم / لا',
        'multiple_choice' => '🔘 اختيار متعدد',
    ];

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Safely returns the options array.
     * For non-multiple_choice questions this will be an empty array.
     */
    public function getOptionsArray(): array
    {
        if ($this->type !== 'multiple_choice') {
            return [];
        }

        return is_array($this->options) ? $this->options : [];
    }

    /**
     * Validate that a given answer value is within the defined options.
     * Used during evaluation submission validation.
     */
    public function isValidChoice(string $value): bool
    {
        return in_array($value, $this->getOptionsArray(), true);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function form(): BelongsTo
    {
        return $this->belongsTo(EvaluationForm::class, 'form_id');
    }
}
