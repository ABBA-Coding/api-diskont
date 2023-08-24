<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Promotions\Promotion;
use App\Traits\CategoryTrait;

class PromotionController extends Controller
{
    use CategoryTrait;

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
}
