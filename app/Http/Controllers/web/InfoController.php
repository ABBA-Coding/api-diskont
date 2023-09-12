<?php

namespace App\Http\Controllers\web;

use App\Models\Info;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function get()
    {
    	$info = Info::latest()
            ->first();

        $this->without_lang([$info]);

        return response([
            'info' => $info
        ]);
    }
}
