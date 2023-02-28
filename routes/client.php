<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;









Route::prefix('auth')->group(function () {
    Route::post('check', [LoginController::class, 'checkUser']);
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [LoginController::class, 'register']);
    Route::get('test/{phone_number}', function ($phone_number) {
        return response(\Illuminate\Support\Facades\Cache::get($phone_number));
    });
});
