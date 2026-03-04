<?php

namespace Modules\Educational\Domain\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Educational\Domain\Models\ScheduleTemplate;
use Carbon\Carbon;

class SchedulingConflictOccurred
{
    use SerializesModels;

    public $template;
    public $startsAt;
    public $endsAt;
    public $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(ScheduleTemplate $template, Carbon $startsAt, Carbon $endsAt, string $reason)
    {
        $this->template = $template;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->reason = $reason;
    }
}
