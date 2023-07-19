<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Products\ProductInfo;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $PAGINATE = 16;
    protected function set_paginate($paginate)
    {
        $this->PAGINATE = $paginate;
    }

    public function index(Request $request)
    {
        if($request->all && $request->all == 1) {
            $categories = Category::whereNull('parent_id')->with('children')->get();

            $this->without_lang($categories);
            foreach ($categories as $value) {
                $this->children_without_lang($value);
            }

            return response([
                'categories' => $categories
            ]);
        }

        if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);
        $categories = Category::whereNull('parent_id')
            ->orderBy('position')
            ->select('id', 'name', 'is_popular', 'desc', 'icon', 'icon_svg', 'img', 'slug')
            ->with('children');

        if(isset($request->popular) && $request->popular != '' && $request->popular == 1) {
            $categories = $categories->where('is_popular', 1);
        }

        $categories = $categories->paginate($this->PAGINATE);

        $this->without_lang($categories);
        foreach ($categories as $value) {
            $this->children_without_lang($value);
        }

        return response([
            'categories' => $categories
        ]);
    }

    public function show(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)
            ->with('children', 'parent', 'children')
            ->first();

        $children = [];
        $this->getAllChildren($category, $children);

        $children_ids = array_map(function($item) {
            return $item->id;
        }, $children);


        $product_infos = ProductInfo::whereIn('category_id', $children_ids);
        /*
         * filtr produktov po max i min price
         */
        if(isset($request->min_price) && $request->min_price != '' && isset($request->max_price) && $request->max_price != '') {
            $min_price = (float)$request->min_price;
            $max_price = (float)$request->max_price;
            $product_infos = $product_infos->whereHas('default_product', function($q) use ($min_price, $max_price) {
                $q->whereBetween('price', [$min_price, $max_price]);
            });
        }

        /*
         * sortirovka produktov
         */
        if(isset($request->sort) && $request->sort != '') {
            switch($request->sort) {
                case 'popular':
                    //
                    break;
                case 'cheap_first':
                    //
                    break;
                case 'expensive_first':
                    //
                    break;
                case 'new':
                    //
                    break;
                case 'high_rating':
                    //
                    break;
            }
        }

        /*
         * sortirovka po attributam
         */
        if($request->input('attributes') && $request->input('attributes') != '') {
            $product_infos = $product_infos->whereHas('products', function($q) use ($request) {
                $q->whereHas('attribute_options', function($qi) use ($request) {
                    /*
                     * str to arr
                     */
                    $attributes_ids = explode(',', $request->input('attributes'));
                    $qi->whereIn('attribute_options.id', $attributes_ids);
                });
            });
        }
        $product_infos = $product_infos->with('default_product', 'default_product.images', 'default_product.attribute_options')
            ->get();

        $attributes = $category->attributes()->with('options')->get();

        $this->without_lang([$category]);
        $this->children_without_lang($category);
        $this->parent_without_lang($category);

        $this->without_lang($product_infos);
        $this->without_lang($attributes);
        foreach ($attributes as $attribute) {
            $this->without_lang($attribute->options);
        }

        return response([
            'category' => $category,
            'product_infos' => $product_infos,
            'attributes' => $attributes,
        ]);
    }

    private function getAllChildren($category, &$children)
    {
        $children[] = $category;
        if(empty($category->children)) return $category;

        foreach($category->children as $child) {
            $this->getAllChildren($child, $children);
        }
    }

    public function children_without_lang($category)
    {
        while(count($category->children) > 0) {
            $this->without_lang($category->children);
            foreach ($category->children as $value) {
                $this->without_lang($value->attributes);
                foreach ($value->attributes as $attribute) {
                    $this->without_lang($attribute->options);
                }
                return self::children_without_lang($value);
            }
        }
    }

    public function parent_without_lang($category)
    {
        while($category->parent) {
            $this->without_lang([$category->parent]);
            $this->without_lang($category->parent->attributes);
            foreach ($category->parent->attributes as $attribute) {
                $this->without_lang($attribute->options);
            }
            return self::children_without_lang($category->parent);
        }
    }
}
