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
        // $request->validate([
        //     'type' => 'required|in:main,promo,small,top,medium,bottom',
        // ]);
        if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);

        $banners = Banner::latest()
            ->select('id', 'img', 'm_img', 'link', 'type');

        if(isset($request->type) && $request->type != '') {
            $banners = $banners->where('type', $request->type);
        }

        $banners = $banners->paginate($this->PAGINATE);

        $this->without_lang($banners);

        $lang = $request->header('lang') ?? $this->main_lang;
        foreach ($banners as $banner) {
            $banner->img = $banner->img[$lang] ?? ($banner->img[$this->main_lang] ?? null);
            $banner->m_img = $banner->m_img[$lang] ?? ($banner->m_img[$this->main_lang] ?? null);
        }

        return response([
            'banners' => $banners
        ]);
    }
}
