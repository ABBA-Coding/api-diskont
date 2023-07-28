<?php

namespace App\Http\Controllers\Dicoin;

use App\Models\Dicoin\Dicoin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DicoinController extends Controller
{
    public function index()
    {
        $dicoin = Dicoin::latest()
            ->first();

         return response([
            'dicoin' => $dicoin
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

        return response([
            'dicoin' => $dicoin
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
