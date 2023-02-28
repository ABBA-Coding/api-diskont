<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Products\Product;

class ProductController extends Controller
{
    protected $PAGINATE = 16;

    public function index(Request $request)
    {
        $products = Product::select('id', 'info_id', 'model', 'price')
            ->with('info', 'info.brand', 'info.category', 'images');

        if(isset($request->bestsellers)) {
            $products = $products->orderBy('position');
        }

        if(isset($request->popular)) {
            $products = $products->orderBy('is_popular');
        }

        if(isset($request->new)) {
            $products = $products->orderBy('created_at');
        }

        if(isset($request->category) && $request->category != '') {
            $products = $products->whereHas('info', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        $products = $products->paginate($this->PAGINATE);

        return response([
            'products' => $products
        ]);
    }
}
