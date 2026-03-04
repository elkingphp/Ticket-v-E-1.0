<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        // SECURITY: Define strong global password policy
        // Min 10 chars, mixed case, numbers, symbols, not in breach databases
        Password::defaults(function () {
            return app()->isProduction()
                ? Password::min(10)->mixedCase()->numbers()->symbols()->uncompromised()
                : Password::min(8);
        });

        \Illuminate\Pagination\Paginator::useBootstrapFive();
        \Modules\Users\Domain\Models\User::observe(\App\Observers\UserObserver::class);

        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'user' => \Modules\Users\Domain\Models\User::class,
        ]);

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            // Flexible check: if user has super-admin role, grant all access
            if ($user->hasRole('super-admin')) {
                return true;
            }

            // --- Backward Compatibility & Deprecation Logging ---
            $deprecated = [
                "view audit logs" => "audit.view",
                "update profile" => "profile.update",
                "view analytics" => "analytics.view",
                "view integrity widget" => "integrity_widget.view",
                "view users" => "users.view",
                "create users" => "users.create",
                "edit users" => "users.edit",
                "view roles" => "roles.view",
                "manage roles" => "roles.manage",
                "view permissions" => "permissions.view",
                "manage permissions" => "permissions.manage",
                "view settings" => "settings.view",
                "manage settings" => "settings.manage",
                "view students" => "students.view",
                "create students" => "students.create",
                "edit students" => "students.edit",
                "import students" => "students.import",
                "export students" => "students.export",
                "view instructors" => "instructors.view",
                "create instructors" => "instructors.create",
                "edit instructors" => "instructors.edit",
                "delete instructors" => "instructors.delete",
                "view groups" => "groups.view",
                "create groups" => "groups.create",
                "edit groups" => "groups.edit",
                "delete groups" => "groups.delete",
                "view lectures" => "lectures.view",
                "create lectures" => "lectures.create",
                "edit lectures" => "lectures.edit",
                "delete lectures" => "lectures.delete",
                "manage attendance" => "attendance.manage",
                "view attendance reports" => "attendance.report",
                "view programs" => "programs.view",
                "manage programs" => "programs.manage",
                "view tracks" => "tracks.view",
                "manage tracks" => "tracks.manage",
                "view job profiles" => "job_profiles.view",
                "manage job profiles" => "job_profiles.manage",
                "manage campus structure" => "campus_structure.manage",
                "manage evaluations" => "evaluations.manage",
                "view evaluation results" => "evaluation_results.view",
                "delete users" => "users.delete",
                "tickets.access" => "tickets.view",
                "tickets.create" => "tickets.create",
                "tickets.agent_desk" => "tickets.manage",
                "tickets.lookups" => "tickets.lookups",
                "tickets.settings" => "tickets.settings",
                "tickets.routing" => "tickets.routing",
                "tickets.manage_templates" => "ticket_templates.manage",
                "delete students" => "students.delete",
                "import instructors" => "instructors.import",
                "export instructors" => "instructors.export",
                "import groups" => "groups.import",
                "export groups" => "groups.export",
                "manage lectures" => "lectures.manage",
                "tickets.manage_stages" => "ticket_stages.view",
                "tickets.manage_categories" => "ticket_categories.view",
                "tickets.manage_complaints" => "ticket_complaints.view",
                "tickets.manage_statuses" => "ticket_statuses.view",
                "tickets.manage_priorities" => "ticket_priorities.view",
                "tickets.manage_groups" => "ticket_groups.view",
                "view notifications" => "notifications.view"
            ];

            if (isset($deprecated[$ability])) {
                \Illuminate\Support\Facades\Log::warning("DEPRECATED PERMISSION: '$ability' used in " . request()->fullUrl() . ". Refactor to '{$deprecated[$ability]}'.");
                return $user->hasPermissionTo($deprecated[$ability]);
            }

            return null;
        });

        // Register Notification Observer for real-time broadcasting
        \Illuminate\Notifications\DatabaseNotification::observe(\App\Observers\NotificationObserver::class);
    }
}