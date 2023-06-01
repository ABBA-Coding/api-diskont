<?php

namespace App\Http\Controllers\web;

use App\Models\Settings\Region;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::with('districts')
            ->get();

        return response([
            'regions' => $regions
        ]);
    }
}
