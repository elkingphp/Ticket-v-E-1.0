<?php

namespace Modules\Core\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;

class CoreDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ModuleActivationSeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            SecuritySettingsSeeder::class,
            NotificationThresholdsSeeder::class,
        ]);
    }
}