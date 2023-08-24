<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Models\Category;
use App\Models\Products\Product;
use App\Models\Products\ProductInfo;
use App\Http\Resources\CategoryResource;
use App\Traits\CategoryTrait;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use CategoryTrait;

    protected $PAGINATE = 16;
    protected $PAGE = 1;
    protected function set_paginate($paginate)
    {
        $this->PAGINATE = $paginate;
    }
    protected function set_page($page)
    {
        $this->PAGE = $page;
    }

    public function index(Request $request)
    {
    	// poluchat vse kategorii?
        if($request->all && $request->all == 1)	return response($this->getAllCategoriesWithChildren(1));

    	// ustanovit limit
        if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);
        if(isset($request->page) && $request->page != '') $this->set_page($request->page);

        $get_only_popular = isset($request->popular) && $request->popular != '' && $request->popular == 1;
        $categories = $this->paginateAllCategoriesWithChildren(1, $this->PAGE, $get_only_popular);

        return response($categories);
    }

    public function show(Request $request, $slug)
    {
        if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);
        
        $category = Category::where('slug', $slug)
            ->with('parent')
            ->first();
        $category->children = $this->get_children($category, 1);

        $children = [];
        $this->getAllChildren($category, $children);

        $children_ids = array_map(function($item) {
            return $item->id;
        }, $children);

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

        $attributes = $category->attributes()->with('options')->get();

        $this->without_lang([$category]);
        $this->parent_without_lang($category);

        $this->without_lang($attributes);
        foreach ($attributes as $attribute) {
            $this->without_lang($attribute->options);
        }

        $products = Product::where([
                ['stock', '>', 0],
                ['status', 'active']
            ])
            ->whereHas('info', function ($q) use ($children_ids) {
                $q->where([
                        ['is_active', 1]
                    ])
                    ->whereIn('category_id', $children_ids);
            });

        /*
         * filtr produktov po max i min price
         */
        $exchange = ExchangeRate::latest()
            ->first()['exchange_rate'];
        if(isset($request->min_price) && $request->min_price != '' && isset($request->max_price) && $request->max_price != '') {
            $min_price = (float)$request->min_price / $exchange;
            $max_price = (float)$request->max_price / $exchange;
            $products = $products->whereBetween('price', [$min_price, $max_price]);
        }

        /*
         * sortirovka po attributam
         */
        if($request->input('attributes') && $request->input('attributes') != '') {
            $products = $products->whereHas('attribute_options', function($q) use ($request) {
               /*
                * str to arr
                */
               $attributes_ids = explode(',', $request->input('attributes'));
               $q->whereIn('attribute_options.id', $attributes_ids);
           });
        }

        $products = $products->with('info', 'images')
            ->paginate($this->PAGINATE);

        /*
         * set data lang
         */
        $this->without_lang($products);
        foreach ($products as $product) {
            $this->without_lang([$product->info]);
        }

        return response([
            'category' => $category,
            'products' => $products,
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

    // public function children_without_lang($category)
    // {
    //     $this->without_lang($category->children);

    // 	if(count($category->children) > 0) {
    // 		foreach ($category->children as $category_inner) {
    // 			$this->without_lang($category_inner->children);

    // 			$this->without_lang($category_inner->attributes);
	   //          foreach ($category_inner->attributes as $attribute) {
	   //              $this->without_lang($attribute->options);
	   //          }
    //     	}
    // 	}
    // }

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
