<?php

use Illuminate\Support\Facades\Route;

/*
 |--------------------------------------------------------------------------
 | Web Routes
 |--------------------------------------------------------------------------
 |
 | Here is where you can register web routes for your application. These
 | routes are loaded by the RouteServiceProvider within a group which
 | contains the "web" middleware group. Now create something great!
 |
 */

Route::middleware(['auth', 'verified', 'module_status:users', 'ermo_trace:users'])->group(function () {
    Route::get('users/export', [\Modules\Users\Http\Controllers\UsersController::class, 'export'])->name('users.export')->middleware(['permission:users.view', 'enforce.security']);
    Route::post('users/bulk-actions', [\Modules\Users\Http\Controllers\UsersController::class, 'bulkActions'])->name('users.bulk-actions')->middleware(['permission:users.edit|delete users', 'enforce.security']);
    Route::post('users/import', [\Modules\Users\Http\Controllers\UsersController::class, 'import'])->name('users.import')->middleware(['permission:users.create', 'enforce.security']);
    Route::get('users/download-template', [\Modules\Users\Http\Controllers\UsersController::class, 'downloadTemplate'])->name('users.download-template')->middleware(['permission:users.create', 'enforce.security']);
    Route::post('roles/bulk-actions', [\Modules\Users\Http\Controllers\RoleController::class, 'bulkActions'])->name('roles.bulk-actions')->middleware(['permission:roles.manage', 'enforce.security']);
    Route::resource('roles', \Modules\Users\Http\Controllers\RoleController::class)->middleware(['permission:roles.view|manage roles', 'enforce.security']);
    Route::resource('users', \Modules\Users\Http\Controllers\UsersController::class)->middleware(['permission:users.view|manage users', 'enforce.security']);
});