<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\c\ProductController;
use App\Http\Controllers\c\OrderController;

Route::get('/', function () {});

Route::group(['prefix' => 'products'], function () {
    Route::post('/', [ProductController::class, 'store'])->middleware('check1c');
    Route::delete('/', [ProductController::class, 'delete'])->middleware('check1c');
});

Route::group(['prefix' => 'order'], function () {
    Route::get('test', [OrderController::class, 'create_client']);
});
