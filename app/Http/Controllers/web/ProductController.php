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
            // ->with('info', 'info.brand', 'info.category', 'images', 'characteristic_options', 'characteristic_options.characteristic')
            // ->with('info.category.attributes', 'info.category.attributes.options')
            ->with('attribute_options')
            ->first();
        // return response($product);

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
                $temp_this_product_options = $current_product_options_ids_with_attribute_keys;
                $temp_this_product_options[$attribute_id] = $option;
                // var_dump($temp_this_product_options);echo '<br>';
                // return response($temp_this_product_options);
                foreach($real_combinations as $real_combination) {
                    // return response($temp_this_product_options);
                    if(empty(array_diff($temp_this_product_options, $real_combination['options']))) {
                        $res[$counter]['title'] = 'title-' . $counter;
                        $res[$counter]['options']['title'] = 'option-title-' . $counter;
                        $res[$counter]['options']['slug'] = $real_combination['slug'];
                        $res[$counter]['options']['active'] = true;
                        $res[$counter]['options']['available'] = true;

                        $counter ++;
                    }
                }
            }
        }

        foreach($real_combinations as $real_combination) {
            $res[$counter]['title'] = 'title-' . $counter;
            $res[$counter]['options']['title'] = 'option-title-' . $counter;
            $res[$counter]['options']['slug'] = $real_combination['slug'];
            $res[$counter]['options']['active'] = true;
            $res[$counter]['options']['available'] = true;

            $counter ++;
        }
        unset($counter);
        // return response($res);

        /*
            sdelat massiv s unikalnimi znacheniyami
        */
        $temp = [];
        $unique_res = [];
        foreach($res as $item) {
            if(!in_array($item['options']['slug'], $temp)) {
                $temp[] = $item['options']['slug'];
                
                $unique_res[] = $item;
            }
        }
        unset($temp);
        return response($unique_res);




        return response($product);
    }

    // public function show($slug)
    // {
    //     $product = Product::where('slug', $slug)
    //         // ->with('info', 'info.brand', 'info.category', 'images', 'characteristic_options', 'characteristic_options.characteristic')
    //         // ->with('info.category.attributes', 'info.category.attributes.options')
    //         ->with('attribute_options')
    //         ->first();
    //     // return response($product);

    //     $current_product_options_ids = $product->attribute_options->pluck('id')->toArray();
    //     // id options dannogo produkta v formate [attribute_id => option_id, ...]
    //     $current_product_options_ids_with_attribute_keys = [];
        
    //     foreach($current_product_options_ids as $key => $current_product_options_id) {
    //         $temp = Attribute::whereHas('options', function($q) use ($current_product_options_id) {
    //             $q->where('id', $current_product_options_id);
    //         })->first()->id;
    //         $current_product_options_ids_with_attribute_keys[$temp] = $current_product_options_id;
    //     }
    //     // return response($current_product_options_ids_with_attribute_keys);

    //     // vse attributi s optionami
    //     $attributes = [];
    //     $attribute_options = [];
    //     $siblings = $product->info->products;
    //     foreach($siblings as $sibling) {
    //         foreach($sibling->attribute_options as $attribute_option) {
    //             $attributes[] = $attribute_option->attribute->id;
    //         }
    //     }
    //     $unique_attributes_ids = array_unique($attributes);

    //     $result_attributes = [];
    //     $counter = 0;
    //     foreach($unique_attributes_ids as $unique_attributes_id) {
    //         $result_attributes[$counter]['id'] = $unique_attributes_id;
    //         $result_attributes[$counter]['options'] = Attribute::find($unique_attributes_id)->options->pluck('id')->toArray();

    //         $counter ++;
    //     }
    //     unset($counter);
    //     // return response($result_attributes);





    //     $result_arr = [];
    //     $attributes_count = count($result_attributes);
        
    //     for($i=0; $i<$attributes_count; $i++) {
    //         // $result_arr[$result_attributes[$i]['id']] = $result_attributes[$i]['options'][0];
    //         $result_arr[] = $result_attributes[$i]['options'][0];
    //     }
    //     // return response($result_arr);


    //     // for($l=0; $l<count($result_attributes[0]['options']); $l++) {
    //     //     for($k=0; $k<count($result_attributes[1]['options']); $k++) {
    //     //         for ($i=0; $i<count($result_attributes[2]['options']); $i++) {
    //     //             for($j=0; $j<count($result_attributes[3]['options']); $j++) {
    //     //                 $result_arr[$result_attributes[3]['id']] = $result_attributes[3]['options'][$j];
    //     //                 dd($result_arr);
    //     //                 var_dump($result_arr);echo "<br>";
    //     //             }
    //     //             $result_arr[$result_attributes[3]['id']] = $result_attributes[3]['options'][0];

    //     //             if($i != count($result_attributes[2]['options']) -1) {
    //     //                 $result_arr[$result_attributes[2]['id']] = $result_attributes[2]['options'][$i+1];
    //     //             }
    //     //         }
    //     //         $result_arr[$result_attributes[2]['id']] = $result_attributes[2]['options'][0];

    //     //         if($k != count($result_attributes[1]['options']) -1) {
    //     //             $result_arr[$result_attributes[1]['id']] = $result_attributes[1]['options'][$k+1];
    //     //         }
    //     //     }
    //     //     $result_arr[$result_attributes[1]['id']] = $result_attributes[1]['options'][0];

    //     //     if($l != count($result_attributes[0]['options']) -1) {
    //     //         $result_arr[$result_attributes[0]['id']] = $result_attributes[0]['options'][$l+1];
    //     //     }
    //     // }
    //     // exit();


    //     // for($i1 = 0; $i1 < count($result_attributes[0]['options']); $i1 ++){
    //     //     for($i2 = 0; $i2 < count($result_attributes[1]['options']); $i2 ++){
    //     //         for($i3 = 0; $i3 < count($result_attributes[2]['options']); $i3 ++){
    //     //             for($i4 = 0; $i4 < count($result_attributes[3]['options']); $i4 ++){

    //     //                 $variant = [
    //     //                     $result_attributes[0]['id'] => $result_attributes[0]['options'][$i1],
    //     //                     $result_attributes[1]['id'] => $result_attributes[1]['options'][$i2],
    //     //                     $result_attributes[2]['id'] => $result_attributes[2]['options'][$i3],
    //     //                     $result_attributes[3]['id'] => $result_attributes[3]['options'][$i4],
    //     //                 ];
    //     //                 var_dump($variant);echo "<br>";

    //     //             }
    //     //         }
    //     //     }
    //     // }
    //     // exit();



    //     // vse vozmojnie kombinacii produktov
    //     $real_combinations = [];
    //     $counter = 0;
    //     foreach($siblings as $sibling) {
    //         $real_combinations[$counter]['slug'] = $sibling->slug;
    //         $real_combinations[$counter]['options'] = $sibling->attribute_options->pluck('id')->toArray();

    //         $counter ++;
    //     }
    //     unset($counter);
    //     return response($real_combinations);


    //     foreach($this->combs($result_attributes) as $item) {
    //         foreach($real_combinations as $real_combination) {
    //             $temp_arr = array_diff($item, $real_combination['options']);
    //             // if(empty($temp_arr))     
    //         }
    //         var_dump($item); echo '<br>';
    //     }
    //     exit();




    //     return response([
    //         'attributes' => $attributes,
    //         'product' => $product,
    //     ]);
    // }







    // for($i1 = 0; $i1 < count($result_attributes[0]['options']); $i1 ++){
    //     for($i2 = 0; $i2 < count($result_attributes[1]['options']); $i2 ++){
    //         for($i3 = 0; $i3 < count($result_attributes[2]['options']); $i3 ++){
    //             for($i4 = 0; $i4 < count($result_attributes[3]['options']); $i4 ++){

    //                 $variant = [
    //                     $result_attributes[0]['id'] => $result_attributes[0]['options'][$i1],
    //                     $result_attributes[1]['id'] => $result_attributes[1]['options'][$i2],
    //                     $result_attributes[2]['id'] => $result_attributes[2]['options'][$i3],
    //                     $result_attributes[3]['id'] => $result_attributes[3]['options'][$i4],
    //                 ];
    //                 var_dump($variant);echo "<br>";

    //             }
    //         }
    //     }
    // }


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
















    private function sort_and_redef($arr)
    {
        asort($arr);
        return array_values($arr);
    }
}
