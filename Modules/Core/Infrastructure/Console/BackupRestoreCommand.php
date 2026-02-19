<?php

namespace Modules\Core\Infrastructure\Console;

use Illuminate\Console\Command;
use Modules\Core\Domain\Interfaces\BackupServiceInterface;

class BackupRestoreCommand extends Command
{
    protected $signature = 'backup:restore-db {filename}';
    protected $description = 'Restore a database backup';

    public function handle(BackupServiceInterface $backupService)
    {
        $filename = $this->argument('filename');

        if ($this->confirm("هل أنت متأكد من استرجاع النسخة {$filename}؟ سيتم حذف البيانات الحالية!")) {
            $this->info("بدء عملية الاسترجاع...");

            try {
                $backupService->restoreBackup($filename);
                $this->info("تم استرجاع النسخة بنجاح.");
            }
            catch (\Exception $e) {
                $this->error("فشل الاسترجاع: " . $e->getMessage());
            }
        }
    }
}