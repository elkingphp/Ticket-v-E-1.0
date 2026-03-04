<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->middleware('guest');

Route::get('/health', [\App\Http\Controllers\HealthCheckController::class, 'check'])
    ->name('health.check')
    ->middleware(['health.token', 'throttle:60,1']);
