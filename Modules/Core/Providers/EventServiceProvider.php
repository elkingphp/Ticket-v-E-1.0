<?php

namespace Modules\Core\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        \Modules\Core\Domain\Events\ModuleBooted::class => [
            \Modules\Core\Infrastructure\Listeners\LogModuleBoot::class,
        ],
        \Modules\Core\Domain\Events\AuditLogCreated::class => [
            \Modules\Core\Application\Listeners\AuditCriticalEventListener::class,
        ],
        \Modules\Core\Domain\Events\SystemHealthChecked::class => [
            \Modules\Core\Application\Listeners\SystemHealthListener::class,
        ],
    ];

    protected $subscribe = [
        \Modules\Core\Application\Listeners\ModuleLifecycleListener::class,
        \Modules\Core\Application\Listeners\ApprovalEventListener::class,
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void
    {
    }
}