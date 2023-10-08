<?php

namespace App\Http\Controllers\web;

use App\Models\Info;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\MainTrait;

class InfoController extends Controller
{
    use MainTrait;
    public function get(Request $request)
    {
        $info = Info::latest()
            ->first();

        // only active lang
        $this->without_lang([$info]);

        // add current location
        $currentLocation = '';
        if ($request->input('lat') !== null && $request->input('lon') !== null) $currentLocation = $this->getRegion($request->input('lat'), $request->input('lon'), $request->header('lang') ?? null);
        $info->currentLocation = $currentLocation;

        return response([
            'info' => $info
        ]);
    }
}
