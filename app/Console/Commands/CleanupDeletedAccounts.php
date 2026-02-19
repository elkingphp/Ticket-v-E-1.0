<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupDeletedAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-deleted-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete accounts scheduled for deletion more than 14 days ago';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \Modules\Users\Domain\Models\User::where('scheduled_for_deletion_at', '<=', now())->get();

        foreach ($users as $user) {
            $this->info("Permanently deleting user: {$user->email}");
            $user->delete(); // This should be a real delete, not soft delete if that was enabled.
        }

        $this->info('Cleanup complete.');
    }
}