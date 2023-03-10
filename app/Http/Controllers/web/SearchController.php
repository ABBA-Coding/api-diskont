<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $products = [];
        $categories = [];
        
        return response([
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
