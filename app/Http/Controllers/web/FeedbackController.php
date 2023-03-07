<?php

namespace App\Http\Controllers\web;

use App\Models\Feedbacks\Feedback;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    protected $PAGINATE = 16;
    protected function set_paginate($paginate)
    {
        $this->PAGINATE = $paginate;
    }

    public function index(Request $request)
    {
        if(isset($request->limit) && $request->limit != '' && $request->limit < 41) $this->set_paginate($request->limit);
        $feedbacks = Feedback::latest()
            ->select('id', 'feedback', 'company', 'logo')
            ->paginate($this->PAGINATE);

        return response([
            'feedbacks' => $feedbacks
        ]);
    }
}
