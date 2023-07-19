<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return 'API for e-shop NDC.uz';
});

// Route::get('iuwebiuerwv', function() {
// 	$categories = App\Models\Category::all();
// 	foreach ($categories as $value) {
// 		$text = $value->name['ru'];
// 		$value->update(['for_search' => $text]);
// 	}
// 	dd($categories);
// });