<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:cleanup {--days=90 : The number of days to keep logs}';
    protected $description = 'Cleanup old activity and notification retry logs';

    public function handle()
    {
        $days = (int)$this->option('days');
        $date = now()->subDays($days);

        $this->info("Cleaning up logs older than {$days} days ({$date->toDateString()})...");

        // 1. Cleanup Activity Logs
        // We wrap in try-catch in case table doesn't exist yet
        try {
            $affectedActivity = \Illuminate\Support\Facades\DB::table('activity_log')->where('created_at', '<', $date)->delete();
            $this->line("- Deleted {$affectedActivity} old activity logs.");
        }
        catch (\Throwable $e) {
            $this->error("- Failed to clean activity_log: " . $e->getMessage());
            $affectedActivity = 0;
        }

        // 2. Cleanup Notification Retry Logs
        try {
            $affectedRetry = \Illuminate\Support\Facades\DB::table('notification_retry_logs')->where('created_at', '<', $date)->delete();
            $this->line("- Deleted {$affectedRetry} old notification retry logs.");
        }
        catch (\Throwable $e) {
            $this->error("- Failed to clean notification_retry_logs: " . $e->getMessage());
            $affectedRetry = 0;
        }

        \Illuminate\Support\Facades\Log::info("System Cleanup: Removed {$affectedActivity} activity logs and {$affectedRetry} retry logs older than {$days} days.");

        $this->info('Cleanup completed successfully.');
    }
}