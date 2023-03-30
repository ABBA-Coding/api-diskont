<?php

namespace App\Http\Controllers\Characteristics;

use App\Models\Characteristics\CharacteristicOption;
use App\Models\Characteristics\Characteristic;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CharacteristicController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $characteristics = Characteristic::latest()
            ->select('id', 'group_id', 'name')
            ->with('group', 'options')
            ->paginate($this->PAGINATE);

        return response([
            'characteristics' => $characteristics
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
            'group_id' => 'required|integer',
            'name' => 'required|array',
            'name.ru' => 'required',
            'options' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $characteristic = Characteristic::create([
                'group_id' => $request->group_id,
                'name' => $request->name,
                'for_search' => $request->name['ru'],
            ]);

            foreach($request->options as $option) {
                $name = [
                    'ru' => $option
                ];
                $characteristic->options()->create([
                    'name' => $name,
                    'for_search' => $option,
                ]);        
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response($characteristic);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Characteristic  $characteristic
     * @return \Illuminate\Http\Response
     */
    public function show(Characteristic $characteristic)
    {
        $characteristic = Characteristic::where('id', $characteristic->id)
            ->select('id', 'group_id', 'name')
            ->with('group', 'options')
            ->first();

        return response([
            'characteristic' => $characteristic
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Characteristic  $characteristic
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Characteristic $characteristic)
    {
        $request->validate([
            'group_id' => 'required|integer',
            'name' => 'required|array',
            'name.ru' => 'required',
            'options' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $characteristic->update([
                'group_id' => $request->group_id,
                'name' => $request->name,
                'for_search' => $request->name['ru'],
            ]);

            $not_deleted_options = [];
            $options = $characteristic->options;
            foreach($request->options as $option) {
                $name = [
                    'ru' => $option['name']
                ];
                if($option['id'] == 0) {
                    $new_option = $characteristic->options()->create([
                        'name' => $name,
                        'for_search' => $option['name'],
                    ]);
                    $not_deleted_options[] = $new_option->id;
                } else {
                    $not_deleted_options[] = $option['id'];
                    $characteristic->options()
                        ->find($option['id'])
                        ->update([
                            'name' => $name,
                            'for_search' => $option['name'],
                        ]);
                }
            }
            $characteristic->options()
                ->whereNotIn('id', $not_deleted_options)
                ->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response($characteristic);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Characteristic  $characteristic
     * @return \Illuminate\Http\Response
     */
    public function destroy(Characteristic $characteristic)
    {
        DB::beginTransaction();

        try {
            $characteristic->options()->delete();
            $characteristic->delete();

            DB::commit();
        } catch (\Exception $e) {
            Db::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'message' => __('messages.successfully_deleted')
        ]);
    }

    public function all()
    {
        $characteristics = Characteristic::latest()
            ->select('id', 'group_id', 'name')
            ->with('group', 'options')
            ->get();

        return response([
            'characteristics' => $characteristics
        ]);
    }
}
