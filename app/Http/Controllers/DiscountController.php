<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'title' => 'required|array',
            'title.ru' => 'required',
            'percent' => 'sometimes|integer',
            'amount' => 'sometimes|integer',
            'type' => 'required|in:product,brand',
            'ids' => 'array|required',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'status' => 'required|boolean'
        ]);
        if(!($request->percent && $request->amount)) return response([
            'message' => 'Odnovremenno amount i percent ne mojet bit pustim'
        ], 422);

        $data = $request->all();
        $data['for_search'] = $data['title']['ru'].(isset($data['desc']['ru']) ? ' '.$data['desc']['ru'] : '');

        DB::beginTransaction();
        try {
            $discount = Discount::create($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'discount' => $discount
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function show(Discount $discount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discount $discount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discount $discount)
    {
        //
    }
}
