<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Application\Services\FallbackLogger;

class RetryFailedNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:retry-failed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry sending failed notifications';

    protected FallbackLogger $fallbackLogger;

    /**
     * Create a new command instance.
     */
    public function __construct(FallbackLogger $fallbackLogger)
    {
        parent::__construct();
        $this->fallbackLogger = $fallbackLogger;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Retrying failed notifications...');

        // Get statistics before retry
        $statsBefore = $this->fallbackLogger->getFailedNotificationsStats();
        $this->info("Found {$statsBefore['total']} failed notifications.");

        if ($statsBefore['total'] === 0) {
            $this->info('✅ No failed notifications to retry.');
            return 0;
        }

        // Show breakdown by type
        if (!empty($statsBefore['by_type'])) {
            $this->info('Breakdown by type:');
            foreach ($statsBefore['by_type'] as $type => $count) {
                $this->line("  - {$type}: {$count}");
            }
        }

        // Retry failed notifications
        $successCount = $this->fallbackLogger->retryFailedNotifications();

        // Get statistics after retry
        $statsAfter = $this->fallbackLogger->getFailedNotificationsStats();

        $this->newLine();
        $this->info("✅ Successfully retried {$successCount} notifications.");
        $this->info("❌ {$statsAfter['total']} notifications still failed.");

        if ($statsAfter['max_retries'] > 0) {
            $this->warn("⚠️  {$statsAfter['max_retries']} notifications have reached max retry attempts.");
        }

        return 0;
    }
}