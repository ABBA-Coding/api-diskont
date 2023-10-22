<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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


//handle requests from payment system
Route::any('/handle/{paysys}',function($paysys){
    (new Goodoneuz\PayUz\PayUz)->driver($paysys)->handle();
});

//redirect to payment system or payment form
Route::any('/pay/{paysys}/{key}/{amount}',function($paysys, $key, $amount){
	$model = Goodoneuz\PayUz\Services\PaymentService::convertKeyToModel($key);
    $url = request('https://beta.diskont.uz','https://beta.diskont.uz'); // redirect url after payment completed
    $pay_uz = new Goodoneuz\PayUz\PayUz;
    $pay_uz
    	->driver($paysys)
    	->redirect($model, $amount, 860, $url);
});

//Route::get('iuwebiuerwv', function() {
//    $infos = App\Models\Products\ProductInfo::all();
//    foreach ($infos as $value) {
//
//        $categoryName = $value->category->name['ru'];
//        $brandName = $value->brand->name;
//        $productName = $value->name['ru'];
//
//        $result = $brandName . ' ' . $categoryName . ' ' . $productName;
//
//        $originalName = $value->name;
//        $originalName['ru'] = $result;
//        $value->update([
//            'name' => $originalName
//        ]);
//
//        if(isset($value->name['ru'])) {
//            $value->update([
//                'for_search' => $value->name['ru']
//            ]);
//        }
//    }
//
//    $products = App\Models\Products\Product::all();
//    foreach ($products as $value) {
//
//        $originalName = $value->name;
//        $originalName['ru'] = $value->info->name['ru'];
//        $value->update([
//            'name' => $originalName
//        ]);
//
//        if(isset($value->name['ru'])) {
//            $value->update([
//                'for_search' => $value->name['ru']
//            ]);
//        }
//    }
//    dd('ok');
//});


//Route::get('redis', function () {
//    return response(Cache::store('redis')->get('products/index'));
//});

Route::get('wfgwegw', function () {
    $translates = \App\Models\Translate\Translate::whereNull('for_search')
        ->get();
    foreach ($translates as $translate) {
        $forSearch = $translate->val['ru'];
        $translate->update([
            'for_search' => $forSearch
        ]);
//        dd($forSearch);
    }
    dd($translates->count());
});
