<?php

namespace App\Http\Controllers\c;

use App\Http\Controllers\Controller;
use App\Models\LogC;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $data = [
            'req' => $request->url(),
            'res' => '',
            'body' => json_encode($request->all())
        ];

        // obnovlenie i save qilish kerak
        // type code

        LogC::create($data);

        return response([
            'message' => 'Success'
        ]);
    }
}
