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
            ->select('id', 'img', 'link', 'type')
            // ->with('parent', 'attribute_groups', 'attribute_groups.attributes', 'characteristic_groups', 'characteristic_groups.characteristics')
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
            'link' => 'nullable|array',
            'type' => 'required|in:main,promo,small',
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

        $banner = Banner::create([
            'img' => $img,
            'link' => $request->link,
            'type' => $request->type,
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
            'type' => 'required|in:main,promo,small',
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

        $img = isset($img[$this->main_lang]) ? $img : $banner->img;

        $banner->update([
            'img' => $img,
            'link' => $request->link,
            'type' => $request->type,
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
        ];
        return response([
            'types' => $types
        ]);
    }
}
