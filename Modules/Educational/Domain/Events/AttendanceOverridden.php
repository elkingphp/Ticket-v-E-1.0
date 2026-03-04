<?php

namespace Modules\Educational\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Educational\Domain\Models\Attendance;

class AttendanceOverridden
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Attendance $attendance;

    /**
     * Create a new event instance.
     *
     * @param Attendance $attendance
     */
    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }
}
