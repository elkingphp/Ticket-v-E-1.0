<?php

namespace Modules\Core\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Nwidart\Modules\Module;

class ModuleBooted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Module $module;

    /**
     * Create a new event instance.
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }
}