<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Notification Maintenance Schedules
Schedule::command('notifications:archive --days=30')
    ->daily()
    ->at('02:00')
    ->description('Archive old read notifications');

Schedule::command('notifications:retry-failed')
    ->hourly()
    ->description('Retry failed notifications');

Schedule::command('notifications:monitor')
    ->everyFiveMinutes()
    ->description('Monitor notification channels health');

Schedule::command('logs:cleanup --days=90')
    ->weekly()
    ->at('03:00')
    ->description('Cleanup old logs to maintain database health');

Schedule::call(function () {
    $failedCount = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
    if ($failedCount > 10) {
        \Illuminate\Support\Facades\Log::critical("ALERT: Failed Jobs Threshold Exceeded! Current Count: {$failedCount}");
    }
})->hourly()->description('Monitor Failed Jobs Threshold');

Schedule::command('app:cleanup-deleted-accounts')
    ->daily()
    ->at('04:00')
    ->description('Permanently delete accounts scheduled for deletion');

// ERMO Resilience Schedules
Schedule::job(new \Modules\Core\Application\Jobs\AutoRecoveryJob)
    ->everyMinute()
    ->description('ERMO: Attempt auto-recovery for stable degraded modules');
