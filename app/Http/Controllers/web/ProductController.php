<?php

namespace App\Http\Controllers\web;

use DB;
use App\Models\Branch;
use App\Models\Category;
use App\Models\ExchangeRate;
use App\Models\Characteristics\CharacteristicGroup;
use App\Models\Attributes\AttributeOption;
use App\Models\Attributes\Attribute;
use App\Models\Products\Product;
use App\Traits\CategoryTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use CategoryTrait;

    protected $PAGINATE = 16;
    protected function set_paginate($paginate)
    {
        $this->PAGINATE = $paginate;
    }

    public function index(Request $request)
    {
        // set items size
        if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);

        // get products
        $products = Product::select('id', 'name', 'info_id', 'model', 'price', 'slug', 'dicoin', 'is_popular')
            ->where('status', 'active');

        // filter with category
        if(isset($request->category) && $request->category != '') {

            $category = Category::where('slug', $request->category)
                ->first();
            if($category) {
                $category->children = $this->get_children($category, 1);
                
                $ids = [$category->id];
                foreach($category->children as $child) {
                    $ids[] = $child->id;
                    if(isset($child->children)) $ids = array_merge($ids, $child->children->pluck('id')->toArray());
                }
            }

            $products = $products->whereHas('info', function ($q) use ($ids) {
                $q->whereHas('category', function($qi) use ($ids) {
                    $qi->whereIn('id', $ids);
                });
            });
        }

        // filter with showcase
        if(isset($request->showcase) && $request->showcase != '') {
            
            $products = $products->whereHas('showcases', function($q) use ($request) {
                $q->where('slug', $request->showcase);
            });
        }

        // filter with prices
        $exchange_rate = ExchangeRate::latest()
            ->first()['exchange_rate'];

        if(isset($request->min_price) && $request->min_price != '' && isset($request->max_price) && $request->max_price != '') {
            $products  = $products->whereBetween('price', [$request->min_price / $exchange_rate, $request->max_price / $exchange_rate]);
        }

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

        // get products
        $products = $products->with('info', 'info.brand', 'images', 'attribute_options', 'characteristic_options', 'promotions')
            ->paginate($this->PAGINATE);

        // without lang
        $this->without_lang($products);
        foreach ($products as $product) {
            $this->without_lang($product->attribute_options);
            $this->without_lang($product->characteristic_options);
            $this->without_lang([$product->info]);
        }

        return response([
            'products' => $products
        ]);
    }

    public function show(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)
            ->with('info', 'info.brand', 'info.comments.user', 'images', 'characteristic_options', 'characteristic_options.characteristic', 'characteristic_options.characteristic.group', 'promotions')
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
        $siblings = $product->info->products->where('status', 'active');
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
        $siblings = $product->info->products->where('status', 'active');
        // dd($siblings);
        foreach($siblings as $sibling) {
            foreach($sibling->attribute_options as $attribute_option) {
                $attributes[] = [
                    'id' => $attribute_option->attribute->id,
                    'title' => $attribute_option->attribute->name
                ];
            }
        }

        $attributes = array_unique($attributes, SORT_REGULAR);

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
        if(isset($result_attributes[0])) { // oshibkadan keyin qo'shildi
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
        }

        if(count($product->attribute_options) > 0) {
            $this->without_lang($product->attribute_options);
        }
        if(count($product->characteristic_options) > 0) {
            $this->without_lang($product->characteristic_options);

            foreach ($product->characteristic_options as $characteristic_option) {
                // $this->without_lang([$characteristic_option->characteristic->group]);
                $this->without_lang([$characteristic_option->characteristic]);
            }
        }
        $this->without_lang([$product->info, $product->info->category]);
        foreach ($product->info->products as $product_1) {
            if(count($product_1->attribute_options) > 0) {
                $this->without_lang($product_1->attribute_options);
            }
            $this->without_lang([$product_1->info]);
        }
        $this->parent_without_lang($product->info->category);
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
        $this->without_lang([$product]);
        if($product->discount) $this->without_lang([$product->discount]);

        return response([
            'product' => $product,
            'attributes' => $res_without_lang,
            'characteristics' => $this->getProductCharacteristics($product),
            'branches' => $this->getProductBranches($request->lat, $request->lon),
        ]);
    }

    function parent_without_lang($category)
    {
        if($category) $this->without_lang([$category]);
        while($category->parent) {
            return $this->parent_without_lang($category->parent);
        }
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

    public function getProductCharacteristics(Product $product)
    {
        $characteristics = CharacteristicGroup::whereHas('characteristics', function($q) use ($product) {
            $q->whereHas('options', function($qi) use ($product) {
                $qi->whereHas('products', function ($qi2) use ($product) {
                    $qi2->where('products.id', $product->id);
                });
            });
        })
            ->with(['characteristics.options' => function($q) use ($product) {
                $q->whereHas('products', function ($qi) use ($product) {
                    $qi->where('products.id', $product->id);
                });
            }])
            ->get();

        $this->without_lang($characteristics);
        foreach($characteristics as $characteristic_group) {
            $this->without_lang($characteristic_group->characteristics);
            foreach($characteristic_group->characteristics as $characteristic) {
                $this->without_lang($characteristic->options);
            }
        }

        return $characteristics;
    }

    public function getProductBranches($lat, $lon)
    {
        if(is_null($lat) || is_null($lon)) {
            $branches = Branch::all();
        } else {
            $res = Http::get('https://nominatim.openstreetmap.org/reverse?format=json&lat='.$lat.'&lon='.$lon.'&zoom=6&accept-language=ru');
            $res_arr = $res->json();

            $branches = Branch::whereHas('region', function($q) use ($res_arr) {
                $q->whereHas('branchCity', function($qi) use ($res_arr) {
                    $qi->where('name', 'like', '%'.(isset($res_arr['address']['city']) ? $res_arr['address']['city'] : $res_arr['address']['state']).'%');
                });
            })
                ->get();
        }

        $this->without_lang($branches);

        return $branches;
    }
}
