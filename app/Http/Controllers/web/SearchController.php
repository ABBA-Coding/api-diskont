<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Products\ProductInfo;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $products = ProductInfo::where('name', 'like', '%'.$request->search.'%')
            ->orWhere('for_search', 'like', '%'.$request->search.'%')
            ->with('products', 'products.images')
            ->limit(20)
            ->get();
        $categories = Category::where('name', 'like', '%'.$request->search.'%')
            ->orWhere('for_search', 'like', '%'.$request->search.'%')
            ->limit(20)
            ->get();
        
        return response([
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
