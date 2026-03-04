<?php

use Illuminate\Support\Facades\Route;
use Modules\Tickets\Http\Controllers\TicketsController;
use Modules\Tickets\Http\Controllers\Admin\TicketStageController;
use Modules\Tickets\Http\Controllers\Admin\TicketCategoryController;
use Modules\Tickets\Http\Controllers\Admin\TicketComplaintController;
use Modules\Tickets\Http\Controllers\Admin\TicketStatusController;
use Modules\Tickets\Http\Controllers\Admin\TicketPriorityController;
use Modules\Tickets\Http\Controllers\Admin\TicketGroupController;
use Modules\Tickets\Http\Controllers\Admin\TicketEmailTemplateController;

Route::middleware(['auth', 'verified'])->group(function () {

    // User Tickets — open to all authenticated users (controller filters by ownership)
    Route::resource('tickets', TicketsController::class)->names('tickets');

    // Notifications
    Route::get('notifications/{id}/read', [\Modules\Tickets\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/mark-all-read', [\Modules\Tickets\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');

    // Admin & Management (Granular Permissions)
    Route::prefix('admin/tickets')->name('admin.tickets.')->group(function () {

        // Redirect to main settings page (no separate permission gate needed — handled by settings.view)
        Route::get('settings', function () {
            return redirect()->route('settings.index', ['#tickets']);
        })->name('settings');

        Route::resource('stages', TicketStageController::class);
        Route::resource('categories', TicketCategoryController::class);
        Route::resource('complaints', TicketComplaintController::class);
        Route::resource('statuses', TicketStatusController::class);
        Route::resource('priorities', TicketPriorityController::class);
        Route::resource('groups', TicketGroupController::class);

        Route::get('delete-requests', [\Modules\Tickets\Http\Controllers\Admin\TicketDeleteRequestController::class, 'index'])->name('delete-requests.index');
        Route::post('delete-requests/{deleteRequest}/approve', [\Modules\Tickets\Http\Controllers\Admin\TicketDeleteRequestController::class, 'approve'])->name('delete-requests.approve');
        Route::post('delete-requests/{deleteRequest}/reject', [\Modules\Tickets\Http\Controllers\Admin\TicketDeleteRequestController::class, 'reject'])->name('delete-requests.reject');

        Route::middleware('permission:tickets.templates.manage')->group(function () {
            Route::get('templates/{template}/preview', [TicketEmailTemplateController::class, 'preview'])->name('templates.preview');
            Route::post('templates/{template}/test', [TicketEmailTemplateController::class, 'test'])->name('templates.test');
            Route::resource('templates', TicketEmailTemplateController::class);
        });
    });

    // Agent Desk (Handle/Reply) - NEW GRANULAR PERMISSIONS
    Route::prefix('agent/tickets')->name('agent.tickets.')->group(function () {

        Route::get('/dashboard', [\Modules\Tickets\Http\Controllers\Agent\TicketDashboardController::class, 'index'])
            ->name('dashboard')->middleware('permission:tickets.view_dashboard');

        // View helpdesk list: requires view_desk OR reply OR distribute (anyone with any helpdesk access)
        Route::get('/', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'index'])
            ->name('index')->middleware('permission:tickets.view_desk|tickets.reply|tickets.distribute');

        // View ticket details: requires at least reply permission (view_desk= list only)
        Route::get('/{uuid}', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'show'])
            ->name('show')->middleware('permission:tickets.reply|tickets.distribute');

        Route::get('/{uuid}/print', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'print'])
            ->name('print')->middleware('permission:tickets.reply|tickets.distribute');

        // Reply: requires tickets.reply
        Route::post('/{uuid}/reply', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'reply'])
            ->name('reply')->middleware('permission:tickets.reply');

        // Status & Priority changes: requires tickets.reply
        Route::put('/{uuid}/status', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'updateStatus'])
            ->name('updateStatus')->middleware('permission:tickets.reply');

        Route::put('/{uuid}/priority', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'updatePriority'])
            ->name('updatePriority')->middleware('permission:tickets.reply');

        // Assign: route is shared — Controller enforces reply vs distribute
        Route::put('/{uuid}/assign', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'assign'])
            ->name('assign')->middleware('permission:tickets.reply|tickets.distribute');

        // Assign to Group: requires distribute
        Route::put('/{uuid}/assign-group', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'assignGroup'])
            ->name('assignGroup')->middleware('permission:tickets.distribute');

        // Close: requires reply
        Route::put('/{uuid}/close', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'close'])
            ->name('close')->middleware('permission:tickets.reply');

        // Request Delete (with approval): requires delete_requires_approval
        Route::post('/{uuid}/delete-request', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'requestDelete'])
            ->name('requestDelete')->middleware('permission:tickets.delete_requires_approval');

        // Bulk Close: requires bulk_close
        Route::post('/bulk-close', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'bulkClose'])
            ->name('bulkClose')->middleware('permission:tickets.bulk_close');
    });
});