<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\SendEmail\OTPController;

// http://127.0.0.1:8000/api/v1/auth/...
Route::group(['prefix' => 'v1/auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
});

// http://127.0.0.1:8000/api/v1/send-otp
Route::group(['prefix' => 'v1'], function () {
    Route::post('/send-otp', [OTPController::class, 'sendOtp']);
});
