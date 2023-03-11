<?php

namespace App\Http\Controllers\web;

use App\Models\Attributes\AttributeOption;
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
            ->with('info', 'info.brand', 'info.category', 'images', 'attribute_options');

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
        $attribute_options_ids = [330];
        $product = Product::whereHas('attribute_options', function($q) use ($attribute_options_ids) {
            $q->whereIn('attribute_option_id', $attribute_options_ids);
        })->with('attribute_options')->get();
        dd($product);
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

        $product_attributes_ids = [];
        foreach($product_attributes as $product_attribute) {
            $product_attributes_ids[] = $product_attribute['id'];
        }

        foreach($attributes as $attribute) {
            foreach($attribute['options'] as $option) {
                if(in_array($option['id'], $product_attributes_ids)) {
                    $option['active'] = 1;
                    $option['slug'] = null;
                    $option['is_not_available'] = 0;
                } else {
                    // bowqa attributlari t6gri keladigan imenno wu attributni bowqa variantlari
                    $current_options_attribute_options_ids = AttributeOption::find($option['id'])->attribute->options->pluck('id')->toArray();
                    $other_product_attributes_ids = array_map(function($product_attributes_id) use ($current_options_attribute_options_ids) {
                        if(in_array($product_attributes_id, $current_options_attribute_options_ids)) {
                            return $option['id'];
                        }
                        return $product_attributes_id;
                    }, $product_attributes_ids);

                    // Product::whereHas();

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
