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
            ->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('for_search', 'like', '%'.$request->search.'%');
            });

        // sortirovka
        if(isset($request->sort) && $request->sort != '') {
            switch ($request->sort) {
                case 'popular':
                    $products = $products->orderBy('is_popular', 'desc');
                    break;

                case 'expensive':
                    $products = $products->orderBy('price', 'desc');
                    break;

                case 'cheap':
                    $products = $products->orderBy('price');
                    break;
            }
        }

        $products = $products->with('images', 'promotions')
            ->limit(20)
            ->get();

        $categories = Category::where(function($q) use ($request) {
            $q->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('for_search', 'like', '%'.$request->search.'%');
        })
            ->limit(20)
            ->get();

        foreach($products as $product) {
            $this->without_lang([$product]);
            $this->without_lang($product->promotions);
            $this->without_lang([$product->info]);
        }
        $this->without_lang($categories);

        return response([
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
