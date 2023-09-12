<?php

namespace App\Http\Controllers\web;

use App\Models\Branch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function get()
    {
    	$branches = Branch::latest()
            ->with('region')
            ->get();

        $this->without_lang($branches);
        foreach ($branches as $key => $value) {
        	$this->without_lang([$value->region]);
        }

        return response([
            'branches' => $branches
        ]);
    }
}
