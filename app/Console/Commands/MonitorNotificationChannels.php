<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Application\Services\NotificationMonitor;

class MonitorNotificationChannels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:monitor {--detailed : Show detailed report}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor the health of notification channels';

    protected NotificationMonitor $monitor;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationMonitor $monitor)
    {
        parent::__construct();
        $this->monitor = $monitor;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking notification channels health...');
        $this->newLine();
        
        if ($this->option('detailed')) {
            $report = $this->monitor->getDetailedReport();
            $this->displayDetailedReport($report);
        } else {
            $health = $this->monitor->checkChannelHealth();
            $this->displaySimpleReport($health);
        }
        
        return 0;
    }
    
    /**
     * Display simple health report
     */
    protected function displaySimpleReport(array $health)
    {
        $rows = [];
        
        foreach ($health as $channel => $status) {
            $statusIcon = match($status['status']) {
                'healthy' => '✅',
                'degraded' => '⚠️',
                'failed' => '❌',
                default => '❓'
            };
            
            $latency = $status['latency_ms'] !== null 
                ? number_format($status['latency_ms'], 2) . 'ms' 
                : 'N/A';
            
            $rows[] = [
                $statusIcon . ' ' . ucfirst($channel),
                ucfirst($status['status']),
                $latency,
                $status['message']
            ];
        }
        
        $this->table(
            ['Channel', 'Status', 'Latency', 'Message'],
            $rows
        );
        
        // Overall status
        $allHealthy = $this->monitor->areAllChannelsHealthy();
        $this->newLine();
        
        if ($allHealthy) {
            $this->info('✅ All notification channels are healthy!');
        } else {
            $failedChannels = $this->monitor->getFailedChannels();
            $this->warn('⚠️  Some channels are not healthy:');
            foreach ($failedChannels as $channel => $status) {
                $this->error("  - {$channel}: {$status['message']}");
            }
        }
    }
    
    /**
     * Display detailed health report
     */
    protected function displayDetailedReport(array $report)
    {
        $this->info("📊 Detailed Health Report");
        $this->info("Timestamp: {$report['timestamp']}");
        $this->info("Overall Status: " . strtoupper($report['overall_status']));
        $this->info("Average Latency: {$report['average_latency_ms']}ms");
        $this->info("Failed Channels: {$report['failed_count']}");
        $this->newLine();
        
        $this->displaySimpleReport($report['channels']);
        
        // Recommendations
        if ($report['failed_count'] > 0) {
            $this->newLine();
            $this->warn('📋 Recommendations:');
            
            foreach ($report['channels'] as $channel => $status) {
                if ($status['status'] === 'failed') {
                    $recommendation = $this->getRecommendation($channel, $status);
                    $this->line("  • {$channel}: {$recommendation}");
                }
            }
        }
    }
    
    /**
     * Get recommendation for failed channel
     */
    protected function getRecommendation(string $channel, array $status): string
    {
        return match($channel) {
            'database' => 'Check database connection and credentials',
            'mail' => 'Verify mail server configuration in .env',
            'redis' => 'Ensure Redis server is running',
            'reverb' => 'Start Reverb server with: php artisan reverb:start',
            default => 'Check channel configuration'
        };
    }
}