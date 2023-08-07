<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Promotions\Promotion;

class PromotionController extends Controller
{
    protected $PAGINATE = 16;
    protected function set_paginate($paginate)
    {
        $this->PAGINATE = $paginate;
    }

    public function index(Request $request)
    {
        if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);
        $promotions = Promotion::latest()
            ->paginate($this->PAGINATE);

        $this->without_lang($promotions);

        return response([
            'promotions' => $promotions
        ]);
    }

    public function show($slug)
    {
        $promotion = Promotion::where('slug', $slug)
        	->with('products', 'products.info', 'products.images')
            ->first();

        if($promotion) {
            $this->without_lang([$promotion]);
            foreach ($promotion->products as $product) {
                $this->without_lang([$product->info]);
            }
        };

        $categories = Category::whereHas('product_infos', function ($q) use ($slug) {
            $q->whereHas('products', function ($qi) use ($slug) {
                $qi->whereHas('promotions', function ($qi2) use ($slug) {
                    $qi2->where('slug', $slug);
                });
            });
        })->with('parent')->get();

        $this->without_lang($categories);
        $categories = $this->category_reverse($categories);

        $this->without_lang($promotion->products);

        return response([
            'promotion' => $promotion,
            'categories' => $categories,
        ]);
    }

    function category_reverse($categories): array
    {
        $result = [];
        foreach ($categories as $key => $category) {
            $this->parent_without_lang($category);

            $to_1_lvl = [];
            $counter = 0;
            while($category) {
                $to_1_lvl[$counter]['id'] = $category->id;
                $to_1_lvl[$counter]['name'] = $category->name;
                $to_1_lvl[$counter]['desc'] = $category->desc;
                $to_1_lvl[$counter]['icon'] = $category->icon;
                $to_1_lvl[$counter]['icon_svg'] = $category->icon_svg;
                $to_1_lvl[$counter]['sm_img'] = $category->sm_img;
                $to_1_lvl[$counter]['md_img'] = $category->md_img;
                $to_1_lvl[$counter]['lg_img'] = $category->lg_img;
                $to_1_lvl[$counter]['slug'] = $category->slug;

                $category = $category->parent;

                $counter ++;
            }
            unset($counter);
            $to_1_lvl = array_reverse($to_1_lvl);

            $counter = 0;
            foreach ($to_1_lvl as $item) {
                if($counter == 0) $result[$key] = $item;
                else if (isset($result[$key]['children'][0]['children'][0])){
                    $result[$key]['children'][0]['children'][0]['children'][0] = $item;
                }
                else if (isset($result[$key]['children'][0])){
                    $result[$key]['children'][0]['children'][0] = $item;
                }
                else if (!isset($result[$key]['children'])) {
                    $result[$key]['children'][0] = $item;
                }

                $counter ++;
            }
            unset($counter);
        }

        return $result;
    }

    public function parent_without_lang($category)
    {
        while($category->parent) {
            $this->without_lang([$category->parent]);
            $this->without_lang($category->parent->attributes);
            foreach ($category->parent->attributes as $attribute) {
                $this->without_lang($attribute->options);
            }
            return self::parent_without_lang($category->parent);
        }
    }
}
