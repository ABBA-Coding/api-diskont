<?php

namespace App\Http\Controllers;

use App\Models\RegionGroup;
use App\Models\Settings\Region;
use DB;
use Illuminate\Http\Request;

class RegionGroupController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = RegionGroup::latest()
            ->with('regions')
            ->paginate($this->PAGINATE);

        return response([
            'groups' => $groups
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
            'delivery_price' => 'required|integer',
        ]);
        $data = $request->all();
        $data['for_search'] = $this->for_search($request, ['name']);

        DB::beginTransaction();
        try {
            $group = RegionGroup::create($data);
            foreach($data['regions'] as $region) {
                Region::find($region)->update(['group_id' => $group->id]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'group' => $group
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RegionGroup  $regionGroup
     * @return \Illuminate\Http\Response
     */
    public function show(RegionGroup $regionGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RegionGroup  $regionGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RegionGroup $regionGroup)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ru' => 'required|max:500',
            'delivery_price' => 'required|integer',
        ]);
        $data = $request->all();
        $data['for_search'] = $this->for_search($request, ['name']);

        DB::beginTransaction();
        try {
            foreach($regionGroup->regions as $region) {
                $region->update(['group_id' => null]);
            }
            $regionGroup->update($data);
            foreach($data['regions'] as $region) {
                Region::find($region)->update(['group_id' => $regionGroup->id]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'group' => $regionGroup
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RegionGroup  $regionGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(RegionGroup $regionGroup)
    {
        DB::beginTransaction();
        try {
            foreach($regionGroup->regions as $region) {
                $region->update(['group_id' => null]);
            }
            $regionGroup->delete();

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();

            return response([
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
