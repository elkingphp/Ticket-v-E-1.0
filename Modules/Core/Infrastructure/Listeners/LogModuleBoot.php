<?php

namespace Modules\Core\Infrastructure\Listeners;

use Modules\Core\Domain\Events\ModuleBooted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogModuleBoot implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ModuleBooted $event): void
    {
        Log::info("Hook Executed: Module {$event->module->getName()} has been booted.");
    }
}