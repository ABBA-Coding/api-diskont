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
            ->with('info', 'info.brand', 'info.category', 'images', 'attribute_options', 'badges', 'characteristic_options');

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

        foreach ($products as $product) {
            $this->without_lang($product->attribute_options);
            $this->without_lang($product->characteristic_options);
            $this->without_lang([$product->info, $product->info->category]);
            $this->without_lang([$product->info->category->parent, $product->info->category->parent->parent]);
        }

        return response([
            'products' => $products
        ]);
    }

    public function show(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)
            ->with('info', 'info.brand', 'info.category', 'info.category.parent', 'images', 'characteristic_options', 'characteristic_options.characteristic', 'badges')
            // ->with('info.category.attributes', 'info.category.attributes.options')
            // ->with('attribute_options')
            ->first();

        // $characteristic_groups = [];
        // foreach($product->characteristic_options as $option) {
        //     $characteristic_groups[] = $option->characteristic->group;
        // }
        // $characteristic_groups = array_unique($characteristic_groups);
        // $counter = 0;
        // foreach($characteristic_groups as $group) {
        //     foreach($product->characteristic_options as $option) {
        //         if($option->characteristic->group_id == $group->id) {
        //             $group['characteristics'][$counter] = $option->characteristic;

        //             $counter ++;
        //         }
        //     }
        // }
        // unset($counter);
        // return response($characteristic_groups);

        /*
            produktning optionlar idlari
            [
                option_id,
                option_id,
                ...
            ]
        */
        $current_product_options_ids = $product->attribute_options->pluck('id')->toArray();
        // return response($current_product_options_ids);

        /*
            produktning optionlar idlari, attribute keylari bn
            [
                'attribute_key' => 'option_key',
                ...
            ]
        */
        foreach($current_product_options_ids as $key => $current_product_options_id) {
            $temp = Attribute::whereHas('options', function($q) use ($current_product_options_id) {
                $q->where('id', $current_product_options_id);
            })->first()->id;
            $current_product_options_ids_with_attribute_keys[$temp] = $current_product_options_id;
        }
        // return response($current_product_options_ids_with_attribute_keys);


        /*
            produktning mavjud variaciyalari
        */
        $siblings = $product->info->products;
        $real_combinations = [];
        $counter = 0;
        foreach($siblings as $sibling) {
            $real_combinations[$counter]['slug'] = $sibling->slug;
            $real_combinations[$counter]['options'] = $sibling->attribute_options->pluck('id')->toArray();

            $counter ++;
        }
        unset($counter);
        // return response($real_combinations);

        /*
            produktning mumkin b6lgan hamma variaciyalari
        */
        $attributes = [];
        $siblings = $product->info->products;
        foreach($siblings as $sibling) {
            foreach($sibling->attribute_options as $attribute_option) {
                $attributes[] = [
                    'id' => $attribute_option->attribute->id,
                    'title' => $attribute_option->attribute->name
                ];
            }
        }
        // return response($attributes);
        $attributes = array_unique($attributes, SORT_REGULAR);
        // return response($attributes);

        $result_attributes = [];
        $counter = 0;
        foreach($attributes as $attribute) {
            // return response($attribute);
            $result_attributes[$counter]['id'] = $attribute['id'];
            $result_attributes[$counter]['title'] = $attribute['title'];
            $result_attributes[$counter]['options'] = Attribute::find($attribute['id'])->options->pluck('id')->toArray();

            $counter ++;
        }
        // return response($result_attributes);

        /*
            frontga kerak k6riniwga olib kelamiz
            [
                [
                    'title' => 'Obyom',
                    'options' => [
                        [
                            'title => '256',
                            'slug' => 'obyom-256',
                            'active' => true,
                            'available' => true
                        ],
                        [
                            'title => '512',
                            'slug' => 'obyom-512',
                            'active' => true,
                            'available' => false
                        ]
                    ]
                ],
                [
                    ...
                ]
            ]
        */

        $res = [];
        $counter = 0;

        for($i=0; $i<count($result_attributes); $i++) {
            $attribute_id = $result_attributes[$i]['id'];
            foreach($result_attributes[$i]['options'] as $option) {
                // return response($current_product_options_ids_with_attribute_keys);
                $temp_this_product_options = $current_product_options_ids_with_attribute_keys;
                $temp_this_product_options[$attribute_id] = $option;
                // dd($temp_this_product_options);
                foreach($real_combinations as $real_combination) {
                    // return response($real_combination);
                    if(empty(array_diff($temp_this_product_options, $real_combination['options']))) {
                        $res[$i]['title'] = $result_attributes[$i]['title'];
                        $res[$i]['options'][$counter]['title'] = AttributeOption::find($option)->name;
                        $res[$i]['options'][$counter]['slug'] = $real_combination['slug'];
                        // return response($current_product_options_ids);
                        $res[$i]['options'][$counter]['active'] = empty(array_diff($temp_this_product_options, $current_product_options_ids)) ? true : false;
                        $res[$i]['options'][$counter]['available'] = true;

                        $counter ++;
                    }
                }
            }

            $counter = 0;
        }
        unset($counter);
        // return response($res);



        /*
            qolgan variaciyalarni ham q6wamiz
        */

        $first_attribute = $result_attributes[0];
        $temp = [];
        $counter = count($res[0]['options']);

        $slugs_array = [];
        foreach($res as $item) {
            foreach($item['options'] as $option) {
                $slugs_array[] = $option['slug'];
            }
        }
        $slugs_array = array_unique($slugs_array);
        // return response($slugs_array);

        if(count($first_attribute['options']) > count($res[0]['options'])) {
            foreach($this->combs($result_attributes) as $variants) {
                foreach($real_combinations as $real_combination) {
                    // return response($real_combination);
                    if(empty(array_diff($variants, $real_combination['options'])) && !in_array($real_combination['slug'], $slugs_array)) {
                        $res[0]['title'] = $first_attribute['title'];
                        $res[0]['options'][$counter]['title'] = AttributeOption::find($real_combination['options'][0])->name;
                        $res[0]['options'][$counter]['slug'] = $real_combination['slug'];
                        // return response($current_product_options_ids);
                        $res[0]['options'][$counter]['active'] = false;
                        $res[0]['options'][$counter]['available'] = true;

                        $counter ++;
                    }
                }
            }
        }
        unset($counter);

        $this->without_lang($product->attribute_options);
        $this->without_lang($product->characteristic_options);
        $this->without_lang([$product->info, $product->info->category]);
        foreach ($product->info->products as $product_1) {
            $this->without_lang($product_1->attribute_options);
            $this->without_lang([$product_1->info]);
        }
        $this->without_lang([$product->info->category->parent, $product->info->category->parent->parent]);
        $res_without_lang = [];
        foreach ($res as $re) {
            $lang = $request->header('lang');
            if(!$lang) $lang = $this->main_lang;

            $re['title'] = $re['title'][$lang];
            $options = [];
            foreach ($re['options'] as $option) {
                $option['title'] = $option['title'][$lang];
                $options[] = $option;
            }
            $re['options'] = $options;

            $res_without_lang[] = $re;
        }

        return response([
            'product' => $product,
            'attributes' => $res_without_lang
        ]);
    }

    function combs($arrays) {
        $result = array();
        $arrays = array_values($arrays);
        $sizeIn = sizeof($arrays);
        $size = $sizeIn > 0 ? 1 : 0;
        foreach ($arrays as $array)
            $size = $size * sizeof($array['options']);

        for ($i = 0; $i < $size; $i ++)
        {
            $result[$i] = array();
            for ($j = 0; $j < $sizeIn; $j ++) {
                array_push($result[$i], current($arrays[$j]['options']));
            }

            for ($j = ($sizeIn -1); $j >= 0; $j --)
            {
                if (next($arrays[$j]['options']))
                    break;
                elseif (isset ($arrays[$j]['options']))
                    reset($arrays[$j]['options']);
            }
        }

        return $result;
    }
}
