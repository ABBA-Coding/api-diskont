<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Characteristics\CharacteristicGroup;
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

        // get categories
        $categories = Category::whereHas('product_infos', function($q) use ($request) {
            $q->whereHas('products', function($qi) use ($request) {
                $qi->whereIn('id', $request->input('products'));
            });
        })
            ->with('parent')
            ->get();

        $parentCategories = $this->getOnlyParentCategory($categories);

        $requestCategoryId = ($request->input('category') !== null && $request->input('category') != '') ? $request->input('category') : $parentCategories->first()->id;

        $getParentCategoryCategories = $this->getParentCategoryCategories($requestCategoryId, $categories);

        // get products
        $products = Product::whereIn('id', $request->input('products'));
        if ($request->input('category') !== null && $request->input('category') != '') {
            $products = $products->whereHas('info', function ($q) use ($getParentCategoryCategories, $request) {
                $q->whereIn('category_id', $getParentCategoryCategories->pluck('id')->toArray());
            });
        }
        $products = $products->get();

        // products are exist
        if (!$products->first()) return response([
            'message' => 'Products not found'
        ], 404);

        $characteristic_groups = CharacteristicGroup::whereHas('categories', function ($q) use ($getParentCategoryCategories) {
            $q->whereIn('categories.id', $getParentCategoryCategories->pluck('id')->toArray());
        })
            ->get();

        $lang = $request->header('lang');
        if(!$lang) $lang = $this->main_lang;
        $response_characteristics = [];

        $counter = 0;
        foreach($characteristic_groups as $group) {
            $response_characteristics[$counter]['name'] = $group->name[$lang];

            $counter1 = 0;
            foreach($group->characteristics as $characteristic) {
                $response_characteristics[$counter]['characteristics'][$counter1]['name'] = $characteristic->name[$lang];

                foreach($products as $product) {
                    $response_characteristics[$counter]['characteristics'][$counter1]['products'][] = isset($this->get_option_id($characteristic->id, $product->id)[0]) ? $this->get_option_id($characteristic->id, $product->id)[0] : null;
                }

                $counter1 ++;
            }
            unset($counter1);

            $counter ++;
        }
        unset($counter);


        $this->without_lang($parentCategories);
        foreach($parentCategories as $category) {
            $this->parent_without_lang($category);
        }

        return response([
            'data' => $response_characteristics,
            'categories' => $parentCategories,
        ]);
    }

    public function get_option_id($characteristic_id, $product_id): ?array
    {
        $characteristic_option = CharacteristicOption::whereHas('products', function ($q) use ($product_id) {
            $q->where('products.id', $product_id);
        })->whereHas('characteristic', function ($q) use ($characteristic_id) {
            $q->where('id', $characteristic_id);
        })->first();

        return !$characteristic_option ? null : $this->without_lang([$characteristic_option]);
    }

    private function getOnlyParentCategory($categories)
    {
        $categories2lvlIds = [];
        foreach ($categories as $category) {
            if (!in_array($category->parent_id, $categories2lvlIds)) {
                $categories2lvlIds[] = $category->parent->id;
            }
        }

        $categoriesResult = Category::whereIn('id', $categories2lvlIds)
            ->with('parent')
            ->get();

        return $categoriesResult;
    }

    // berilgan $categories lardan $parentCategory ga tegishlilarini qaytaradi
    private function getParentCategoryCategories($parentCategoryId, $categories): \Illuminate\Support\Collection
    {
        $parentCategory = Category::find($parentCategoryId);

        $parentCategoryCategories = $this->get_children($parentCategory);
        $parentCategoryCategories = $parentCategoryCategories->filter(function ($item) use ($categories) {
            return in_array($item->id, $categories->pluck('id')->toArray());
        })->values();

        return $parentCategoryCategories;
    }
}
