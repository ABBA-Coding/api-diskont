<?php

namespace App\Http\Controllers\Attributes;

use App\Models\Attributes\{
    AttributeOption,
    Attribute,
};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attributes = Attribute::latest()
            ->select('id', 'name', 'keywords')
            ->with('options', 'categories')
            ->paginate($this->PAGINATE);

        return response([
            'attributes' => $attributes
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
            'name.'.$this->main_lang => 'required',
            'options' => 'nullable|array',
            // 'options.*.position' => 'required|integer',
            'options.*.name.'.$this->main_lang => 'required',
        ]);

        DB::beginTransaction();

        try {
            $attribute = Attribute::create([
                'name' => $request->name,
                'for_search' => $request->name['ru'],
                'keywords' => $request->keywords,
            ]);

            foreach($request->options as $option) {
                $attribute->options()->create([
                    'name' => $option['name'],
                    'for_search' => $option['name'][$this->main_lang],
                    'position' => isset($option['position']) ? $option['position'] : 1000, // buni required qilib qo'yish kerak
                ]);        
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response($attribute);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function show(Attribute $attribute)
    {
        $attribute = Attribute::where('id', $attribute->id)
            ->select('id', 'name', 'keywords')
            ->with('options')
            ->first();

        return response([
            'attribute' => $attribute
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name' => 'required|array',
            'name.'.$this->main_lang => 'required',
            'options' => 'nullable|array',
            'options.*.position' => 'required|integer',
            'options.*.name.'.$this->main_lang => 'required',
        ]);

        DB::beginTransaction();

        try {
            $attribute->update([
                // 'group_id' => $request->group_id,
                'name' => $request->name,
                'for_search' => $request->name['ru'],
                'keywords' => $request->keywords,
            ]);

            /*
             *  6cirilmagan optionlarni topish
             */
            $request_options = $request->options;
            $qolganlari_ids = array_filter($request_options, function($i) {
                if($i['id'] != 0) return true;
            });
            $qolganlari_ids = array_values(array_map(function($i) {
                return $i['id'];
            }, $qolganlari_ids));
            /*
             *  6cirilmagan optionlarni update qiliw
             */
            foreach(AttributeOption::whereIn('id', $qolganlari_ids)->get() as $item) {
                $inner_data = $request->options[array_search($item->id, $qolganlari_ids)];
                $inner_data['for_search'] = $inner_data['name'][$this->main_lang];

                $item->update($inner_data);
            }
            /*
             *  liwniylarini 6ciriw
             */
            $attribute->options()->whereNotIn('id', $qolganlari_ids)->delete();
            /*
             *  yangilarini q6wiw
             */
            $yangilari = array_filter($request_options, function($i) {
                if($i['id'] == 0) return true;
            });
            foreach($yangilari as $item) {
                AttributeOption::create([
                    'attribute_id' => $attribute->id,
                    'position' => $item['position'],
                    'name' => $item['name'],
                    'for_search' => $item['name'][$this->main_lang],
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response($attribute);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attribute $attribute)
    {
        DB::beginTransaction();
        try {
            if($attribute->id == 1) return response([
                'message' => 'Forbidden'
            ], 403);
            $attribute->options()->delete();
            $attribute->delete();

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
        $attributes = Attribute::latest()
            ->select('id', 'name')
            ->with('options')
            ->get();

        return response([
            'attributes' => $attributes
        ]);
    }
}
