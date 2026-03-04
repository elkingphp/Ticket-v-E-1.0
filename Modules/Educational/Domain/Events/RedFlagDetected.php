<?php

namespace Modules\Educational\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Educational\Domain\Models\LectureFormAssignment;

class RedFlagDetected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public LectureFormAssignment $assignment,
        public array $flaggedQuestions
    ) {
    }
}
