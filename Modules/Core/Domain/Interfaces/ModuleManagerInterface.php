<?php

namespace Modules\Core\Domain\Interfaces;

interface ModuleManagerInterface
{
    public function getCachedStatuses(): array;
    public function refreshCache(): array;
    public function transitionState(string $slug, string $targetStatus, string $reason = ''): bool;
    public function shutdown(string $slug, bool $force = false): bool;
    public function getActiveRequestsCount(string $slug): int;
    public function syncFromFilesystem(): void;
    public function getSortedModules(): array;
}