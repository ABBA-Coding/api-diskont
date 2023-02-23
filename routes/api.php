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
    AttributeGroupController,
    AttributeOptionController,
};
use App\Http\Controllers\Files\{
    UploadController,
    DeleteController,
};
use App\Http\Controllers\{
    CategoryController,
    BrandController,
    PostController,
};
use App\Http\Controllers\Faqs\{
    FaqCategoryController,
    FaqController,
};

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user(); 
});

Route::prefix('admin')->group(function() {
    Route::apiResource('characteristics', CharacteristicController::class);
    Route::get('characteristics_groups/all', [CharacteristicGroupController::class, 'all']);
    Route::apiResource('characteristics_groups', CharacteristicGroupController::class);
    Route::apiResource('characteristics_options', CharacteristicOptionController::class);

    Route::apiResource('attributes', AttributeController::class);
    Route::get('attributes_groups/all', [AttributeGroupController::class, 'all']);
    Route::apiResource('attributes_groups', AttributeGroupController::class);
    Route::apiResource('attributes_options', AttributeOptionController::class);

    Route::post('/files/upload', [UploadController::class, 'upload']);
    Route::delete('/files/delete', [DeleteController::class, 'delete']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('posts', PostController::class);

    Route::apiResource('faqs', FaqController::class);

    Route::get('faqs_categories', [FaqCategoryController::class, 'index']);
    Route::apiResource('faqs_categories', FaqCategoryController::class);
});