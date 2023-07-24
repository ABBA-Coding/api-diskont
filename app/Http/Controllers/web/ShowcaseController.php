<?php

namespace App\Http\Controllers\web;

use App\Models\Showcase;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShowcaseController extends Controller
{
    public function get()
    {
        $showcases = Showcase::with('products', 'products.info', 'products.images')
            ->whereHas('products', function ($q) {
                $q->where('status', 'active');
            })
            ->get();

        $this->without_lang($showcases);
        foreach ($showcases as $value) {
            foreach ($value->products as $product) {
                $this->without_lang([$product->info]);
            }
        }

        return response([
            'showcases' => $showcases
        ]);
    }

    public function show($slug)
    {
        $showcase = Showcase::where('slug', $slug)
            ->with('products', 'products.info', 'products.images')
            ->first();

        $categories = Category::whereHas('product_infos', function ($q) use ($slug) {
            $q->whereHas('products', function ($qi) use ($slug) {
                $qi->whereHas('showcases', function ($qi2) use ($slug) {
                    $qi2->where('slug', $slug);
                });
            });
        })->with('parent')->get();
        // return response($this->category_reverse($categories));


        $this->without_lang([$showcase]);
        foreach ($showcase->products as $product) {
            $this->without_lang([$product->info]);
        }

        $this->without_lang($categories);
        foreach ($categories as $category) {
            $this->parent_without_lang($category);
        }

        return response([
            'showcase' => $showcase,
            'categories' => $categories,
        ]); 
    }

    function category_reverse($categories)
    {
        $reversed = [];

        foreach ($categories as $key => $category) {

            $temp_category = $category;
            while($temp_category) {
                $reversed[$key][] = $temp_category;

                $temp_category = $temp_category->parent;
            }
        }

        $result = [];
        foreach ($reversed as $key => $cat_arr) {

            // $counter = count($cat_arr) - 1;
            // while($counter) {
            //     if($counter+1 == count($cat_arr)) {
            //         $result[$key] = $cat_arr[$counter];
            //     } else {
            //         $result[$key]['child'] = $cat_arr[$counter];
            //     }

            //     $counter --;
            // }
            // $result[$key]
        }

        return $reversed;
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
