<?php

namespace App\Services;

use Modules\Users\Domain\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    /**
     * Calculate Profile Completion Percentage for all users.
     * Uses chunking to avoid memory issues and caching for performance.
     */
    public function getGlobalProfileCompletion(): float
    {
        return Cache::remember('global_profile_completion', 3600, function () {
            $totalUsers = User::count();
            if ($totalUsers === 0)
                return 0.0;

            $totalCompletion = 0;

            User::query()->select(['id', 'first_name', 'last_name', 'email', 'avatar', 'phone'])
                ->chunkById(1000, function ($users) use (&$totalCompletion) {
                foreach ($users as $user) {
                    $fields = ['first_name', 'last_name', 'email', 'avatar', 'phone'];
                    $filled = 0;
                    foreach ($fields as $field) {
                        if (!empty($user->$field))
                            $filled++;
                    }
                    $totalCompletion += ($filled / count($fields)) * 100;
                }
            }
            );

            return round($totalCompletion / $totalUsers, 2);
        });
    }

    /**
     * Get User Growth Trends (Last 6 Months) for ApexCharts.
     */
    public function getUserGrowthTrends(): array
    {
        return Cache::remember('user_growth_trends', 1800, function () {
            $lastSixMonths = collect(range(0, 5))->map(function ($i) {
                    return Carbon::now()->subMonths($i)->format('Y-m');
                }
                )->reverse();

                // Adjusted for PostgreSQL syntax as found in earlier steps
                $data = User::select(
                    DB::raw("to_char(created_at, 'YYYY-MM') as month"),
                    DB::raw("count(*) as count")
                )
                    ->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                $trends = $lastSixMonths->mapWithKeys(function ($month) use ($data) {
                    $match = $data->firstWhere('month', $month);
                    return [$month => $match ? $match->count : 0];
                }
                );

                return [
                    'labels' => $trends->keys()->toArray(),
                    'series' => $trends->values()->toArray()
                ];
            });
    }
}