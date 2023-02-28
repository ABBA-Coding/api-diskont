<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\web\{
    CategoryController,
    ProductController,
    BrandController,
};









Route::prefix('auth')->group(function () {
    Route::post('check', [LoginController::class, 'checkUser']);
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [LoginController::class, 'register']);
    Route::get('test/{phone_number}', function ($phone_number) {
        return response(\Illuminate\Support\Facades\Cache::get($phone_number));
    });
});

Route::get('categories', [CategoryController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);
Route::get('brands', [BrandController::class, 'index']);
