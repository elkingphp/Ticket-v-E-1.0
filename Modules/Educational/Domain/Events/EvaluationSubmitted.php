<?php

namespace Modules\Educational\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Educational\Domain\Models\LectureEvaluation;

class EvaluationSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public LectureEvaluation $evaluation;

    /**
     * Create a new event instance.
     *
     * @param LectureEvaluation $evaluation
     */
    public function __construct(LectureEvaluation $evaluation)
    {
        $this->evaluation = $evaluation;
    }
}
