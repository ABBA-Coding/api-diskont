<?php

namespace App\Http\Controllers;

use App\Models\Promotions\Promotion;
use Illuminate\Support\Facades\Storage;
use DB;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $promotions = Promotion::latest()
            ->paginate($this->PAGINATE);

        return response([
            'promotions' => $promotions
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
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
        $data = $request->all();

        foreach (['banner', 'short_name_icon', 'sticker'] as $value) {
            if($request->$value && Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->$value)[count(explode('/', $request->$value)) - 1])) {
                $explode_img = explode('/', $request->$value);
                Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/promotions/'.$value.'s/' . $explode_img[count($explode_img) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/promotions/'.$value.'s/200/' . $explode_img[count($explode_img) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/promotions/'.$value.'s/600/' . $explode_img[count($explode_img) - 1]);
                $data[$value] = $explode_img[count($explode_img) - 1];
            }
        }

        $data['for_search'] = $this->for_search($request, ['name', 'desc', 'sticker_text', 'short_name']);
        $data['slug'] = $this->to_slug($request, Promotion::class, 'name', $this->main_lang);

        DB::beginTransaction();
        try {
            $promotion = Promotion::create($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'promotion' => $promotion
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Promotions\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function show(Promotion $promotion)
    {
        $promotion = Promotion::where('id', $promotion->id)
            ->with('products')
            ->first();

        return response([
            'promotion' => $promotion,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Promotions\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ru' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
        $data = $request->all();

        foreach (['banner', 'short_name_icon', 'sticker'] as $value) {
            if($request->$value) {
                if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->$value)[count(explode('/', $request->$value)) - 1])) {
                    $explode_img = explode('/', $request->$value);
                    Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/promotions/'.$value.'s/' . $explode_img[count($explode_img) - 1]);
                    Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/promotions/'.$value.'s/200/' . $explode_img[count($explode_img) - 1]);
                    Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/promotions/'.$value.'s/600/' . $explode_img[count($explode_img) - 1]);
                    $data[$value] = $explode_img[count($explode_img) - 1];
                } else if(Storage::disk('public')->exists('/uploads/promotions/'.$value.'s/' . explode('/', $request->$value)[count(explode('/', $request->$value)) - 1])) {
                    $data[$value] = $promotion->$value;
                }
            }
        }

        $data['for_search'] = $this->for_search($request, ['name', 'desc', 'sticker_text', 'short_name']);
        $data['slug'] = $this->to_slug($request, Promotion::class, 'name', $this->main_lang);

        DB::beginTransaction();
        try {
            $promotion->update($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'promotion' => $promotion
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Promotions\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Promotion $promotion)
    {
        DB::beginTransaction();
        try {
            $promotion->delete();

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
