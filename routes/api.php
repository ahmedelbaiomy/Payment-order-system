<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebhookController;




Route::prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
});

Route::prefix('payments')->group(function () {
    Route::post('/{order}', [PaymentController::class, 'pay']);
});

Route::post('/webhook/stripe', [WebhookController::class, 'handle']);
