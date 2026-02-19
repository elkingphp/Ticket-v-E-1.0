<?php

namespace Modules\Core\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SystemHealthChecked
{
    use Dispatchable, SerializesModels;

    public array $healthData;

    public function __construct(array $healthData)
    {
        $this->healthData = $healthData;
    }
}