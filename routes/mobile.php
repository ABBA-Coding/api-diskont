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
    SearchController,
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
    Route::get('/', [CategoryController::class, 'index']); // yes
    Route::get('/{slug}', [CategoryController::class, 'show']);
});

Route::prefix('products')->group(function() {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{slug}', [ProductController::class, 'show']);
});
Route::get('brands', [BrandController::class, 'index']);
Route::get('brands/{slug}', [BrandController::class, 'show']); // yes
Route::get('banners', [BannerController::class, 'index']); // yes
Route::prefix('posts')->group(function() {
    Route::get('/', [PostController::class, 'index']);
    Route::get('/{slug}', [PostController::class, 'show']);
});
Route::get('feedbacks', [FeedbackController::class, 'index']);
Route::get('search', [SearchController::class, 'search']); // yes