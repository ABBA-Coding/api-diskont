<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banners = Banner::latest()
            ->select('id', 'showcase_id', 'img', 'm_img', 'link', 'type')
            ->with('showcase')
            ->paginate($this->PAGINATE);

        return response([
            'banners' => $banners
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'img' => 'array|required',
            'img.' . $this->main_lang => 'required',
            'm_img' => 'array|required',
            'm_img.' . $this->main_lang => 'required',
            'link' => 'nullable|array',
            'type' => 'required|in:main,promo,small,top,medium,bottom,product_of_the_day,type1,type2',
            'showcase_id' => 'nullable|integer',
        ]);

        foreach($request->img as $key => $item) {
            if(isset($request->img[$key]) && Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->img[$key])[count(explode('/', $request->img[$key])) - 1])) {
                $explode_logo = explode('/', $request->img[$key]);
                Storage::disk('public')->move('/uploads/temp/' . $explode_logo[count($explode_logo) - 1], '/uploads/banners/' . $explode_logo[count($explode_logo) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $explode_logo[count($explode_logo) - 1], '/uploads/banners/200/' . $explode_logo[count($explode_logo) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $explode_logo[count($explode_logo) - 1], '/uploads/banners/600/' . $explode_logo[count($explode_logo) - 1]);
                $img[$key] = $explode_logo[count($explode_logo) - 1];
            }
        }
        $img = isset($img[$this->main_lang]) ? $img : [];

        foreach($request->m_img as $key => $item) {
            if(isset($request->m_img[$key]) && Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->m_img[$key])[count(explode('/', $request->m_img[$key])) - 1])) {
                $explode_logo = explode('/', $request->m_img[$key]);
                Storage::disk('public')->move('/uploads/temp/' . $explode_logo[count($explode_logo) - 1], '/uploads/banners/' . $explode_logo[count($explode_logo) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $explode_logo[count($explode_logo) - 1], '/uploads/banners/200/' . $explode_logo[count($explode_logo) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $explode_logo[count($explode_logo) - 1], '/uploads/banners/600/' . $explode_logo[count($explode_logo) - 1]);
                $m_img[$key] = $explode_logo[count($explode_logo) - 1];
            }
        }
        $m_img = isset($m_img[$this->main_lang]) ? $m_img : [];

        $banner = Banner::create([
            'img' => $img,
            'm_img' => $m_img,
            'link' => $request->link,
            'type' => $request->type,
            'showcase_id' => $request->showcase_id,
        ]);

        return response([
            'banner' => $banner
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function show(Banner $banner)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'img' => 'array|required',
            'img.' . $this->main_lang => 'required',
            'link' => 'nullable|array',
            'type' => 'required|in:main,promo,small,top,medium,bottom,product_of_the_day,type1,type2',
            'showcase_id' => 'nullable|integer',
        ]);

        foreach($request->img as $key => $item) {
            if(isset($request->img[$key]) && Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->img[$key])[count(explode('/', $request->img[$key])) - 1])) {
                $explode_logo = explode('/', $request->img[$key]);
                Storage::disk('public')->move('/uploads/temp/' . $explode_logo[count($explode_logo) - 1], '/uploads/banners/' . $explode_logo[count($explode_logo) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $explode_logo[count($explode_logo) - 1], '/uploads/banners/200/' . $explode_logo[count($explode_logo) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $explode_logo[count($explode_logo) - 1], '/uploads/banners/600/' . $explode_logo[count($explode_logo) - 1]);
                $img[$key] = $explode_logo[count($explode_logo) - 1];
            } else {
                $img[$key] = $banner->img[$key] ?? null;
            }
        }

        foreach($request->m_img as $key => $item) {
            if(isset($request->m_img[$key]) && Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->m_img[$key])[count(explode('/', $request->m_img[$key])) - 1])) {
                $explode_logo = explode('/', $request->m_img[$key]);
                Storage::disk('public')->move('/uploads/temp/' . $explode_logo[count($explode_logo) - 1], '/uploads/banners/' . $explode_logo[count($explode_logo) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $explode_logo[count($explode_logo) - 1], '/uploads/banners/200/' . $explode_logo[count($explode_logo) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $explode_logo[count($explode_logo) - 1], '/uploads/banners/600/' . $explode_logo[count($explode_logo) - 1]);
                $m_img[$key] = $explode_logo[count($explode_logo) - 1];
            } else {
                $m_img[$key] = $banner->m_img[$key] ?? null;
            }
        }

        $banner->update([
            'img' => $img,
            'm_img' => $m_img,
            'link' => $request->link,
            'type' => $request->type,
            'showcase_id' => $request->showcase_id,
        ]);

        return response([
            'banner' => $banner
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function destroy(Banner $banner)
    {
        foreach($banner->img as $item) {
            $this->delete_files([
                public_path('/uploads/banners/200/' . $item),
                public_path('/uploads/banners/600/' . $item),
                public_path('/uploads/banners/' . $item),
            ]);
        }
        foreach($banner->m_img as $item) {
            $this->delete_files([
                public_path('/uploads/banners/200/' . $item),
                public_path('/uploads/banners/600/' . $item),
                public_path('/uploads/banners/' . $item),
            ]);
        }
        $banner->delete();

        return response([
            'message' => __('messages.successfully_deleted')
        ]);
    }

    public function types()
    {
        $types = [
            'main' => 'main',
            'promo' => 'promo',
            'small' => 'small',
            'top' => 'top',
            'medium' => 'medium',
            'bottom' => 'bottom',
            'product_of_the_day' => 'product_of_the_day',
            'type1' => 'type1',
            'type2' => 'type2',
        ];
        return response([
            'types' => $types
        ]);
    }
}
