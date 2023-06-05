<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\web\{
    RegionController,
    OrderController,
    Auth\AuthController,
    ProfileController,
    CategoryController,
    ProductController,
    BrandController,
    BannerController,
    PostController,
    FeedbackController,
    SearchController,
};









Route::prefix('auth')->group(function () {
    Route::post('check', [AuthController::class, 'checkUser']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum'])->group(function() {
    Route::prefix('profile')->group(function () {
        Route::put('update', [ProfileController::class, 'update']);
        Route::put('edit_name', [ProfileController::class, 'edit_name']);
        Route::get('me', [ProfileController::class, 'me']);
    });

    Route::post('order', [OrderController::class, 'store']);
});

Route::prefix('categories')->group(function() {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{slug}', [CategoryController::class, 'show']);
});

Route::prefix('products')->group(function() {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{slug}', [ProductController::class, 'show']);
});
Route::prefix('brands')->group(function() {
    Route::get('/', [BrandController::class, 'index']);
    Route::get('/{slug}', [BrandController::class, 'show']);
});
Route::get('banners', [BannerController::class, 'index']);
Route::prefix('posts')->group(function() {
    Route::get('/', [PostController::class, 'index']);
    Route::get('/{slug}', [PostController::class, 'show']);
});
Route::get('feedbacks', [FeedbackController::class, 'index']);
Route::get('regions', [RegionController::class, 'index']);
Route::get('search', [SearchController::class, 'search']);
Route::post('get_products', [OrderController::class, 'get_products']);