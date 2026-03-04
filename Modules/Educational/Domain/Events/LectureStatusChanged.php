<?php

namespace Modules\Educational\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Educational\Domain\Models\Lecture;

class LectureStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Lecture $lecture;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new event instance.
     *
     * @param Lecture $lecture
     * @param string $oldStatus
     * @param string $newStatus
     */
    public function __construct(Lecture $lecture, string $oldStatus, string $newStatus)
    {
        $this->lecture = $lecture;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
