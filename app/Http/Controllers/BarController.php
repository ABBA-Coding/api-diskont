<?php

namespace App\Http\Controllers;

use App\Models\Bar;
use Illuminate\Support\Facades\Storage;
use DB;
use Illuminate\Http\Request;

class BarController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bars = Bar::latest()
            ->paginate($this->PAGINATE);

        return response([
            'bars' => $bars
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
            'name' => 'required|array',
            'name.ru' => 'required|max:500',
            'icon' => 'nullable|max:255',
            'text_color' => 'required|max:255',
            'color1' => 'required|max:255',
            'color2' => 'required|max:255',
        ]);
        $data = $request->all();

        if($request->icon && Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->icon)[count(explode('/', $request->icon)) - 1])) {
            $explode_img = explode('/', $request->icon);
            Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/bars/' . $explode_img[count($explode_img) - 1]);
            Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/bars/200/' . $explode_img[count($explode_img) - 1]);
            Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/bars/600/' . $explode_img[count($explode_img) - 1]);
            $data['icon'] = $explode_img[count($explode_img) - 1];
        }

        DB::beginTransaction();
        try {
            $data['for_search'] = $this->for_search($request, ['name']);
            $bar = Bar::create($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'bar' => $bar
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bar  $bar
     * @return \Illuminate\Http\Response
     */
    public function show(Bar $bar)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bar  $bar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bar $bar)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ru' => 'required|max:500',
            'icon' => 'nullable|max:255',
            'text_color' => 'required|max:255',
            'color1' => 'required|max:255',
            'color2' => 'required|max:255',
        ]);
        $data = $request->all();

        if($request->icon) {
            if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->icon)[count(explode('/', $request->icon)) - 1])) {
                $explode_img = explode('/', $request->icon);
                Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/bars/' . $explode_img[count($explode_img) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/bars/200/' . $explode_img[count($explode_img) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/bars/600/' . $explode_img[count($explode_img) - 1]);
                $data['icon'] = $explode_img[count($explode_img) - 1];
            } else if(Storage::disk('public')->exists('/uploads/bars/' . explode('/', $request->icon)[count(explode('/', $request->icon)) - 1])) {
                $data['icon'] = $bar->icon;
            }
        }

        DB::beginTransaction();
        try {
            $data['for_search'] = $this->for_search($request, ['name']);
            $bar->update($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'bar' => $bar
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bar  $bar
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bar $bar)
    {
        DB::beginTransaction();
        try {
            $bar->delete();

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

    public function for_search(Request $request, $fields)
    {
        $result = '';

        if(count($fields) == 0) return '';

        foreach($fields as $field) {
            $result .= isset($request->$field['ru']) ? ($request->$field['ru'] . ' ') : '';
        }

        return $result;
    }
}
