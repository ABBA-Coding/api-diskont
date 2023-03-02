<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;

class BannerController extends Controller
{
    protected $PAGINATE = 16;

    public function index(Request $request)
    {
        $request->validate([
            'type' => 'required|in:main,promo,small',
        ]);

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
