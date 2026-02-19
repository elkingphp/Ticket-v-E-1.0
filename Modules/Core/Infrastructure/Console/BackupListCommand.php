<?php

namespace Modules\Core\Infrastructure\Console;

use Illuminate\Console\Command;
use Modules\Core\Domain\Interfaces\BackupServiceInterface;

class BackupListCommand extends Command
{
    protected $signature = 'backup:list-local';
    protected $description = 'List all local backups';

    public function handle(BackupServiceInterface $backupService)
    {
        $backups = $backupService->getBackups();

        if (empty($backups)) {
            $this->info("لا توجد نسخ احتياطية.");
            return;
        }

        $this->table(['Name', 'Size', 'Date'], array_map(function ($b) {
            return [$b['name'], round($b['size'] / 1024 / 1024, 2) . ' MB', $b['date']];
        }, $backups));
    }
}