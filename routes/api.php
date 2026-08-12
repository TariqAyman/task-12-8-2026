<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\CustomerServiceController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Middleware\AuthenticateApi;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('auth.login');

    Route::middleware(AuthenticateApi::class)->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('auth/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::apiResource('customers', CustomerController::class);

        Route::apiResource('customers.services', CustomerServiceController::class)
            ->only(['index', 'store'])
            ->parameters(['services' => 'service']);

        Route::apiResource('services', ServiceController::class)
            ->only(['index', 'show', 'update', 'destroy']);
    });
});
