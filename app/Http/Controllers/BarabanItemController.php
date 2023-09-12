<?php

namespace App\Http\Controllers;

use App\Models\BarabanItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BarabanItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $items = BarabanItem::select('count', 'position')
        //     ->get();

        // return response([
        //     'items' => $items
        // ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        // $request->validate([
        //     'items' => 'required|array',
        //     'items.*' => 'required',
        //     'items.*.position' => 'required',
        //     'items.*count' => 'required',
        // ]);
        // $data = $request->all();

        // if(count($data['items']) != 12) return response([
        //     'message' => 'Kolichestvo poley barabana 12'
        // ], 422);

        // DB::beginTransaction();
        // try {
        //     $items = BarabanItem::all();
        //     foreach($items as $item) {
        //         $item->delete();
        //     }
        //     foreach ($data['items'] as $key => $value) {
        //         BarabanItem::create($value);
        //     }

        //     DB::commit();
        // } catch(\Exception $e) {
        //     DB::rollBack();

        //     return response([
        //         'message' => $e->getMessage()
        //     ], 500);
        // }

        // return response([
        //     'message' => 'Successfully updated'
        // ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BarabanItem  $barabanItem
     * @return \Illuminate\Http\Response
     */
    public function show(BarabanItem $barabanItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BarabanItem  $barabanItem
     * @return \Illuminate\Http\Response
     */
    public function edit(BarabanItem $barabanItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BarabanItem  $barabanItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BarabanItem $barabanItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BarabanItem  $barabanItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(BarabanItem $barabanItem)
    {
        //
    }
}
