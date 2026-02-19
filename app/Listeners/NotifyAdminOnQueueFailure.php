<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminOnQueueFailure
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(\Illuminate\Queue\Events\JobFailed $event): void
    {
        $jobName = $event->job->resolveName();

        // Prevent infinite loop if the alert itself fails
        if (str_contains($jobName, 'ThresholdExceededAlert') || str_contains($jobName, 'GenericAlert')) {
            return;
        }

        // Add a cooldown to prevent notification flood (max one alert every 10 minutes)
        $cooldownKey = 'alert:queue_failure_cooldown';
        if (\Illuminate\Support\Facades\Cache::has($cooldownKey)) {
            return;
        }

        $oneHourAgo = now()->subHour();
        $failedCount = \Illuminate\Support\Facades\DB::table('failed_jobs')
            ->where('failed_at', '>=', $oneHourAgo)
            ->count();

        if ($failedCount >= 5) { // Increased threshold to 5 to be less sensitive
            $admin = \Modules\Users\Domain\Models\User::role('super-admin')->first();
            if ($admin) {
                // Send alert notification
                $admin->notify(new \App\Notifications\ThresholdExceededAlert([
                    'title' => 'Queue Failure Alert',
                    'message' => "High failure rate detected: {$failedCount} jobs failed in the last hour.",
                    'priority' => 'critical',
                    'event_type' => 'queue_failure'
                ]));

                // Set cooldown
                \Illuminate\Support\Facades\Cache::put($cooldownKey, true, now()->addMinutes(10));

                \Illuminate\Support\Facades\Log::critical("ALERT: High queue failure rate detected! Count: {$failedCount}. Cooldown set for 10 minutes.");
            }
        }
    }
}