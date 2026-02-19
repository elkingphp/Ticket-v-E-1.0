<?php

namespace Modules\Core\Infrastructure\Console;

use Illuminate\Console\Command;
use Modules\Core\Domain\Interfaces\ModuleManagerInterface;

class SyncModulesCommand extends Command
{
    protected $signature = 'ermo:sync';
    protected $description = 'Sync filesystem modules with the ERMO database registry';

    public function handle(ModuleManagerInterface $moduleManager)
    {
        $this->info('Starting ERMO Module Sync...');

        try {
            $moduleManager->syncFromFilesystem();
            $this->success('Modules synced successfully.');

            $modules = $moduleManager->getSortedModules();
            $headers = ['Slug', 'Name', 'Status', 'Core', 'Version'];
            $data = [];

            foreach ($modules as $module) {
                $data[] = [
                    $module->slug,
                    $module->name,
                    $module->status,
                    $module->is_core ? 'Yes' : 'No',
                    $module->state_version
                ];
            }

            $this->table($headers, $data);

        }
        catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
        }
    }

    private function success(string $message)
    {
        $this->info('✅ ' . $message);
    }
}