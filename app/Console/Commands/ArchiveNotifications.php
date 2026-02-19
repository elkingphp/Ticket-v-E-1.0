<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:archive {--days=30 : Number of days to keep notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive old read notifications to the archive table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int)$this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Archiving notifications older than {$days} days (before {$cutoffDate->toDateString()})...");

        // Get notifications to archive (read notifications older than cutoff date)
        $notificationsToArchive = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('created_at', '<', $cutoffDate)
            ->get();

        if ($notificationsToArchive->isEmpty()) {
            $this->info('No notifications to archive.');
            return 0;
        }

        $count = $notificationsToArchive->count();
        $this->info("Found {$count} notifications to archive.");

        // Start transaction
        DB::beginTransaction();

        try {
            // Insert into archive table
            $archived = 0;
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($notificationsToArchive as $notification) {
                DB::table('notifications_archive')->insert([
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'notifiable_type' => $notification->notifiable_type,
                    'notifiable_id' => $notification->notifiable_id,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'archived_at' => now(),
                ]);

                $archived++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            // Delete from main table
            $deleted = DB::table('notifications')
                ->whereNotNull('read_at')
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            DB::commit();

            $this->info("✅ Successfully archived {$archived} notifications.");
            $this->info("✅ Deleted {$deleted} notifications from main table.");

            // Show statistics
            $this->showStatistics();

            return 0;
        }
        catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error archiving notifications: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Show notification statistics
     */
    protected function showStatistics()
    {
        $this->newLine();
        $this->info('📊 Notification Statistics:');

        $activeCount = DB::table('notifications')->count();
        $archivedCount = DB::table('notifications_archive')->count();
        $unreadCount = DB::table('notifications')->whereNull('read_at')->count();

        $this->table(
        ['Metric', 'Count'],
        [
            ['Active Notifications', $activeCount],
            ['Unread Notifications', $unreadCount],
            ['Archived Notifications', $archivedCount],
            ['Total', $activeCount + $archivedCount],
        ]
        );
    }
}