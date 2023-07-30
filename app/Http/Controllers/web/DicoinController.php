<?php

namespace App\Http\Controllers\web;

use App\Models\Dicoin\Dicoin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DicoinController extends Controller
{
    public function get()
    {
    	$dicoin = Dicoin::latest()
    		->select('sum_to_dicoin', 'dicoin_to_sum', 'dicoin_to_reg')
    		->first();

		return response([
			'dicoin' => $dicoin
		]);
    }
}
