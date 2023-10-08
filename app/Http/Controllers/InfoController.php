<?php

namespace App\Http\Controllers;

use App\Models\Info;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $info = Info::latest()
            ->first();

        return response([
            'info' => $info
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Info $info)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Info $info)
    {
        $request->validate([
            'logo' => 'required|max:255',
            'phone_number' => 'required|max:255',
            'email' => 'nullable|max:255',
            'telegram' => 'nullable|max:255',
            'instagram' => 'nullable|max:255',
            'facebook' => 'nullable|max:255',
        ]);

        if($request->logo) {
            if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->logo)[count(explode('/', $request->logo)) - 1])) {
                $logoOld = explode('/', $request->logo);
                Storage::disk('public')->move('/uploads/temp/' . $logoOld[count($logoOld) - 1], '/uploads/info/' . $logoOld[count($logoOld) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $logoOld[count($logoOld) - 1], '/uploads/info/200/' . $logoOld[count($logoOld) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $logoOld[count($logoOld) - 1], '/uploads/info/600/' . $logoOld[count($logoOld) - 1]);
                $logo = $logoOld[count($logoOld) - 1];
            } else if(Storage::disk('public')->exists('/uploads/info/' . explode('/', $request->logo)[count(explode('/', $request->logo)) - 1])) {
                $logo = $info->logo;
            }
        }
        if($request->favicon) {
            if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->favicon)[count(explode('/', $request->favicon)) - 1])) {
                $explode_img = explode('/', $request->favicon);
                Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/info/' . $explode_img[count($explode_img) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/info/200/' . $explode_img[count($explode_img) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/info/600/' . $explode_img[count($explode_img) - 1]);
                $favicon = $explode_img[count($explode_img) - 1];
            } else if(Storage::disk('public')->exists('/uploads/info/' . explode('/', $request->favicon)[count(explode('/', $request->favicon)) - 1])) {
                $favicon = $info->favicon;
            }
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['logo'] = $logo ?? $request->logo;
            $data['favicon'] = $favicon ?? $request->favicon;

            $info->update($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'info' => $info
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Info $info)
    {
        //
    }
}
