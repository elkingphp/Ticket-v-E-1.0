<?php

namespace Modules\Users\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Users\Domain\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserService extends BaseService
{
    protected UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new user with enterprise fields.
     */
    public function createUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $data['joined_at'] = now();
        $data['status'] = $data['status'] ?? 'active';

        if ($data['status'] === 'active') {
            $data['activated_at'] = now();
        }

        return $this->repository->create($data);
    }

    /**
     * Block a user with a reason.
     */
    public function blockUser(int $userId, string $reason)
    {
        return $this->repository->updateStatus($userId, 'blocked', $reason);
    }

    /**
     * Activate a user.
     */
    public function activateUser(int $userId)
    {
        return $this->repository->updateStatus($userId, 'active');
    }

    /**
     * Toggle 2FA for a user.
     */
    public function toggle2FA(int $userId, bool $enabled)
    {
        return $this->repository->update($userId, [
            'two_factor_enabled' => $enabled
        ]);
    }
}