<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * EvaluationForm — the blueprint for a lecture supervision form.
 *
 * Lifecycle:
 *   draft ──→ published ──→ archived
 *
 * Rules:
 *   • Only draft forms can be edited or deleted.
 *   • A form cannot be published unless it has at least 1 question.
 *   • Deletion is only allowed for drafts with zero submitted evaluations.
 *   • Once published, the form is frozen — snapshot integrity is preserved.
 */
class EvaluationForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'education.evaluation_forms';

    protected $fillable = [
        'title',
        'type', // Kept for backwards compatibility
        'form_type_id', // Replaces type with dynamic EvaluationType
        'status',
        'description',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function formType()
    {
        return $this->belongsTo(EvaluationType::class, 'form_type_id');
    }

    // ─── Allowed enum values ──────────────────────────────────────────────────

    public const TYPES = [
        'lecture_feedback' => 'تغذية راجعة للمحاضرة',
        'course_evaluation' => 'تقييم المقرر',
        'instructor_evaluation' => 'تقييم المدرب',
        'general' => 'عام',
    ];

    public const STATUSES = [
        'draft' => 'مسودة',
        'published' => 'منشور',
        'archived' => 'مؤرشف',
    ];

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    // ─── Business Logic Guards ────────────────────────────────────────────────

    /**
     * Can this form's metadata or questions be edited?
     * Only allowed while in draft state.
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Can this form be soft-deleted?
     * Only allowed if it is a draft AND has no submitted evaluations.
     */
    public function canBeDeleted(): bool
    {
        return $this->status === 'draft'
            && $this->lectureEvaluations()->count() === 0;
    }

    /**
     * Publish this form.
     * Validates preconditions before flipping the status.
     *
     * @throws \RuntimeException
     */
    public function publish(): void
    {
        if ($this->status !== 'draft') {
            throw new \RuntimeException('يمكن نشر المسودات فقط.');
        }

        if ($this->questions()->count() === 0) {
            throw new \RuntimeException('لا يمكن نشر نموذج بدون أسئلة.');
        }

        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Archive a published form.
     *
     * @throws \RuntimeException
     */
    public function archive(): void
    {
        if ($this->status !== 'published') {
            throw new \RuntimeException('يمكن أرشفة النماذج المنشورة فقط.');
        }

        $this->update(['status' => 'archived']);
    }

    /**
     * Build a full snapshot of this form (structure + questions + options).
     * Called at the time of the FIRST submission for a given assignment.
     */
    public function buildSnapshot(): array
    {
        return [
            'form_id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'form_type_id' => $this->form_type_id,
            'description' => $this->description,
            'questions' => $this->questions()
                ->get()
                ->map(fn($q) => [
                    'id' => $q->id,
                    'question_text' => $q->question_text,
                    'type' => $q->type,
                    'options' => $q->options,
                    'is_required' => $q->is_required,
                    'order_index' => $q->order_index,
                ])
                ->toArray(),
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function questions(): HasMany
    {
        return $this->hasMany(EvaluationQuestion::class, 'form_id')
            ->orderBy('order_index');
    }

    public function lectureEvaluations(): HasMany
    {
        return $this->hasMany(LectureEvaluation::class, 'form_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LectureFormAssignment::class, 'form_id');
    }
}
