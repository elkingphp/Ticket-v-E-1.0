<?php

use Illuminate\Support\Facades\Route;
use Modules\Educational\Http\Controllers\Api\AttendanceController;
use Modules\Educational\Http\Controllers\Api\LectureController;
use Modules\Educational\Http\Controllers\Api\EvaluationController;

Route::prefix('educational')->middleware('api')->group(function () {
    Route::apiResource('attendances', AttendanceController::class)->only(['index', 'update']);
    Route::post('attendances/{attendance}/lock', [AttendanceController::class, 'lock']);

    Route::apiResource('lectures', LectureController::class)->only(['index']);
    Route::patch('lectures/{lecture}/status', [LectureController::class, 'updateStatus']);

    Route::post('evaluations/submit', [EvaluationController::class, 'submit']);
});
