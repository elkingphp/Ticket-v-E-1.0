<?php

namespace Modules\Users\Infrastructure\Repositories;

use Modules\Core\Infrastructure\Repositories\BaseRepository;
use Modules\Users\Domain\Interfaces\UserRepositoryInterface;
use Modules\Users\Domain\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByUsername(string $username)
    {
        return $this->model->where('username', $username)->first();
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function updateStatus(int $userId, string $status, ?string $reason = null)
    {
        $user = $this->find($userId);
        if (!$user)
            return false;

        $data = [
            'status' => $status,
            'status_reason' => $reason,
        ];

        if ($status === 'blocked') {
            $data['blocked_at'] = now();
        } elseif ($status === 'active') {
            $data['activated_at'] = $user->activated_at ?? now();
            $data['blocked_at'] = null;
        }

        return $user->update($data);
    }

    public function advancedSearch(array $filters)
    {
        $query = $this->model->newQuery();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['role'])) {
            try {
                $query->role($filters['role']);
            } catch (\Exception $e) {
                // Ignore if role invalid
            }
        }

        return $query->with('roles')->latest()->paginate($filters['per_page'] ?? 10)->withQueryString();
    }

    public function bulkUpdateStatus(array $ids, string $status)
    {
        $data = ['status' => $status];
        if ($status == 'blocked') {
            $data['blocked_at'] = now();
        } elseif ($status == 'active') {
            $data['blocked_at'] = null;
            $data['activated_at'] = now();
        }

        return $this->model->whereIn('id', $ids)->update($data);
    }

    public function bulkDelete(array $ids)
    {
        return $this->model->destroy($ids);
    }
}