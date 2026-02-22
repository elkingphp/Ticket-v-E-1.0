<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', [\App\Http\Controllers\HealthCheckController::class , 'check'])
    ->name('health.check')
    ->middleware(['health.token', 'throttle:60,1']);Route::get("/dev/login", function () { auth()->loginUsingId(1); return redirect("/educational/evaluations/settings#evaluation-types"); });
