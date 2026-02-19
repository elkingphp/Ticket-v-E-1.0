<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\ERMOController;

Route::prefix('v1/ermo')->group(function () {
    Route::get('/registry', [ERMOController::class , 'registry']);
    Route::get('/metrics', [ERMOController::class , 'metrics']);
    Route::get('/prometheus-metrics', [ERMOController::class , 'prometheus']);
    Route::get('/events', [ERMOController::class , 'events']);
    Route::get('/graph', [ERMOController::class , 'graph']);

    // Write operations (Sudo & Auth Protected)
    Route::middleware(['auth:sanctum', 'sudo'])->group(function () {
            Route::post('/transition', [ERMOController::class , 'transition']);
            Route::post('/sync', [ERMOController::class , 'sync']);
        }
        );
    });