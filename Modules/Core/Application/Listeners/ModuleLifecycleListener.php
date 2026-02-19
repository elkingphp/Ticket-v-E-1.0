<?php

namespace Modules\Core\Application\Listeners;

use Modules\Core\Domain\Events\ERMO\ModuleActivated;
use Modules\Core\Domain\Events\ERMO\ModuleDisabled;
use Modules\Core\Domain\Events\ERMO\ModuleMaintenanceEnabled;
use Modules\Core\Domain\Events\ERMO\ModuleDegraded;
use Modules\Core\Domain\Events\ERMO\ModuleRecovered;
use Modules\Core\Application\Services\AuditLoggerService;
use Illuminate\Support\Facades\Log;

class ModuleLifecycleListener
{
    protected AuditLoggerService $auditLogger;

    public function __construct(AuditLoggerService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    public function handleModuleActivated(ModuleActivated $event): void
    {
        $this->logEvent($event->module, 'activated', 'info');
    }

    public function handleModuleDisabled(ModuleDisabled $event): void
    {
        $this->logEvent($event->module, 'disabled', 'warning');
    }

    public function handleModuleMaintenance(ModuleMaintenanceEnabled $event): void
    {
        $this->logEvent($event->module, 'maintenance_enabled', 'warning');
    }

    public function handleModuleDegraded(ModuleDegraded $event): void
    {
        $this->logEvent($event->module, 'degraded', 'critical', $event->healthReport);
    }

    public function handleModuleRecovered(ModuleRecovered $event): void
    {
        $this->logEvent($event->module, 'recovered', 'info');
    }

    protected function logEvent($module, string $event, string $level, array $details = []): void
    {
        $this->auditLogger->log(
            event: "module.{$event}",
            category: 'Modules',
            old: null,
            new: [
                'slug' => $module->slug,
                'name' => $module->name,
                'status' => $module->status,
                'version' => $module->version,
                'details' => $details
            ],
            level: $level
        );

        Log::info("ERMO Lifecycle Event: Module {$module->slug} transition to {$event}");
    }

    public function subscribe($events): array
    {
        return [
            ModuleActivated::class => 'handleModuleActivated',
            ModuleDisabled::class => 'handleModuleDisabled',
            ModuleMaintenanceEnabled::class => 'handleModuleMaintenance',
            ModuleDegraded::class => 'handleModuleDegraded',
            ModuleRecovered::class => 'handleModuleRecovered',
        ];
    }
}