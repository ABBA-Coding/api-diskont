<?php

namespace App\Http\Controllers\Characteristics;

use App\Models\Characteristics\{
    CharacteristicGroup,
    CharacteristicOption,
    Characteristic
};
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
        $characteristics = CharacteristicGroup::latest()
            // ->select('id', 'group_id', 'name')
            ->with('characteristics', 'characteristics.options')
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
            'group' => 'required|array',
            'group.'.$this->main_lang => 'required',
            'attributes' => 'required|array',
            'attributes.*' => 'required',
            'attributes.*.name' => 'array|required',
            'attributes.*.name.'.$this->main_lang => 'required',
            'attributes.*.options' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $group = CharacteristicGroup::create([
                'name' => $request->group,
                'for_search' => $this->for_search($request, ['name'])
            ]);

            foreach($request->input('attributes') as $attribute) {
                $characteristic = Characteristic::create([
                    'group_id' => $group->id,
                    'name' => $attribute['name'],
                    'for_search' => $attribute['name'][$this->main_lang],
                ]);

                foreach($attribute['options'] as $option) {
                    $option = [
                        'name' => $option['name'],
                        'for_search' => $option['name'][$this->main_lang]
                    ];
                    $characteristic->options()->create($option);        
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'characteristic' => $request->all()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Characteristic  $characteristic
     * @return \Illuminate\Http\Response
     */
    public function show($characteristic_group_id)
    {
        $characteristicGroup = CharacteristicGroup::where('id', $characteristic_group_id)
            // ->select('id', 'group_id', 'name')
            ->with('characteristics', 'characteristics.options')
            ->first();

        return response([
            'characteristic' => $characteristicGroup
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Characteristic  $characteristic
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $characteristic_group_id)
    {
        $request->validate([
            'group' => 'required|array',
            'group.'.$this->main_lang => 'required',
            'attributes' => 'required|array',
            'attributes.*' => 'required',
            'attributes.*.name' => 'array|required',
            'attributes.*.name.'.$this->main_lang => 'required',
            'attributes.*.options' => 'array',
        ]);

        DB::beginTransaction();

        try {
            $characteristic_group = CharacteristicGroup::find($characteristic_group_id);

            $characteristic_group->update([
                'name' => $request->group,
                'for_search' => $this->for_search($request, ['name'])
            ]);

            /*
             *  6cirilmagan attributlarni topish
             */
            $request_attributes = $request->input('attributes');
            $qolganlari_ids = array_filter($request_attributes, function($i) {
                if($i['id'] != 0) return true;
            });
            $qolganlari_ids = array_values(array_map(function($i) {
                return $i['id'];
            }, $qolganlari_ids));
            /*
             *  6cirilmagan attributlarni update qiliw
             */
            foreach(Characteristic::whereIn('id', $qolganlari_ids)->get() as $item) {
                $inner_data = $request->input('attributes')[array_search($item->id, $qolganlari_ids)];
                $inner_data['for_search'] = $inner_data['name'][$this->main_lang];

                $item->update($inner_data);

                /*
                 *  6cirilmagan optionlarni topish
                 */
                $request_options = $request->input('attributes')[array_search($item->id, $qolganlari_ids)]['options'];
                $qolganlari_ids_inner = array_filter($request_options, function($i) {
                    if($i['id'] != 0) return true;
                });
                $qolganlari_ids_inner = array_values(array_map(function($i) {
                    return $i['id'];
                }, $qolganlari_ids_inner));
                /*
                 *  6cirilmagan optionlarni update qiliw
                 */
                foreach(CharacteristicOption::whereIn('id', $qolganlari_ids_inner)->get() as $option) {
                    $inner_data = $request->input('attributes')[array_search($item->id, $qolganlari_ids)]['options'][array_search($option->id, $qolganlari_ids_inner)];

                    $option->update($inner_data);
                }
                /*
                 *  liwniylarini 6ciriw
                 */
                $item->options()->whereNotIn('id', $qolganlari_ids_inner)->delete();
                /*
                 *  yangilarini q6wiw
                 */
                $yangilari_inner = array_filter($request_options, function($i) {
                    if($i['id'] == 0) return true;
                });
                foreach($yangilari_inner as $yangisi) {
                    CharacteristicOption::create([
                        'characteristic_id' => $item->id,
                        'name' => $yangisi['name'],
                        'for_search' => $yangisi['name'][$this->main_lang]
                    ]);
                }
            }
            /*
             *  liwniylarini 6ciriw
             */
            foreach($characteristic_group->characteristics as $characteristic) {
                $characteristic->options()->delete();
            }
            $characteristic_group->characteristics()->whereNotIn('id', $qolganlari_ids)->delete();
            /*
             *  yangilarini q6wiw
             */
            $yangilari = array_filter($request_attributes, function($i) {
                if($i['id'] == 0) return true;
            });
            foreach($yangilari as $item) {
                $new_characteristic = Characteristic::create([
                    'group_id' => $characteristic_group->id,
                    'name' => $item['name']
                ]);

                foreach($item['options'] as $option) {
                    CharacteristicOption::create([
                        'characteristic_id' => $new_characteristic->id,
                        'name' => $option['name'],
                        'for_search' => $option['name'][$this->main_lang]
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

        return response($characteristic_group);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Characteristic  $characteristic
     * @return \Illuminate\Http\Response
     */
    public function destroy($characteristic_group_id)
    {
        DB::beginTransaction();

        try {
            $characteristic_group = CharacteristicGroup::find($characteristic_group_id);

            foreach($characteristic_group->characteristics as $characteristic) {
                $characteristic->options()->delete();
            }
            $characteristic_group->characteristics()->delete();
            $characteristic_group->delete();

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
