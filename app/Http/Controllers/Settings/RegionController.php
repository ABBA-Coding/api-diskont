<?php

namespace App\Http\Controllers\Settings;

use App\Models\Settings\{
    District,
    Region,
};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->with('districts', 'group')
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
        $data = $request->all();
        $data['for_search'] = $this->for_search($request, ['name']);

        DB::beginTransaction();
        try {
            $region = Region::create($data);

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
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function show(Region $region)
    {
        $region = Region::where('id', $region->id)
            ->with('districts', 'group')
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
        $data = $request->all();
        $data['for_search'] = $this->for_search($request, ['name']);

        DB::beginTransaction();
        try {
            $region->update($data);

            $qolganlari_ids = $request->districts;
            $qolganlari_ids = array_map(function($i) {
                if($i['id'] != 0) return $i['id'];
            }, $qolganlari_ids);
            $qolganlari_ids = array_values(array_filter($qolganlari_ids, function($i) {
                return !is_null($i);
            }));
            $region->districts()->whereNotIn('id', $qolganlari_ids)->delete();
            foreach($request->districts as $district) {
                if($district['id'] == 0) {
                    $region->districts()->create([
                        'name' => $district['name']
                    ]);
                } else {
                    $d = District::find($district['id']);
                    if(!$d) return response([
                        'message' => 'District not found'
                    ], 404);

                    $d->update([
                        'name' => $district['name']
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        $region = Region::where('id', $region->id)
            ->with('districts')
            ->first();

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
