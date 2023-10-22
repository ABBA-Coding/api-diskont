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

    public function show($slug, Request $request)
    {
        $category_ids = null;
        if(isset($request->category) && $request->category != '') {
            $category = Category::where('slug', $request->category)
                ->first();

            $category_ids = [$category->id];

            foreach($this->get_children($category, 1) ?? [] as $child) {
                $category_ids[] = $child->id;
                if(isset($child->children)) {
                    foreach($child->children ?? [] as $child_i) {
                        $category_ids[] = $child_i->id;
                    }
                }
            }
        }
        $promotion = Promotion::where('slug', $slug)
        	->with(['products' => function($q) use ($request, $category_ids) {
                if(isset($request->category) && $request->category != '' && !is_null($category_ids)) {
                    $q->whereHas('info', function($qi) use ($request, $category_ids) {
                        $qi->whereHas('category', function($qi2) use ($request, $category_ids) {
                            $qi2->whereIn('id', $category_ids);
                        });
                    });
                }
            }, 'products.info', 'products.images'])
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

        $resultCategories = [];
        foreach ($categories as $category) {
            if (isset($category->parent->parent)) {
                $resultCategories[] = $category->parent;
            } else {
                $resultCategories[] = $category;
            }
        }
        $resultCategories1 = $resultCategories->filter(function ($item, $key) use ($resultCategories) {
            if (in_array($item->id, $resultCategories->pluck('id')->toArray())) $resultCategories->forget($key);
            return in_array($item->id, $resultCategories->pluck('id')->toArray());
        });

        $this->without_lang($resultCategories1);

//        $categories = $this->category_reverse($categories);

        $this->without_lang($promotion->products);

        return response([
            'promotion' => $promotion,
            'categories' => $resultCategories1,
        ]);
    }
}
