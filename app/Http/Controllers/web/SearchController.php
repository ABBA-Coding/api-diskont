<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Products\ProductInfo;
use App\Models\Products\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $products = Product::where('status', 'active')
            ->whereHas('info', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('for_search', 'like', '%'.$request->search.'%');
            })
            ->with('images')
            ->limit(20)
            ->get();
        // $products = ProductInfo::where(function ($q) use ($request) {
        // 		$q->where('name', 'like', '%'.$request->search.'%')
        // 			->orWhere('for_search', 'like', '%'.$request->search.'%');
        // 	})
        //     ->where('is_active', 1)
        //     ->whereHas('products', function ($q) {
        //     	// $q->where('status', 'active');
        //     })
        //     ->with(['products' => function ($q) {
        //         $q->where('status', 'active');
        //     }], 'products.images')
        //     ->limit(20)
        //     ->get();
        $categories = Category::where('name', 'like', '%'.$request->search.'%')
            ->orWhere('for_search', 'like', '%'.$request->search.'%')
            ->limit(20)
            ->get();

        foreach($products as $product) {
            $this->without_lang([$product->info]);
        }
        $this->without_lang($categories);
        
        return response([
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
