<?php

namespace Modules\Core\Application\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Domain\Models\Module;
use Modules\Core\Domain\Interfaces\ModuleManagerInterface;
use Illuminate\Support\Facades\{Cache, Log};

class AutoRecoveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(ModuleManagerInterface $manager): void
    {
        $degradedModules = Module::whereIn('status', ['degraded', 'maintenance'])->get();

        foreach ($degradedModules as $module) {
            $slug = $module->slug;

            // Check consecutive health passes
            $failures = (int)Cache::get("ermo:health_failures:{$slug}", 0);

            if ($failures === 0 && $module->health_status === 'healthy') {
                $consecutivePasses = (int)Cache::get("ermo:recovery_passes:{$slug}", 0) + 1;
                Cache::put("ermo:recovery_passes:{$slug}", $consecutivePasses, 300);

                if ($consecutivePasses >= 5) {
                    Log::info("ERMO Auto-Recovery: Module {$slug} passed health checks for 5 cycles. Attempting reactivation.");

                    try {
                        $manager->transitionState($slug, 'active', 'Automated recovery after stability detected.');
                        Cache::forget("ermo:recovery_passes:{$slug}");
                    }
                    catch (\Exception $e) {
                        Log::error("ERMO Auto-Recovery Failed for {$slug}: " . $e->getMessage());
                    }
                }
            }
            else {
                Cache::forget("ermo:recovery_passes:{$slug}");
            }
        }
    }
}