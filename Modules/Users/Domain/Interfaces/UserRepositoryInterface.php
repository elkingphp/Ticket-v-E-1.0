<?php

namespace Modules\Users\Domain\Interfaces;

use Modules\Core\Domain\Interfaces\BaseRepositoryInterface;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUsername(string $username);
    public function findByEmail(string $email);
    public function updateStatus(int $userId, string $status, ?string $reason = null);
    public function advancedSearch(array $filters);
    public function bulkUpdateStatus(array $ids, string $status);
    public function bulkDelete(array $ids);
}