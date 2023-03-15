<?php

namespace App\Http\Controllers\web;

use DB;
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
            ->with('info', 'info.brand', 'info.category', 'images', 'characteristic_options', 'characteristic_options.characteristic')
            // ->with('info.category.attributes', 'info.category.attributes.options')
            // ->with('attribute_options')
            ->first();

        return response([
            'product' => $product,
            'attributes' => []
        ]);

        $current_product_options_ids = $product->attribute_options->pluck('id')->toArray();
        // id options dannogo produkta v formate [attribute_id => option_id, ...]
        $current_product_options_ids_with_attribute_keys = [];
        
        foreach($current_product_options_ids as $key => $current_product_options_id) {
            $temp = Attribute::whereHas('options', function($q) use ($current_product_options_id) {
                $q->where('id', $current_product_options_id);
            })->first()->id;
            $current_product_options_ids_with_attribute_keys[$temp] = $current_product_options_id;
        }

        // vse attributi s optionami
        $attributes = [];
        $attribute_options = [];
        $siblings = $product->info->products;
        foreach($siblings as $sibling) {
            foreach($sibling->attribute_options as $attribute_option) {
                $attributes[] = $attribute_option->attribute->id;
            }
        }
        $unique_attributes_ids = array_unique($attributes);
        $result_attributes = [];
        $counter = 0;
        foreach($unique_attributes_ids as $unique_attributes_id) {
            $result_attributes[$counter]['id'] = $unique_attributes_id;
            $result_attributes[$counter]['options'] = Attribute::find($unique_attributes_id)->options->pluck('id')->toArray();

            $counter ++;
        }
        unset($counter);



        // vse vozmojnie kombinacii produktov
        $real_combinations = [];
        $counter = 0;
        foreach($siblings as $sibling) {
            $real_combinations[$counter]['slug'] = $sibling->slug;
            $real_combinations[$counter]['options'] = $sibling->attribute_options->pluck('id')->toArray();

            $counter ++;
        }
        unset($counter);

        $slugs = [];
        $temp_current_product_options_ids_with_attribute_keys = $current_product_options_ids_with_attribute_keys;
        foreach($result_attributes as $result_attribute) {
            unset($temp_current_product_options_ids_with_attribute_keys[$result_attribute['id']]);

            foreach($result_attribute['options'] as $option) {
                $temp_current_product_options_ids_with_attribute_keys[$result_attribute['id']] = $option;
                var_dump($this->sort_and_redef($temp_current_product_options_ids_with_attribute_keys));
                foreach($real_combinations as $real_combination) {
                    if(empty(array_diff($temp_current_product_options_ids_with_attribute_keys, $real_combination['options']))) {
                        $slugs[] = $real_combination['slug'];
                    } else {
                        $slugs[] = null;
                    }
                }

                $temp_current_product_options_ids_with_attribute_keys = $current_product_options_ids_with_attribute_keys;
            }
        }
        return response($slugs);











        $attributes_with_options = [];
        $counter = 0;
        foreach($unique_attributes as $attribute) {
            $temp_attribute = Attribute::find($attribute);
            $attributes_with_options[$counter]['id'] = $temp_attribute->id;
            $attributes_with_options[$counter]['name'] = $temp_attribute->name;
            $attributes_with_options[$counter]['options'] = $temp_attribute->options->toArray();

            $counter ++;
        }
        unset($counter);

        $counter = 0;
        foreach($attributes_with_options as $attribute_with_option) {
            $inner_counter = 0;
            foreach($attribute_with_option['options'] as $option) {
                $array_with_this_options_ids = $current_product_options_ids_with_attribute_keys;
                $array_with_this_options_ids[$option['attribute_id']] = $option['id'];

                foreach($real_combinations as $real_combination) {
                    if(empty(array_diff($real_combination['options'], $array_with_this_options_ids))) {
                        $attributes_with_options[$counter]['options'][$inner_counter]['slug'] = $real_combination['slug'];                    } else {
                        $attributes_with_options[$counter]['options'][$inner_counter]['slug'] = null;
                    }
                }
                $inner_counter ++;
            }
            $counter ++;
        }
        unset($inner_counter);
        unset($counter);
        $attributes_with_options[0]['options'][3]['slug'] = $real_combination['slug'];




        dd($attributes);
        return response([
            'attributes' => $attributes,
            'product' => $product,
        ]);
    }

    private function sort_and_redef($arr)
    {
        asort($arr);
        return array_values($arr);
    }
}
