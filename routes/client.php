<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\web\{ShowcaseController,
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
    ComparisonController,
    BarController,
    PromotionController,
    TranslateController,
    UserAddressController,
    CommentController,
    DicoinController};








Route::middleware('set_lang')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('check', [AuthController::class, 'checkUser']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    Route::middleware(['auth:sanctum', 'ability:client'])->group(function() {
        Route::prefix('profile')->group(function () {
            Route::put('update', [ProfileController::class, 'update']);
            Route::put('edit_name', [ProfileController::class, 'edit_name']);
            Route::get('me', [ProfileController::class, 'me']);
        });

        Route::post('order', [OrderController::class, 'store']);
        Route::post('addresses', [UserAddressController::class, 'store']);
        Route::put('addresses/{id}', [UserAddressController::class, 'update']);
        Route::delete('addresses/{id}', [UserAddressController::class, 'destroy']);
    });

    Route::prefix('categories')->group(function() {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{slug}', [CategoryController::class, 'show']);
    });

    Route::prefix('products')->group(function() {
        Route::post('/comments', [CommentController::class, 'store']);
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
    Route::post('order/one_click', [OrderController::class, 'one_click']);
    Route::get('showcases', [ShowcaseController::class, 'get']);
    Route::get('showcases/{slug}', [ShowcaseController::class, 'show']);
    Route::post('comparison', [ComparisonController::class, 'comparison']);
    Route::prefix('bars')->group(function() {
        Route::get('/', [BarController::class, 'index']);
        Route::get('/{slug}', [BarController::class, 'show']);
    });
    Route::prefix('promotions')->group(function() {
        Route::get('/', [PromotionController::class, 'index']);
        Route::get('/{slug}', [PromotionController::class, 'show']);
    });
    Route::get('dicoins', [DicoinController::class, 'get']);
    Route::get('translates', [TranslateController::class, 'get']);
});
