<?php

namespace Modules\Educational\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;

class EducationalDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            StandardLookupsSeeder::class,
        ]);
    }
}
