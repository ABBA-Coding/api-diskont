<?php

namespace App\Http\Controllers\Dicoin;

use App\Models\Dicoin\Dicoin;
use App\Models\BarabanItem;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DicoinController extends Controller
{
    public function index()
    {
        $dicoin = Dicoin::latest()
            ->first();

        $items = BarabanItem::select('count', 'position')
            ->get();

         return response([
            'dicoin' => $dicoin,
            'items' => $items
        ]);   
    }

    public function store(Request $request)
    {
        $request->validate([
            'sum_to_dicoin' => 'required|integer',
            'dicoin_to_sum' => 'required|integer',
            'dicoin_to_reg' => 'required|integer',
        ]);
        $data = $request->all();

        $dicoin = Dicoin::create($data);


        // baraban items
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'required',
            'items.*.position' => 'required',
            'items.*count' => 'required',
        ]);

        $data = $request->all();

        if(count($data['items']) != 12) return response([
            'message' => 'Kolichestvo poley barabana 12'
        ], 422);

        DB::beginTransaction();
        try {
            $items = BarabanItem::all();
            foreach($items as $item) {
                $item->delete();
            }
            foreach ($data['items'] as $key => $value) {
                BarabanItem::create($value);
            }

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'dicoin' => $dicoin,
            'message' => 'Items successfully updated'
        ]);
    }

    public function show(Dicoin $dicoin)
    {
        //
    }

    public function update(Request $request, Dicoin $dicoin)
    {
        //
    }

    public function destroy(Dicoin $dicoin)
    {
        //
    }
}
