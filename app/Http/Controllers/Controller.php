<?php

namespace App\Http\Controllers;

use App\Models\SmsHistory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $main_lang = 'ru';

    public function send_sms($phone_number, $text)
    {
        SmsHistory::create([
            'phone_number' => $phone_number,
            'text' => $text,
            'sent' => 0
        ]);
        return true;
    }

    public function to_slug(\Illuminate\Http\Request $request, $model, $field, $lang = 'ru')
    {
        $slug = \Illuminate\Support\Str::slug($request->$field[$lang]);
        $counter = 1;

        if($model::where('slug', $slug)->exists()) {
            while ($model::where('slug', $slug . '-' . $counter)->exists()) {
                $counter ++;
            }
        }

        return $slug;
    }
}
