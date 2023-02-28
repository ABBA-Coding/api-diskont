<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;

class BrandController extends Controller
{
    protected $PAGINATE = 16;

    public function index()
    {
        $brands = Brand::latest()
            ->paginate($this->PAGINATE);

        return response([
            'brands' => $brands
        ]);
    }
}
