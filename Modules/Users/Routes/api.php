<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\Api\AuthController;

/*
 |--------------------------------------------------------------------------
 | API Routes
 |--------------------------------------------------------------------------
 |
 | Here is where you can register API routes for your application. These
 | routes are loaded by the RouteServiceProvider within a group which
 | is assigned the "api" middleware group. Enjoy building your API!
 |
 */

Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class , 'login'])->name('api.auth.login');
    Route::post('/register', [AuthController::class , 'register'])->name('api.auth.register');

    Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class , 'logout'])->name('api.auth.logout');
            Route::get('/user', [AuthController::class , 'profile'])->name('api.auth.user');
        }
        );
    });