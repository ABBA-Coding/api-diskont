<?php

namespace App\Http\Controllers\web;

use App\Models\Settings\Region;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::with('districts', 'districts.villages')
            ->get();

        $this->without_lang($regions);
        foreach ($regions as $region) {
            $this->without_lang($region->districts);

            foreach($region->districts as $district) {
                $this->without_lang($district->villages);
            }
        }

        return response([
            'regions' => $regions
        ]);
    }
}
