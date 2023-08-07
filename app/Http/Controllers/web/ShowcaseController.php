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
        $showcases = Showcase::whereHas('products', function ($q) {
                $q->where('status', 'active');
            })
            ->with(['products' => function ($q) {
                $q->where('status', 'active')
                    ->with('images', 'info');
            }])
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
            ->with(['products' => function ($q) {
            $q->where([
                ['status', 'active'],
                ['stock', '>', 0]
            ]);
        }], 'products.info', 'products.images')
            ->whereHas('products', function ($q) {
                $q->where([
                    ['status', 'active'],
                    ['stock', '>', 0]
                ]);
            })
            ->first();

        $categories = Category::whereHas('product_infos', function ($q) use ($slug) {
            $q->whereHas('products', function ($qi) use ($slug) {
                $qi->whereHas('showcases', function ($qi2) use ($slug) {
                    $qi2->where('slug', $slug);
                });
            });
        })->with('parent')->get();

        $this->without_lang($categories);
        $categories = $this->category_reverse($categories);


        $this->without_lang([$showcase]);
        foreach ($showcase->products as $product) {
            $this->without_lang([$product, $product->info]);
        }

        return response([
            'showcase' => $showcase,
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
