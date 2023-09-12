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
    public function index(Request $request)
    {
        // if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);

        $brands = Brand::latest();

        if(isset($request->top) && $request->top == 1) {
            $brands = $brands->where('is_top', 1);
        }

        $brands = $brands->get();

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

        $this->without_lang($categories);

        return response([
            'brand' => $brand,
            'categories' => $categories,
        ]);
    }
}
