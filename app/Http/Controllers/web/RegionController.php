<?php

namespace App\Http\Controllers\web;

use App\Models\Settings\Region;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::whereHas('group', function ($q) {
	        	$q->where('is_active', 1);
	        })
			->with('districts', 'group:id,delivery_price')
            ->get();

        $this->without_lang($regions);
        foreach ($regions as $region) {
            $this->without_lang($region->districts);
        }

        return response([
            'regions' => $regions
        ]);
    }
}
