<?php

namespace Modules\Core\Application\Services\Dashboard\Metrics;

use Modules\Core\Application\Services\Dashboard\BaseMetricService;
use Modules\Users\Domain\Models\User;
use Spatie\Permission\Models\Role;

class UserMetricsService extends BaseMetricService
{
    protected function calculate(array $options): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_roles' => Role::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
        ];
    }
}