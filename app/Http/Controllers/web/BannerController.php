<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;

class BannerController extends Controller
{
    protected $PAGINATE = 16;
    protected function set_paginate($paginate)
    {
        $this->PAGINATE = $paginate;
    }

    public function index(Request $request)
    {
        $request->validate([
            'type' => 'required|in:main,promo,small',
        ]);
        if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);

        $banners = Banner::latest()
            ->select('id', 'img', 'link', 'type');

        if(isset($request->type) && $request->type != '') {
            $banners = $banners->where('type', $request->type);
        }

        $banners = $banners->paginate($this->PAGINATE);

        return response([
            'banners' => $banners
        ]);
    }
}
