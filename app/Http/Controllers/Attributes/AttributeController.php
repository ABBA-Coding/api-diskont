<?php

namespace App\Http\Controllers\Attributes;

use App\Models\Attributes\Attribute;
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
            'name.ru' => 'required',
            'options' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $attribute = Attribute::create([
                'name' => $request->name,
                'for_search' => $request->name['ru'],
                'keywords' => $request->keywords
            ]);

            foreach($request->options as $option) {
                $name = [
                    'ru' => $option
                ];
                $attribute->options()->create([
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
            // 'group_id' => 'required|integer',
            'name' => 'required|array',
            'name.ru' => 'required',
            'options' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $attribute->update([
                // 'group_id' => $request->group_id,
                'name' => $request->name,
                'for_search' => $request->name['ru'],
                'keywords' => $request->keywords,
            ]);

            $not_deleted_options = [];
            $options = $attribute->options;
            foreach($request->options as $option) {
                $name = [
                    'ru' => $option['name']
                ];
                if($option['id'] == 0) {
                    $new_option = $attribute->options()->create([
                        'name' => $name,
                        'for_search' => $option['name'],
                    ]);
                    $not_deleted_options[] = $new_option->id;
                } else {
                    $not_deleted_options[] = $option['id'];
                    $attribute->options()
                        ->find($option['id'])
                        ->update([
                            'name' => $name,
                            'for_search' => $option['name'],
                        ]);
                }
            }
            $attribute->options()
                ->whereNotIn('id', $not_deleted_options)
                ->delete();

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
