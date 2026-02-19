<?php

namespace Modules\Core\Infrastructure\Console;

use Illuminate\Console\Command;
use Modules\Core\Domain\Models\AuditLog;
use Modules\Core\Application\Services\SettingsService;

class AuditCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old audit logs based on retention settings';

    /**
     * Execute the console command.
     */
    public function handle(SettingsService $settings)
    {
        $days = $settings->get('audit_retention_days', 30);
        $date = now()->subDays($days);

        $count = AuditLog::where('created_at', '<', $date)->delete();

        $this->info("Deleted {$count} old audit logs (retention: {$days} days).");
    }
}