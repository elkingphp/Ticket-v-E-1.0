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
    Route::resource('tickets', TicketsController::class)->names('tickets');

    // Notifications
    Route::get('notifications/{id}/read', [\Modules\Tickets\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/mark-all-read', [\Modules\Tickets\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');

    Route::prefix('admin/tickets')->name('admin.tickets.')->group(function () {
        Route::get('settings', [Modules\Tickets\Http\Controllers\Admin\TicketSettingsController::class, 'index'])->name('settings');
        Route::post('settings', [Modules\Tickets\Http\Controllers\Admin\TicketSettingsController::class, 'update'])->name('settings.update');

        Route::resource('stages', TicketStageController::class);
        Route::resource('categories', TicketCategoryController::class);
        Route::resource('complaints', TicketComplaintController::class);
        Route::resource('statuses', TicketStatusController::class);
        Route::resource('priorities', TicketPriorityController::class);
        Route::resource('groups', TicketGroupController::class);
        Route::get('templates/{template}/preview', [TicketEmailTemplateController::class, 'preview'])->name('templates.preview');
        Route::post('templates/{template}/test', [TicketEmailTemplateController::class, 'test'])->name('templates.test');
        Route::resource('templates', TicketEmailTemplateController::class);
    });

    Route::prefix('agent/tickets')->name('agent.tickets.')->group(function () {
        Route::get('/', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'index'])->name('index');
        Route::get('/{uuid}', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'show'])->name('show');
        Route::get('/{uuid}/print', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'print'])->name('print');
        Route::post('/{uuid}/reply', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'reply'])->name('reply');
        Route::put('/{uuid}/status', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'updateStatus'])->name('updateStatus');
        Route::put('/{uuid}/priority', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'updatePriority'])->name('updatePriority');
        Route::put('/{uuid}/assign', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'assign'])->name('assign');
        Route::put('/{uuid}/assign-group', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'assignGroup'])->name('assignGroup');
        Route::put('/{uuid}/close', [\Modules\Tickets\Http\Controllers\Agent\AgentTicketController::class, 'close'])->name('close');
    });
});