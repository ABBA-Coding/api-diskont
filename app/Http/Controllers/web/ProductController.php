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

        if(isset($request->type) && $request->type != '') {
            switch ($request->type) {
                case 'popular':
                    $products = $products->orderBy('is_popular');
                    break;

                case 'new':
                    $products = $products->orderBy('created_at');
                    break;

                case 'products_of_the_day':
                    $products = $products->where('product_of_the_day', 1);
                    break;

                case 'bestsellers':
                    // code...
                    break;
                
                default:
                    // code...
                    break;
            }
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
