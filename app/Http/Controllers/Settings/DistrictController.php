<?php

namespace App\Http\Controllers\Settings;

use App\Models\Settings\District;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $districts = District::latest()
            ->with('region')
            ->paginate($this->PAGINATE);

        return response([
            'districts' => $districts
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
            'region_id' => 'required|integer'
        ]);

        DB::beginTransaction();
        try {
            $district = District::create([
                'name' => $request->name,
                'region_id' => $request->region_id,
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
            'district' => $district
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(District $district)
    {
        $district = District::with('region')
            ->first();

        return response([
            'district' => $district
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, District $district)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ru' => 'required',
            'region_id' => 'required|integer'
        ]);

        DB::beginTransaction();
        try {
            $district->update([
                'name' => $request->name,
                'region_id' => $request->region_id,
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
            'district' => $district
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(District $district)
    {
        DB::beginTransaction();
        try {
            $district->delete();

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
