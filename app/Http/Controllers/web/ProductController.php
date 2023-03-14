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
        $product = Product::where('slug', $slug)
            // ->with('info', 'info.brand', 'info.category', 'images', 'attribute_options', 'characteristic_options')
            // ->with('info.category.attributes', 'info.category.attributes.options')
            ->with('attribute_options')
            ->first();
        // dd($product);

        $attributes = $product->info->category->attributes()
            ->with('options')
            ->get()
            ->toArray();

        $product_attributes_ids = $product->attribute_options()
            ->get()
            ->pluck('id')
            ->toArray();
        // dd($product_attributes_ids);

        $result = Product::whereHas('attribute_options', function($q) use ($product_attributes_ids) {
            $q->whereIn('attribute_option_id', $product_attributes_ids);
        })->get();
        dd($result);

        $attributes = array_map(function($attribute) {
            return $attribute['id'];
        }, $attributes);
        $this->sort_and_redef($attributes);
        foreach($attributes as $key => $attribute) {
            $attribute_options = Attribute::find($attribute)->options;

            
            
            foreach($attribute_options as $option_key => $option) {
                $for_delete_item_arr = $attributes;
                unset($for_delete_item_arr[$key]);
                $other_attribute_options = Attribute::whereIn('id', $for_delete_item_arr)->get();

                $target_attribute_options = [];
                foreach($other_attribute_options as $item) {
                    $target_attribute_options[] = $item->options[0]['id'];
                }








                $target_attribute_options[] = $option->id;
                $target_attribute_options_temp = $this->sort_and_redef($target_attribute_options);
                // dd($target_attribute_options_temp);

                dd($product->attribute_options->pluck('id')->toArray());

                if(in_array($option['id'], $product_attributes_ids)) {
                    $option['active'] = 1;
                    $option['slug'] = null;
                    $option['is_not_available'] = 0;
                } else {}
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

    private function sort_and_redef($arr)
    {
        asort($arr);
        return array_values($arr);
    }
}
