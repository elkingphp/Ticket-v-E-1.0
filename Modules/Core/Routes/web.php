<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/locale-debug', function () {
    return response()->json([
        'app_locale' => app()->getLocale(),
        'session_locale' => session()->get('locale'),
        'user_locale' => auth()->check() ? auth()->user()->language : 'Guest',
        'config_locale' => config('app.locale'),
        'roles' => auth()->check() ? auth()->user()->getRoleNames() : [],
        'permissions' => auth()->check() ? auth()->user()->getAllPermissions()->pluck('name') : [],
        'dt_language_url' => app()->getLocale() == 'ar' ? asset('assets/json/datatable-ar.json') : null,
    ]);
});

// Language Switcher
Route::get('/lang/{locale}', [\Modules\Core\Http\Controllers\LocalizationController::class, 'switch'])->name('lang.switch');

Route::middleware(['auth', 'verified', 'module_status:core'])->group(function () {
    // Fallback GET route for exports
    Route::get('/profile/export', function () {
        return redirect()->route('profile.index')->with('warning', __('core::profile.invalid_data'));
    });

    Route::middleware(['ermo_trace:core'])->group(function () {
        Route::get(
            '/dashboard',
            function () {
                return view('core::dashboard');
            }
        )->name('dashboard')->middleware(['enforce.security', 'permission:core.dashboard.view']);

        // Profile Routes (except 2FA)
        Route::get('/profile', [\Modules\Core\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [\Modules\Core\Http\Controllers\ProfileController::class, 'update'])->name('profile.update')->middleware('sudo');
        Route::post('/profile/avatar', [\Modules\Core\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar');
        Route::delete('/profile/avatar', [\Modules\Core\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
        Route::post('/profile/password', [\Modules\Core\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password')->middleware('sudo');
        Route::post('/profile/logout-other-devices', [\Modules\Core\Http\Controllers\ProfileController::class, 'logoutOtherDevices'])->name('profile.logout-other-devices');
        Route::get('/profile/sudo', [\Modules\Core\Http\Controllers\ProfileController::class, 'sudo'])->name('profile.sudo');
        Route::post('/profile/sudo', [\Modules\Core\Http\Controllers\ProfileController::class, 'sudoConfirm'])->name('profile.sudo.confirm');
        Route::delete('/profile/sessions/{id}', [\Modules\Core\Http\Controllers\ProfileController::class, 'terminateSession'])->name('profile.sessions.terminate')->middleware('sudo');
        Route::delete('/profile/sessions', [\Modules\Core\Http\Controllers\ProfileController::class, 'terminateOtherSessions'])->name('profile.sessions.terminate-others')->middleware('sudo');
        Route::post('/profile/notifications', [\Modules\Core\Http\Controllers\ProfileController::class, 'updateNotifications'])->name('profile.notifications.update');
        Route::post('/profile/export', [\Modules\Core\Http\Controllers\ProfileController::class, 'exportData'])->name('profile.export')->middleware('sudo');
        Route::get('/profile/export/download/{filename}', [\Modules\Core\Http\Controllers\ProfileController::class, 'downloadExport'])->name('profile.export.download')->where('filename', '.*');
        Route::delete('/profile/export/{filename}', [\Modules\Core\Http\Controllers\ProfileController::class, 'deleteExport'])->name('profile.export.delete')->where('filename', '.*');

        Route::delete('/profile', [\Modules\Core\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.delete')->middleware('sudo');
        Route::post('/profile/cancel-deletion', [\Modules\Core\Http\Controllers\ProfileController::class, 'cancelDeletion'])->name('profile.delete.cancel');

        // Audit Logs
        Route::get('/audit-logs/export', [\Modules\Core\Http\Controllers\AuditController::class, 'export'])->name('audit.export')->middleware(['permission:audit.view', 'enforce.security']);
        Route::get('/audit-logs', [\Modules\Core\Http\Controllers\AuditController::class, 'index'])->name('audit.index')->middleware(['permission:audit.view', 'enforce.security']);
        Route::get('/audit-logs/{id}', [\Modules\Core\Http\Controllers\AuditController::class, 'show'])->name('audit.show')->middleware(['permission:audit.view', 'enforce.security']);

        // Dashboard Metrics API
        Route::get('/api/dashboard/metrics', [App\Http\Controllers\Api\DashboardController::class, 'metrics'])->name('dashboard.metrics')->middleware(['permission:analytics.view', 'enforce.security']);

        // Notifications
        Route::group(
            ['prefix' => 'notifications', 'as' => 'notifications.', 'middleware' => ['permission:notifications.view', 'enforce.security']],
            function () {
                Route::get('/', [\Modules\Core\Http\Controllers\NotificationController::class, 'index'])->name('index');
                Route::get('/latest', [\Modules\Core\Http\Controllers\NotificationController::class, 'latest'])->name('latest');
                Route::post('/{id}/read', [\Modules\Core\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
                Route::post('/mark-all-read', [\Modules\Core\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
                Route::delete('/{id}', [\Modules\Core\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
                Route::post('/clear-read', [\Modules\Core\Http\Controllers\NotificationController::class, 'clearRead'])->name('clear-read');
            }
        );

        // Admin: Notification Management (Super Admin Only)
        Route::prefix('admin/notifications')->name('admin.notifications.')->middleware('role:super-admin')->group(
            function () {
                Route::get('/dashboard', [\Modules\Core\Http\Controllers\Admin\NotificationAdminController::class, 'dashboard'])->name('dashboard');
                Route::get('/statistics', [\Modules\Core\Http\Controllers\Admin\NotificationAdminController::class, 'statistics'])->name('statistics');

                // Thresholds Management
                Route::get('/thresholds', [\Modules\Core\Http\Controllers\Admin\NotificationAdminController::class, 'thresholds'])->name('thresholds.index');
                Route::post('/thresholds', [\Modules\Core\Http\Controllers\Admin\NotificationAdminController::class, 'createThreshold'])->name('thresholds.create');
                Route::put('/thresholds/{threshold}', [\Modules\Core\Http\Controllers\Admin\NotificationAdminController::class, 'updateThreshold'])->name('thresholds.update');
                Route::delete('/thresholds/{threshold}', [\Modules\Core\Http\Controllers\Admin\NotificationAdminController::class, 'deleteThreshold'])->name('thresholds.delete');
                Route::post('/thresholds/{threshold}/toggle', [\Modules\Core\Http\Controllers\Admin\NotificationAdminController::class, 'toggleThreshold'])->name('thresholds.toggle');

                // Cleanup & Maintenance
                Route::post('/cleanup', [\Modules\Core\Http\Controllers\Admin\NotificationAdminController::class, 'cleanup'])->name('cleanup');
                Route::post('/test', [\Modules\Core\Http\Controllers\Admin\NotificationAdminController::class, 'testNotification'])->name('test');
            }
        );

        // ERMO Mission Control & Module Management
        Route::prefix('admin/ermo')->name('admin.ermo.')->middleware('role:super-admin')->group(
            function () {
                Route::get('/', [\Modules\Core\Http\Controllers\Admin\ERMOController::class, 'index'])->name('index');
                Route::get('/metrics', [\Modules\Core\Http\Controllers\Admin\ERMOController::class, 'metrics'])->name('metrics');
                Route::get('/graph', [\Modules\Core\Http\Controllers\Admin\ERMOController::class, 'graph'])->name('graph');
                Route::post('/transition', [\Modules\Core\Http\Controllers\Admin\ERMOController::class, 'transition'])->name('transition')->middleware('sudo');

                // Module CRUD
                Route::get('/modules', [\Modules\Core\Http\Controllers\Admin\ERMOController::class, 'modules'])->name('modules.index');
                Route::post('/modules', [\Modules\Core\Http\Controllers\Admin\ERMOController::class, 'store'])->name('modules.store')->middleware('sudo');
                Route::put('/modules/{module}', [\Modules\Core\Http\Controllers\Admin\ERMOController::class, 'update'])->name('modules.update')->middleware('sudo');
                Route::delete('/modules/{module}', [\Modules\Core\Http\Controllers\Admin\ERMOController::class, 'destroy'])->name('modules.destroy')->middleware('sudo');

                // Chaos Simulation
                Route::post('/chaos/simulate', [\Modules\Core\Http\Controllers\Admin\ERMOController::class, 'simulateChaos'])->name('chaos.simulate')->middleware('sudo');
            }
        );
    });

    // Fortify 2FA Overrides for Sudo Mode & AJAX support
    // (Moving outside ermo_trace to avoid potential Redis/LoadShedding interference during debugging)
    Route::middleware(['sudo'])->group(function () {
        Route::post('/user/two-factor-authentication', [\Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController::class, 'store']);
        Route::delete('/user/two-factor-authentication', [\Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController::class, 'destroy']);
        Route::post('/user/confirmed-two-factor-authentication', [\Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController::class, 'store']);
        Route::post('/user/two-factor-recovery-codes', [\Laravel\Fortify\Http\Controllers\RecoveryCodeController::class, 'store']);
        Route::get('/user/two-factor-qr-code', [\Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController::class, 'show']);
        Route::get('/user/two-factor-recovery-codes', [\Laravel\Fortify\Http\Controllers\RecoveryCodeController::class, 'index']);
    });
});