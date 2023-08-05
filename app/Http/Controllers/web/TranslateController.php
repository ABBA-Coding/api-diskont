<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Translate\Translate;
use Illuminate\Http\Request;

class TranslateController extends Controller
{
    public function get()
    {
        $translates = Translate::with('group')
            ->get();

        $this->without_lang($translates);

        $result_translates = [];
        foreach($translates as $item) {
            $result_translates[$item->group->sub_text.'.'.$item->key] = $item->val;
        }

        return response([
            'translates' => $result_translates
        ]);
    }
}
