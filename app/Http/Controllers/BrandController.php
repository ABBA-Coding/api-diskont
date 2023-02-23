<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Support\Facades\Storage;
use DB;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $brands = Brand::latest()
            ->select('id', 'name', 'logo')
            // ->with('parent', 'attribute_groups', 'attribute_groups.attributes', 'characteristic_groups', 'characteristic_groups.characteristics')
            ->paginate($this->PAGINATE);

        return response([
            'brands' => $brands
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
            'name' => 'required|max:255',
            'logo' => 'nullable|max:255',
        ]);

        if($request->logo && Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->logo)[count(explode('/', $request->logo)) - 1])) {
            $explode_logo = explode('/', $request->logo);
            Storage::disk('public')->move('/uploads/temp/' . $explode_logo[count($explode_logo) - 1], '/uploads/brands/' . $explode_logo[count($explode_logo) - 1]);
            Storage::disk('public')->move('/uploads/temp/200/' . $explode_logo[count($explode_logo) - 1], '/uploads/brands/200/' . $explode_logo[count($explode_logo) - 1]);
            Storage::disk('public')->move('/uploads/temp/600/' . $explode_logo[count($explode_logo) - 1], '/uploads/brands/600/' . $explode_logo[count($explode_logo) - 1]);
            $logo = $explode_logo[count($explode_logo) - 1];
        }

        DB::beginTransaction();
        try {
            $brand = Brand::create([
                'name' => $request->name,
                'logo' => $request->logo ? $logo : null,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'brand' => $brand
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|max:255',
            'logo' => 'nullable|max:255',
        ]);

        if($request->logo) {
            if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->logo)[count(explode('/', $request->logo)) - 1])) {
                $explode_logo = explode('/', $request->logo);
                Storage::disk('public')->move('/uploads/temp/' . $explode_logo[count($explode_logo) - 1], '/uploads/brands/' . $explode_logo[count($explode_logo) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $explode_logo[count($explode_logo) - 1], '/uploads/brands/200/' . $explode_logo[count($explode_logo) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $explode_logo[count($explode_logo) - 1], '/uploads/brands/600/' . $explode_logo[count($explode_logo) - 1]);
                $logo = $explode_logo[count($explode_logo) - 1];
            } else if(Storage::disk('public')->exists('/uploads/brands/icons/' . explode('/', $request->logo)[count(explode('/', $request->logo)) - 1])) {
                $logo = $brand->logo;
            }
        }

        DB::beginTransaction();
        try {
            $brand->update([
                'name' => $request->name,
                'logo' => isset($logo) ? $logo : $request->logo,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'brand' => $brand
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function destroy(Brand $brand)
    {
        DB::beginTransaction();
        try {
            $brand->delete();

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();

            return reponse([
                'message' => $e->getMessage()
            ], 500);
        }
        
        return response([
            'message' => __('messages.successfully_deleted')
        ]);
    }
}
