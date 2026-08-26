<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\DepartmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public route
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes (requires valid Sanctum Bearer token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::apiResource('departments', DepartmentController::class);
    });
});