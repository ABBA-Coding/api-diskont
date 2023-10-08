<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait MainTrait {
    public function getRegion($lat, $lon, $lang = 'ru')
    {
        $res = Http::get('https://nominatim.openstreetmap.org/reverse?format=json&lat='.$lat.'&lon='.$lon.'&zoom=6&accept-language='.$lang);
        $res_arr = $res->json();

        return $res_arr['address']['city'] ?? $res_arr['address']['state'];
    }
}
