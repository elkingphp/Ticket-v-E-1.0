<?php

namespace Modules\Core\Application\Services;

use Modules\Core\Domain\Models\Module;
use Modules\Core\Domain\Interfaces\ModuleHealthContract;
use Modules\Core\Domain\Events\ERMO\{ModuleDegraded, ModuleRecovered};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class HealthOrchestratorService
{
    protected ModuleManagerService $moduleManager;

    public function __construct(ModuleManagerService $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Run health checks for a specific module and take action if needed.
     * Implements a Basic Circuit Breaker with Failure Ratio tracking and Atomic Trip Guard.
     */
    public function checkModule(Module $module, ModuleHealthContract $checker): array
    {
        $report = $checker->checks();
        $oldHealth = $module->health_status;
        $newHealth = $report['status'];
        $slug = $module->slug;

        $conf = config('ermo.resilience.circuit_breaker');
        $window = $conf['window_seconds'] ?? 60;

        $totalKey = "ermo:health:checks_total:{$slug}";
        $failureKey = "ermo:health:checks_failed:{$slug}";

        // 1. Increment Total Checks (Cross-store compatible TTL)
        if (!Cache::has($totalKey)) {
            Cache::put($totalKey, 1, $window);
            $total = 1;
        }
        else {
            $total = Cache::increment($totalKey);
        }

        if ($newHealth === 'healthy') {
            if ($oldHealth !== 'healthy') {
                $module->update(['health_status' => 'healthy']);
                ModuleRecovered::dispatch($module);
            }
            return $report;
        }

        // 2. Increment Failures (Cross-store compatible TTL)
        if (!Cache::has($failureKey)) {
            Cache::put($failureKey, 1, $window);
            $failures = 1;
        }
        else {
            $failures = Cache::increment($failureKey);
        }

        // 3. Circuit Breaker Logic (Ratio + Threshold)
        $ratio = $total > 0 ? $failures / $total : 0;

        if ($failures >= $conf['failure_threshold'] && $ratio >= $conf['failure_ratio']) {
            $this->tripCircuit($module, $report, $failures, $ratio);
        }
        else {
            if ($oldHealth !== $newHealth) {
                $module->update(['health_status' => $newHealth]);
                ModuleDegraded::dispatch($module, $report);
            }
        }

        return $report;
    }

    protected function tripCircuit(Module $module, array $report, int $failures, float $ratio)
    {
        $slug = $module->slug;
        $lockKey = "ermo:lock:trip:{$slug}";

        // Atomic Trip Guard: Only one process can trip the circuit per module at a time
        $lock = Cache::lock($lockKey, 10); // 10 second safety lock

        if ($lock->get()) {
            try {
                $module->refresh(); // Fresh state before decision
                $currentStatus = $module->status;

                Log::warning("ERMO: Circuit Tripped for {$slug}. Failures: {$failures}, Ratio: " . ($ratio * 100) . "%. Current Status: {$currentStatus}");

                if ($currentStatus === 'active') {
                    $this->moduleManager->transitionState($slug, 'degraded', "Circuit breaker trip (Ratio: {$ratio})");
                    $this->incrementTripMetric();
                }
                elseif ($currentStatus === 'degraded' && $report['blocking']) {
                    $this->moduleManager->transitionState($slug, 'maintenance', "Circuit breaker escalation (Critical Blocking Failure)");
                    $this->incrementTripMetric();
                }
            }
            finally {
                $lock->release();
            }
        }
    }

    protected function incrementTripMetric(): void
    {
        $key = 'ermo:metrics:circuit_trips_total';
        if (!Cache::has($key)) {
            Cache::forever($key, 1);
        }
        else {
            Cache::increment($key);
        }
    }
}