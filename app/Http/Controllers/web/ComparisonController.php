<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
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
                    // $response_characteristics[$characteristic->id][$product->id] = 
                }
            }
        }
    }

    public function get_option_id($characteristic_id, $product)
    {
        $characteristics = Characteristic::find($characteristic_id)->options;
    }
}
