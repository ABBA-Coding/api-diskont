<?php

namespace App\Http\Controllers\web;

use App\Models\Showcase;
use App\Models\Category;
use App\Traits\CategoryTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShowcaseController extends Controller
{
    use CategoryTrait;
    
    public function get()
    {
        $showcases = Showcase::whereHas('products', function ($q) {
                $q->where('status', 'active');
            })
            ->with(['products' => function ($q) {
                $q->where('status', 'active')
                    ->with('images', 'info', 'promotions');
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
                $q->where('status', 'active')
                    ->with('images', 'info', 'promotions');
            }])
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
}
