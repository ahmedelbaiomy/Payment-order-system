<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AuthController;




Route::middleware('throttle:api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:api')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware('auth:api')->prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->middleware('can:cancel,order');
    });

    Route::middleware('auth:api')->prefix('payments')->group(function () {
        Route::post('/{order}', [PaymentController::class, 'pay']);
    });

    Route::post('/webhook/stripe', [WebhookController::class, 'handle']);
});
