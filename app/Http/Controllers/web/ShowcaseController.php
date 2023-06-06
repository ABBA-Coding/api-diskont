<?php

namespace App\Http\Controllers\web;

use App\Models\Showcase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShowcaseController extends Controller
{
    public function get()
    {
        $showcases = Showcase::with('products', 'products.info', 'products.images')
            ->get();

        return response([
            'showcases' => $showcases
        ]);
    }
}
