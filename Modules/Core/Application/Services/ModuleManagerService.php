<?php

namespace Modules\Core\Application\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Domain\Models\Module;
use Modules\Core\Domain\Events\ERMO\{ModuleActivated, ModuleDisabled, ModuleMaintenanceEnabled};
use Nwidart\Modules\Facades\Module as NwidartModule;

use Illuminate\Support\Str;
use Modules\Core\Domain\Interfaces\ModuleManagerInterface;

class ModuleManagerService implements ModuleManagerInterface
{
    protected const CACHE_KEY = 'ermo_modules_status';
    protected const VERSION_KEY = 'ermo_state_version';

    /**
     * Get the consolidated status of all modules from cache.
     */
    public function getCachedStatuses(): array
    {
        $cachedVersion = Cache::get(self::VERSION_KEY);
        $dbVersion = DB::table('modules')->max('state_version') ?? 0;

        if (!$cachedVersion || $cachedVersion < $dbVersion) {
            return $this->refreshCache();
        }

        return Cache::get(self::CACHE_KEY, []);
    }

    /**
     * Refresh the runtime module cache.
     */
    public function refreshCache(): array
    {
        $modules = Module::all()->mapWithKeys(function ($module) {
            return [
                $module->slug => [
                    'status' => $module->status,
                    'version' => $module->state_version,
                    'is_core' => $module->is_core,
                    'flags' => $module->feature_flags,
                    'max_concurrent' => $module->max_concurrent_requests
                ]
            ];
        })->toArray();

        $maxVersion = DB::table('modules')->max('state_version') ?? 1;

        Cache::forever(self::CACHE_KEY, $modules);
        Cache::forever(self::VERSION_KEY, $maxVersion);

        return $modules;
    }

    /**
     * Atomic transition of module state.
     */
    public function transitionState(string $slug, string $targetStatus, string $reason = ''): bool
    {
        return DB::transaction(function () use ($slug, $targetStatus, $reason) {
            // High-precision pessimistic lock
            /** @var Module $module */
            $module = Module::where('slug', $slug)->lockForUpdate()->firstOrFail();

            if ($module->is_core && in_array($targetStatus, ['disabled', 'maintenance'])) {
                throw new \Exception("Core module {$slug} cannot be disabled or put in maintenance.");
            }

            // Dependency Safeguard
            if ($targetStatus === 'disabled') {
                $this->verifyNoDependentsActive($module);
            }

            if ($targetStatus === 'active') {
                $this->verifyDependenciesActive($module);
            }

            $oldStatus = $module->status;
            $module->transitionTo($targetStatus);

            // Log to Audit Log
            app(\Modules\Core\Application\Services\AuditLoggerService::class)->log(
                "module.transition",
                "Modules",
                ['slug' => $slug, 'status' => $oldStatus],
                ['slug' => $slug, 'status' => $targetStatus, 'reason' => $reason],
                'info'
            );

            // Sync with Nwidart if applicable
            $this->syncWithNwidart($slug, $targetStatus);

            $this->refreshCache();
            $this->dispatchEvent($module, $targetStatus);

            // Broadcast real-time update
            broadcast(new \Modules\Core\Infrastructure\Events\ERMOClusterUpdated('state_transition', [
                'slug' => $slug,
                'status' => $targetStatus,
                'version' => $module->state_version
            ]));

            return true;
        });
    }

    /**
     * Gracefully shutdown a module.
     */
    public function shutdown(string $slug, bool $force = false): bool
    {
        $activeRequests = $this->getActiveRequestsCount($slug);

        if ($activeRequests > 0 && !$force) {
            // First transition to maintenance to warn users
            $this->transitionState($slug, 'maintenance', "Graceful shutdown initiated. Active requests: {$activeRequests}");
            return false;
        }

        return $this->transitionState($slug, 'disabled', "Shutdown finalized.");
    }

    /**
     * Sync with filesystem modules status.
     */
    public function syncFromFilesystem(): void
    {
        $nModules = NwidartModule::all();

        DB::transaction(function () use ($nModules) {
            foreach ($nModules as $nModule) {
                $slug = $nModule->getLowerName();
                $module = Module::where('slug', $slug)->first();

                if (!$module) {
                    $module = new Module();
                    $module->id = (string) Str::uuid();
                    $module->slug = $slug;
                }

                $module->fill([
                    'name' => $nModule->getName(),
                    'version' => $nModule->get('version', '1.0.0'),
                    'priority' => $nModule->get('priority', 0),
                    'metadata' => [
                        'description' => $nModule->getDescription(),
                        'path' => $nModule->getPath(),
                    ]
                ]);

                $module->save();
            }
        });

        $this->refreshCache();
    }

    protected function syncWithNwidart(string $slug, string $status): void
    {
        $nModule = NwidartModule::find($slug);
        if (!$nModule)
            return;

        if ($status === 'active') {
            $nModule->enable();
        } elseif ($status === 'disabled') {
            $nModule->disable();
        }
    }

    protected function verifyNoDependentsActive(Module $module): void
    {
        $activeDependents = $module->dependents()
            ->whereIn('status', ['active', 'maintenance'])
            ->get();

        if ($activeDependents->isNotEmpty()) {
            $names = $activeDependents->pluck('name')->implode(', ');
            throw new \Exception("Cannot disable {$module->name}. The following active modules depend on it: {$names}");
        }
    }

    protected function verifyDependenciesActive(Module $module): void
    {
        $inactiveDeps = $module->dependencies()
            ->whereNotIn('status', ['active', 'maintenance'])
            ->get();

        if ($inactiveDeps->isNotEmpty()) {
            $names = $inactiveDeps->pluck('name')->implode(', ');
            throw new \Exception("Cannot activate {$module->name}. It depends on the following inactive modules: {$names}");
        }
    }

    protected function dispatchEvent(Module $module, string $status): void
    {
        match ($status) {
            'active' => ModuleActivated::dispatch($module),
            'disabled' => ModuleDisabled::dispatch($module),
            'maintenance' => ModuleMaintenanceEnabled::dispatch($module),
            default => null
        };
    }

    /**
     * Get the count of active requests interacting with a specific module.
     */
    public function getActiveRequestsCount(string $slug): int
    {
        return (int) Cache::get("ermo:active_requests:{$slug}", 0);
    }

    /**
     * Get topological sort order of modules.
     */
    public function getSortedModules(): array
    {
        $modules = Module::with('dependencies')->get();
        $sorted = [];
        $visited = [];
        $temp = [];

        foreach ($modules as $module) {
            $this->topologicalSort($module, $visited, $temp, $sorted, $modules->keyBy('id')->all());
        }

        return $sorted;
    }

    protected function topologicalSort($module, &$visited, &$temp, &$sorted, $allModules): void
    {
        if (isset($temp[$module->id])) {
            throw new \Exception("Circular dependency detected involving module: {$module->name}");
        }

        if (!isset($visited[$module->id])) {
            $temp[$module->id] = true;

            foreach ($module->dependencies as $dependency) {
                if (isset($allModules[$dependency->id])) {
                    $this->topologicalSort($allModules[$dependency->id], $visited, $temp, $sorted, $allModules);
                }
            }

            unset($temp[$module->id]);
            $visited[$module->id] = true;
            $sorted[] = $module;
        }
    }
}