<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;


//Route::get('/user', function (Request $request) {
//    // return $request->user();
//    return 'hi';
//});
// ->middleware('auth:sanctum')

Route::prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
});
