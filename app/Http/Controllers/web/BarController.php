<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bar;

class BarController extends Controller
{
	protected $PAGINATE = 16;
    protected function set_paginate($paginate)
    {
        $this->PAGINATE = $paginate;
    }

    public function index(Request $request)
    {
    	if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);
        $bars = Bar::latest()
            ->orderBy('position')
        	->with('category', 'promotion')
            // ->select('id', 'title', 'desc', 'img', 'slug', 'created_at')
            ->paginate($this->PAGINATE);

        $this->without_lang($bars);

        return response([
            'bars' => $bars
        ]);
    }
}
