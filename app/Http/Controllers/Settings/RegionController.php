<?php

namespace App\Http\Controllers\Settings;

use App\Models\Settings\Region;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $regions = Region::latest()
            ->with('districts')
            ->paginate($this->PAGINATE);

        return response([
            'regions' => $regions
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
            'name.ru' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $region = Region::create([
                'name' => $request->name,
                'for_search' => $this->for_search($request, ['name'])
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'region' => $region
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\cr  $cr
     * @return \Illuminate\Http\Response
     */
    public function show(Region $region)
    {
        $region = Region::with('districts')
            ->first();

        return response([
            'region' => $region
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\cr  $cr
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Region $region)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ru' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $region->update([
                'name' => $request->name,
                'for_search' => $this->for_search($request, ['name'])
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'region' => $region
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\cr  $cr
     * @return \Illuminate\Http\Response
     */
    public function destroy(Region $region)
    {
        DB::beginTransaction();
        try {
            $region->districts()->delete();
            $region->delete();

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

    public function for_search(Request $request, $fields): string
    {
        $result = '';

        if(count($fields) == 0) return '';

        foreach($fields as $field) {
            $result .= isset($request->$field['ru']) ? ($request->$field['ru'] . ' ') : '';
        }

        return $result;
    }
}
