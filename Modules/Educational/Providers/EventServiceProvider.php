<?php

namespace Modules\Educational\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        \Modules\Educational\Domain\Events\LectureCreated::class => [
            [\Modules\Educational\Application\Listeners\EducationalEventListener::class, 'handleLectureCreated'],
        ],
        \Modules\Educational\Domain\Events\LectureStatusChanged::class => [
            [\Modules\Educational\Application\Listeners\EducationalEventListener::class, 'handleLectureStatusChanged'],
        ],
        \Modules\Educational\Domain\Events\AttendanceOverridden::class => [
            [\Modules\Educational\Application\Listeners\EducationalEventListener::class, 'handleAttendanceOverridden'],
        ],
        \Modules\Educational\Domain\Events\EvaluationSubmitted::class => [
            [\Modules\Educational\Application\Listeners\EducationalEventListener::class, 'handleEvaluationSubmitted'],
        ],
        \Modules\Educational\Domain\Events\FormAssignedToLecture::class => [
            \Modules\Educational\Application\Listeners\SendFormAssignmentNotification::class,
        ],
        \Modules\Educational\Domain\Events\RedFlagDetected::class => [
            \Modules\Educational\Application\Listeners\HandleRedFlagDetection::class,
        ],
        \Modules\Core\Domain\Events\ApprovalApproved::class => [
            [\Modules\Educational\Application\Listeners\EducationalEventListener::class, 'handleApprovalApproved'],
        ],
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
