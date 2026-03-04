<?php

namespace Modules\Educational\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationAnswer extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'education.evaluation_answers';

    protected $fillable = [
        'lecture_evaluation_id',
        'question_id',
        'answer_rating',
        'answer_value',
    ];

    protected $casts = [
        'answer_rating' => 'integer',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(LectureEvaluation::class, 'lecture_evaluation_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EvaluationQuestion::class, 'question_id');
    }
}
