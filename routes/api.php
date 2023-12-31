<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Characteristics\{
    CharacteristicController,
    CharacteristicGroupController,
    CharacteristicOptionController,
};
use App\Http\Controllers\Attributes\{
    AttributeController,
    AttributeOptionController,
};
use App\Http\Controllers\Files\{
    UploadController,
    DeleteController,
};
use App\Http\Controllers\{
    DiscountController,
    Orders\OneClickOrderController,
    Orders\OrderController,
    RoleController,
    ShowcaseController,
    CategoryController,
    BrandController,
    PostController,
    CommentController,
    BannerController,
    Settings\RegionController,
    Settings\DistrictController,
    PromotionController,
    BarController,
    RegionGroupController,
    BranchController,
    Translates\TranslateController,
    Translates\TranslateGroupController,
    InfoController,
};
use App\Http\Controllers\Faqs\{
    FaqCategoryController,
    FaqController,
};
use App\Http\Controllers\Products\{
    ProductBadgeController,
    ProductController,
    ProductInfoController,
};
use App\Http\Controllers\Feedbacks\FeedbackController;
use App\Http\Controllers\Dicoin\DicoinController;
use App\Http\Controllers\Clients\{
    ClientController,
};
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PermissionGroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsLetterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('admin')->group(function() {
    Route::prefix('auth')->group(function () {
        Route::post('login', [LoginController::class, 'login']);
        Route::post('logout', [LoginController::class, 'logout'])->middleware(['auth:sanctum', 'ability:admin', 'role']);
        Route::post('me', [LoginController::class, 'me'])->middleware(['auth:sanctum', 'ability:admin', 'role']);
    });

    Route::middleware(['auth:sanctum', 'ability:admin', 'role'])->group(function () {
        Route::get('/characteristics/all', [CharacteristicController::class, 'all']);
        Route::apiResource('characteristics', CharacteristicController::class);
        Route::get('characteristics_groups/all', [CharacteristicGroupController::class, 'all']);
        Route::apiResource('characteristics_groups', CharacteristicGroupController::class);

        Route::post('characteristics_options/store_more', [CharacteristicOptionController::class, 'store_more']);
        Route::apiResource('characteristics_options', CharacteristicOptionController::class);

        Route::get('/attributes/all', [AttributeController::class, 'all']);
        Route::apiResource('attributes', AttributeController::class);
        Route::apiResource('attributes_options', AttributeOptionController::class);

        Route::post('/files/upload', [UploadController::class, 'upload']);
        Route::delete('/files/delete', [DeleteController::class, 'delete']);

        Route::apiResource('categories', CategoryController::class);

        Route::get('brands/all', [BrandController::class, 'all']);
        Route::apiResource('brands', BrandController::class);
        Route::apiResource('posts', PostController::class);

        Route::apiResource('faqs', FaqController::class);

        Route::get('faqs_categories', [FaqCategoryController::class, 'index']);
        Route::apiResource('faqs_categories', FaqCategoryController::class);

        Route::get('products/get_undone_variations', [ProductController::class, 'get_undone_variations']);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('products_infos', ProductInfoController::class);

        Route::apiResource('comments', CommentController::class);

        Route::get('/banners/types', [BannerController::class, 'types']);
        Route::apiResource('banners', BannerController::class);

        Route::apiResource('feedbacks', FeedbackController::class);

        Route::apiResource('regions', RegionController::class);
        Route::apiResource('districts', DistrictController::class);

        Route::get('showcases/all', [ShowcaseController::class, 'all']);
        Route::apiResource('showcases', ShowcaseController::class);

        Route::apiResource('one_click_orders', OneClickOrderController::class);
        Route::get('orders/counts', [OrderController::class, 'counts']);
        Route::apiResource('orders', OrderController::class);

        Route::apiResource('product_badges', ProductBadgeController::class);
        Route::apiResource('discounts', DiscountController::class);

        Route::apiResource('promotions', PromotionController::class);
        Route::get('bars/search', [BarController::class, 'search_cat_promo']);
        Route::apiResource('bars', BarController::class);
        Route::apiResource('region_groups', RegionGroupController::class);
        Route::apiResource('dicoin', DicoinController::class);
        Route::apiResource('branches', BranchController::class);
        Route::group(['prefix' => 'clients'], function () {
            Route::get('/', [ClientController::class, 'index']);
            Route::get('/{id}', [ClientController::class, 'show']);
        });
        Route::apiResource('admins', AdminController::class);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('permissions', PermissionController::class);
        Route::apiResource('permission_groups', PermissionGroupController::class);
        Route::get('translate_groups/all', [TranslateGroupController::class, 'all']);
        Route::apiResource('translate_groups', TranslateGroupController::class);
        Route::put('translates/multiple_update', [TranslateController::class, 'multiple_update']);
        Route::apiResource('translates', TranslateController::class);
        Route::apiResource('info', InfoController::class);
        Route::delete('variations/{id}', [ProductController::class, 'variation_delete']);

        Route::get('dashboard', [DashboardController::class, 'get']);

        Route::post('newsletters-send', [NewsLetterController::class, 'send']);
        Route::resource('newsletters', NewsLetterController::class);
    });
});
