<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;

Route::middleware(['auth', 'verified', 'module_status:settings', 'ermo_trace:settings'])->group(function () {
    Route::get('settings', [SettingsController::class , 'index'])->name('settings.index')->middleware(['permission:settings.view|manage settings', 'enforce.security']);
    Route::post('settings', [SettingsController::class , 'update'])->name('settings.update')->middleware(['permission:settings.manage', 'enforce.security']);
});