<?php

namespace Modules\Core\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Application\Services\ModuleManagerService;
use Modules\Core\Domain\Models\Module;

class ModuleActivationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manager = app(ModuleManagerService::class);

        // 1. Sync modules from filesystem
        $manager->syncFromFilesystem();

        // 2. Activate all core and main modules
        $modules = Module::all();
        foreach ($modules as $module) {
            $module->status = 'active';
            $module->is_core = in_array($module->slug, ['core', 'users']);
            $module->save();
        }

        // 3. Refresh cache
        $manager->refreshCache();

        $this->command->info('✅ All modules have been synchronized and activated.');
    }
}
