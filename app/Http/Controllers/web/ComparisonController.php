<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Characteristics\CharacteristicOption;
use App\Models\Products\Product;
use App\Traits\CategoryTrait;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    use CategoryTrait;

    public function comparison(Request $request)
    {
        $request->validate([
            'products' => 'required|array'
        ]);

        // get products
        $products = Product::whereIn('id', $request->input('products'));
        if ($request->input('category') !== null && $request->input('category') != '') {
            $products = $products->whereHas('info', function ($q) use ($request) {
                $q->where('category_id', $request->input('category'));
            });
        }
        $products = $products->get();

        // products are exist
        if (!$products->first()) return response([
            'message' => 'Products not found'
        ], 404);
        $category = ($request->input('category') !== null && $request->input('category') != '') ? $request->input('category') : $products[0]->info->category->id;

        $characteristic_groups = Category::find($category)->characteristic_groups;

        $lang = $request->header('lang');
        if(!$lang) $lang = $this->main_lang;
        $response_characteristics = [];

        $counter = 0;
        foreach($characteristic_groups as $group) {
            $response_characteristics[$counter]['name'] = $group->name[$lang];

            $counter1 = 0;
            foreach($group->characteristics as $characteristic) {
                $response_characteristics[$counter]['characteristics'][$counter1]['name'] = $characteristic->name[$lang];

                foreach($products as $productKey => $product) {
                    $response_characteristics[$counter]['characteristics'][$counter1]['products'][] = isset($this->get_option_id($characteristic->id, $product->id)[0]) ? $this->get_option_id($characteristic->id, $product->id)[0] : null;
                    // $response_characteristics[$group->name[$lang]][$characteristic->name[$lang]][$product->id] = $this->get_option_id($characteristic->id, $product->id);
                }

                $counter1 ++;
            }
            unset($counter1);

            $counter ++;
        }
        unset($counter);

        $categories = Category::whereHas('product_infos', function($q) use ($request) {
            $q->whereHas('products', function($qi) use ($request) {
                $qi->whereIn('id', $request->input('products'));
            });
        })
            ->with('parent')
            ->get();
        $this->without_lang($categories);
        foreach($categories as $category) {
            $this->parent_without_lang($category);
        }

        return response([
            'data' => $response_characteristics,
            'categories' => $categories,
        ]);
    }

    public function get_option_id($characteristic_id, $product_id)
    {
        $characteristic_option = CharacteristicOption::whereHas('products', function ($q) use ($product_id) {
            $q->where('products.id', $product_id);
        })->whereHas('characteristic', function ($q) use ($characteristic_id) {
            $q->where('id', $characteristic_id);
        })->first();

        return !$characteristic_option ? null : $this->without_lang([$characteristic_option]);
    }
}
