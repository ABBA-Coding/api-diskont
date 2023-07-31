<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promotions\Promotion;

class PromotionController extends Controller
{
    protected $PAGINATE = 16;
    protected function set_paginate($paginate)
    {
        $this->PAGINATE = $paginate;
    }

    public function index(Request $request)
    {
        if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);
        $promotions = Promotion::latest()
            ->paginate($this->PAGINATE);

        $this->without_lang($promotions);

        return response([
            'promotions' => $promotions
        ]);
    }

    public function show($slug)
    {
        $promotion = Promotion::where('slug', $slug)
        	->with('products')
            ->first();

        if($promotion) $this->without_lang([$promotion]);

        return response([
            'promotion' => $promotion,
        ]);
    }
}
