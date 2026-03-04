<?php

namespace Modules\Educational\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Educational\Domain\Models\Lecture;

class LectureCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Lecture $lecture;

    /**
     * Create a new event instance.
     *
     * @param Lecture $lecture
     */
    public function __construct(Lecture $lecture)
    {
        $this->lecture = $lecture;
    }
}
