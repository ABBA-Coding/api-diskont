<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Products\Product;
use App\Models\Products\ProductInfo;

class BrandController extends Controller
{
    protected $PAGINATE = 16;
    protected function set_paginate($paginate)
    {
        $this->PAGINATE = $paginate;
    }

    public function index(Request $request)
    {
        if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);

        $brands = Brand::latest();

        if(isset($request->top) && $request->top == 1) {
            $brands = $brands->where('is_top', 1)
                ->get();
        } else {
            $brands = $brands->paginate($this->PAGINATE);
        }

        return response([
            'brands' => $brands
        ]);
    }

    public function show(Request $request, $slug)
    {
        $brand = Brand::where('slug', $slug)
            ->first();

        $categories = Category::whereHas('product_infos', function($q) use ($slug) {
            $q->whereHas('brand', function($qi) use ($slug) {
                $qi->where('slug', $slug);
            });
        })
            ->get();

        $products = ProductInfo::where('brand_id', $brand->id);
        if(isset($request->min_price) && $request->min_price != '') {
            $products = $products->whereHas('products', function($q) use ($request) {
                $q->where('price', '>', $request->min_price);
            });
        }
        if(isset($request->max_price) && $request->max_price != '') {
            $products = $products->whereHas('products', function($q) use ($request) {
                $q->where('price', '<', $request->max_price);
            });
        }
        if(isset($request->category) && $request->category != '') {
            $category = Category::where('slug', $request->category)
                ->first();
            if($category) {
                $products = $products->where('category_id', $category->id);
            } else {
                return response([
                    'message' => __('messages.category_not_found')
                ], 404);
            }
        }
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
        $products = $products->with('default_product', 'default_product.images', 'default_product.attribute_options')
            ->paginate($this->PAGINATE);

        $this->without_lang($categories);
        $this->without_lang($products);


        $products_new = Product::where([
                ['stock', '>', 0],
                ['status', 'active']
            ])->whereHas('info', function ($q) use ($brand) {
                $q->where([
                    ['brand_id', $brand->id],
                    ['is_active', 1]
                ]);
            });

        if(isset($request->min_price) && $request->min_price != '') {
            $products_new = $products_new->where('price', '>', $request->min_price);
        }
        if(isset($request->max_price) && $request->max_price != '') {
            $products_new = $products_new->where('price', '<', $request->max_price);
        }
        if(isset($request->category) && $request->category != '') {
            $category = Category::where('slug', $request->category)
                ->first();
            if($category) {
                $products_new = $products_new->whereHas('info', function ($q) use ($category) {
                    $q->where('category_id', $category->id);
                });
            } else {
                return response([
                    'message' => __('messages.category_not_found')
                ], 404);
            }
        }

        $products_new = $products_new->with('images', 'promotions')
            ->paginate($this->PAGINATE);

        $this->without_lang($products_new);

        return response([
            'brand' => $brand,
            'categories' => $categories,
            'products' => $products,
            'products_new' => $products_new
        ]);
    }
}
