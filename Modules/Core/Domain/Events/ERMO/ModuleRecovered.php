<?php

namespace Modules\Core\Domain\Events\ERMO;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Domain\Models\Module;

class ModuleRecovered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Module $module)
    {
    }
}