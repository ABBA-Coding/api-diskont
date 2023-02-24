<?php

namespace App\Http\Controllers\Products;

use App\Models\Products\{
    Product,
    ProductImage,
    ProductInfo,
};
use App\Http\Controllers\Controller;
use DB;
use Storage;
use Illuminate\Http\Request;

class ProductInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductInfo  $products_info
     * @return \Illuminate\Http\Response
     */
    public function show(ProductInfo $products_info)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductInfo  $products_info
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductInfo $products_info)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductInfo  $products_info
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductInfo $products_info)
    {
        //
    }
}
