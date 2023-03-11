<?php

namespace App\Http\Controllers\web;

use App\Models\Attributes\Attribute;
use App\Models\Products\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $PAGINATE = 16;
    protected function set_paginate($paginate)
    {
        $this->PAGINATE = $paginate;
    }
    
    public function index(Request $request)
    {
        if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);
        $products = Product::select('id', 'info_id', 'model', 'price', 'slug')
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

    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->with('info', 'info.brand', 'info.category', 'images', 'attribute_options', 'characteristic_options')
            ->first();

        $attributes = $product->info->category->attributes()
            ->with('options')
            ->get()
            ->toArray();

        $product_attributes = $product->attribute_options()
            ->get()
            ->toArray();

        $product_sttributes_ids = [];
        foreach($product_attributes as $product_attribute) {
            $product_sttributes_ids[] = $product_attribute['id'];
        }

        foreach($attributes as $attribute) {
            foreach($attribute['options'] as $option) {
                if(in_array($option['id'], $product_sttributes_ids)) {
                    $option['active'] = 1;
                    $option['slug'] = null;
                    $option['is_not_available'] = 0;
                } else {
                    // bowqa attributlari t6gri keladigan imenno wu attributni bowqa variantlari
                    // $attribute['options']

                    // 'active' => 0,
                    // 'slug' => null,
                    // 'is_not_available' => 0,
                }
            }
        }




        dd($attributes);
        return response([
            'attributes' => $attributes,
            // 'product' => $product,
        ]);

        return response([
            'product' => $product
        ]);
    }
}
