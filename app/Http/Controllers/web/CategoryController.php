<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $PAGINATE = 16;
    
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->orderBy('position')
            ->select('id', 'name', 'is_popular', 'desc', 'icon', 'icon_svg', 'img', 'slug')
            ->with('children')
            ->paginate($this->PAGINATE);

        return response([
            'categories' => $categories
        ]);
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->with('children', 'product_infos', 'product_infos.products')
            ->first();
        return response([
            'category' => $category
        ]);
    }
}
