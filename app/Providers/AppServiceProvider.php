<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        \Modules\Users\Domain\Models\User::observe(\App\Observers\UserObserver::class);

        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'user' => \Modules\Users\Domain\Models\User::class ,
        ]);

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            // Flexible check: if user has super-admin role, grant all access
            return $user->hasRole('super-admin') ? true : null;
        });

        // Register Notification Observer for real-time broadcasting
        \Illuminate\Notifications\DatabaseNotification::observe(\App\Observers\NotificationObserver::class);
    }
}