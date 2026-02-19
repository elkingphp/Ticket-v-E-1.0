<?php

namespace Modules\Tickets\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;

class TicketsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        // Seed Statuses
        $statuses = [
            ['name' => 'Open', 'color' => 'primary', 'is_default' => true],
            ['name' => 'In Progress', 'color' => 'info', 'is_default' => false],
            ['name' => 'Resolved', 'color' => 'success', 'is_default' => false],
            ['name' => 'Closed', 'color' => 'secondary', 'is_default' => false],
        ];

        foreach ($statuses as $status) {
            \Modules\Tickets\Domain\Models\TicketStatus::firstOrCreate(['name' => $status['name']], $status);
        }

        // Seed Priorities
        $priorities = [
            ['name' => 'Low', 'color' => 'secondary', 'is_default' => false],
            ['name' => 'Normal', 'color' => 'success', 'is_default' => true],
            ['name' => 'High', 'color' => 'warning', 'is_default' => false],
            ['name' => 'Urgent', 'color' => 'danger', 'is_default' => false],
        ];

        foreach ($priorities as $priority) {
            \Modules\Tickets\Domain\Models\TicketPriority::firstOrCreate(['name' => $priority['name']], $priority);
        }

        $this->call(TicketEmailTemplateSeeder::class);
    }
}
