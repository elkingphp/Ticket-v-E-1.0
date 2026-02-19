<?php

namespace Modules\Core\Domain\Interfaces;

interface BackupServiceInterface
{
    public function createBackup(): bool;
    public function restoreBackup(string $fileName): bool;
    public function getBackups(): array;
}