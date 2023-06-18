<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Characteristics\CharacteristicOption;
use App\Models\Products\Product;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    public function comparison(Request $request)
    {
        $request->validate([
            'products' => 'required|array'
        ]);

        $products = Product::whereIn('id', $request->products)
            ->get();

        $category = (isset($request->category) && $request->category != '') ? $request->category : $products[0]->info->category->id;

        $characteristic_groups = Category::find($category)->characteristic_groups;
        $response_characteristics = [];

        foreach($characteristic_groups as $group) {
            foreach($group->characteristics as $characteristic) {
                foreach($products as $product) {
                    $response_characteristics[$group->name['ru']][$characteristic->name['ru']][$product->id] = $this->get_option_id($characteristic->id, $product->id);
                }
            }
        }

        return response([
            'data' => $response_characteristics
        ]);
    }

    public function get_option_id($characteristic_id, $product_id)
    {
        return CharacteristicOption::whereHas('products', function ($q) use ($product_id) {
                    $q->where('products.id', $product_id);
                })->whereHas('characteristic', function ($q) use ($characteristic_id) {
                    $q->where('id', $characteristic_id);
                })->first();
    }
}
