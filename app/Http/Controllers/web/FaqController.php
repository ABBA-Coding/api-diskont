<?php

namespace App\Http\Controllers\web;

use App\Models\Faqs\Faq;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function get()
    {
    	$faq = Faq::latest()
    		->select('id', 'question', 'answer', 'created_at')
    		->get();

		$this->without_lang($faq);

		return response([
			'faq' => $faq
		]);
    }
}
