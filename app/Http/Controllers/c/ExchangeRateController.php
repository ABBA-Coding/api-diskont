<?php

namespace App\Http\Controllers\c;

use App\Models\ExchangeRate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function update(Request $request)
    {
    	$request->validate([
    		'uzs' => 'required|integer'
    	]);
    	$data['exchange_rate'] = $request->uzs;

    	ExchangeRate::create($data);

    	return response([
    		'error' => null
    	]);
    }
}
