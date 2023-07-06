<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\c\ProductController;

Route::get('/', function () {});

Route::group(['prefix' => 'products'], function () {
    Route::post('/', [ProductController::class, 'store'])->middleware('check1c');
    Route::delete('/', [ProductController::class, 'delete'])->middleware('check1c');
});
