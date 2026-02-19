<?php

namespace Modules\Core\Application\Services;

use Modules\Core\Domain\Interfaces\BackupServiceInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Exception;

class BackupService extends BaseService implements BackupServiceInterface
{
    protected string $disk = 'local';
    protected string $backupFolder = 'laravel-backup';

    public function __construct()
    {
        $this->backupFolder = config('backup.backup.name', 'laravel-backup');
    }

    /**
     * Create a new backup.
     */
    public function createBackup(): bool
    {
        $exitCode = Artisan::call('backup:run', ['--only-db' => true]);
        return $exitCode === 0;
    }

    /**
     * Get list of all backups.
     */
    public function getBackups(): array
    {
        $files = Storage::disk($this->disk)->allFiles($this->backupFolder);

        return array_map(function ($file) {
            return [
                'name' => basename($file),
                'path' => $file,
                'size' => Storage::disk($this->disk)->size($file),
                'date' => date('Y-m-d H:i:s', Storage::disk($this->disk)->lastModified($file)),
            ];
        }, $files);
    }

    /**
     * Restore a specific backup.
     */
    public function restoreBackup(string $fileName): bool
    {
        $filePath = $this->backupFolder . '/' . $fileName;

        if (!Storage::disk($this->disk)->exists($filePath)) {
            throw new Exception("ملف النسخة الاحتياطية غير موجود.");
        }

        try {
            // 1. Enter Maintenance Mode
            Artisan::call('down', [
                '--refresh' => 15,
                '--retry' => 60,
                '--secret' => 'restore-secret-123'
            ]);

            // 2. Create an automatic backup before restoring
            Artisan::call('backup:run', ['--only-db' => true]);

            // 3. Perform Restore
            $this->executeRestore($filePath);

            // 4. Exit Maintenance Mode
            Artisan::call('up');

            return true;
        }
        catch (Exception $e) {
            Artisan::call('up');
            throw $e;
        }
    }

    /**
     * Logic to extract and import the backup into PostgreSQL.
     */
    protected function executeRestore(string $zipPath): void
    {
        $fullZipPath = Storage::disk($this->disk)->path($zipPath);
        $tempDir = storage_path('app/temp-restore');

        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($fullZipPath) === TRUE) {
            $zip->extractTo($tempDir);
            $zip->close();
        }
        else {
            throw new Exception("فشل في فتح ملف الـ ZIP.");
        }

        // Find the SQL file in the extracted directory
        $sqlFiles = File::allFiles($tempDir . '/db-dumps');
        if (empty($sqlFiles)) {
            $sqlFiles = File::glob($tempDir . '/*.sql');
        }

        if (empty($sqlFiles)) {
            File::deleteDirectory($tempDir);
            throw new Exception("لا يوجد ملف SQL داخل النسخة الاحتياطية.");
        }

        $sqlFile = $sqlFiles[0]->getRealPath();

        // Database connection details
        $config = config('database.connections.' . config('database.default'));

        // Command to restore PostgreSQL
        // Assuming psql is available on the system
        $password = $config['password'];
        $dbName = $config['database'];
        $user = $config['username'];
        $host = $config['host'];
        $port = $config['port'];

        // DROP and CREATE database or just import?
        // Safe way: clear current tables and import
        $this->clearDatabase();

        $command = sprintf(
            'PGPASSWORD=%s psql -h %s -p %s -U %s %s < %s',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($dbName),
            escapeshellarg($sqlFile)
        );

        exec($command, $output, $returnVar);

        File::deleteDirectory($tempDir);

        if ($returnVar !== 0) {
            throw new Exception("فشل في استرجاع قاعدة البيانات: " . implode("\n", $output));
        }
    }

    /**
     * Clear all tables from the current database to ensure a clean restore.
     */
    protected function clearDatabase(): void
    {
        $database = config('database.connections.pgsql.database');

        // Disable foreign key checks for PostgreSQL
        DB::statement('SET session_replication_role = "replica";');

        $tables = DB::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'");

        foreach ($tables as $table) {
            DB::statement("DROP TABLE IF EXISTS public.{$table->tablename} CASCADE");
        }

        DB::statement('SET session_replication_role = "origin";');
    }
}