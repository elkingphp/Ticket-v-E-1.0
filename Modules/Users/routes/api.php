<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\UsersController;
use Modules\Users\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Authentication Routes (Public - Rate Limited)
|--------------------------------------------------------------------------
| SECURITY: Apply strict throttling to prevent brute-force attacks.
| - login: 5 attempts per minute per IP
| - register: disabled (handled by admin panel)
*/
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
});

/*
|--------------------------------------------------------------------------
| Authenticated API Routes (Requires Sanctum Token)
|--------------------------------------------------------------------------
| Apply throttle:60,1 = 60 requests per minute
*/
Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('v1')->group(function () {
    Route::get('/auth/profile', [AuthController::class, 'profile'])->name('api.auth.profile');
    Route::apiResource('users', UsersController::class)->names('api.users');
});
