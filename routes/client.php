<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\web\{
    CategoryController,
    ProductController,
    BrandController,
    BannerController,
    PostController,
    FeedbackController,
};









Route::prefix('auth')->group(function () {
    Route::post('check', [LoginController::class, 'checkUser']);
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [LoginController::class, 'register']);
    Route::get('test/{phone_number}', function ($phone_number) {
        return response(\Illuminate\Support\Facades\Cache::get($phone_number));
    });
});

Route::prefix('categories')->group(function() {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{slug}', [CategoryController::class, 'show']);
});
// Route::get('categories', [CategoryController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);
Route::get('brands', [BrandController::class, 'index']);
Route::get('banners', [BannerController::class, 'index']);
Route::prefix('posts')->group(function() {
    Route::get('/', [PostController::class, 'index']);
    Route::get('/{slug}', [PostController::class, 'show']);
});
Route::get('feedbacks', [FeedbackController::class, 'index']);
