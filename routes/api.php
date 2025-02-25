<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ANBController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/anb/token', [ANBController::class, 'getAccessToken']);
Route::post('/withdraw', [ANBController::class, 'withdrawFromBank']);
Route::post('/payment', [ANBController::class, 'sendPayment']);
