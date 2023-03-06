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

    public function to_slug(\Illuminate\Http\Request $request, $model, $field, $lang = 'ru', $update_id = 0)
    {
        if($lang == null) {

            $slug = \Illuminate\Support\Str::slug($request->$field);
            $counter = 1;

            if($update_id == 0) {
                if($model::where('slug', \Illuminate\Support\Str::slug($request->$field))->exists()) {
                    $slug = \Illuminate\Support\Str::slug($request->$field) . '-' . $counter;
                    while ($model::where('slug', \Illuminate\Support\Str::slug($request->$field) . '-' . $counter)->exists()) {
                        $counter ++;
                        $slug = \Illuminate\Support\Str::slug($request->$field) . '-' . $counter;
                    }
                }
            } else {
                if($request->slug == $model::find($update_id)->slug) return $request->slug;

                if($model::where('slug', $request->slug)->exists()) {
                    $slug = $request->slug;
                    if($model::where('slug', $request->slug)->first()->id != $update_id) {
                        $slug = $request->slug . '-' . $counter;
                        while ($model::where('slug', $request->slug . '-' . $counter)->exists()) {
                            if($model::where('slug', $request->slug . '-' . $counter)->first()->id == $update_id) break;
                            $counter ++;
                            $slug = $request->slug . '-' . $counter;
                        }
                    }
                } else {
                    $slug = $request->slug;
                }
            }

        } else {
            $slug = \Illuminate\Support\Str::slug($request->$field[$lang]);
            $counter = 1;

            if($update_id == 0) {
                if($model::where('slug', \Illuminate\Support\Str::slug($request->$field[$lang]))->exists()) {
                    $slug = \Illuminate\Support\Str::slug($request->$field[$lang]) . '-' . $counter;
                    while ($model::where('slug', \Illuminate\Support\Str::slug($request->$field[$lang]) . '-' . $counter)->exists()) {
                        $counter ++;
                        $slug = \Illuminate\Support\Str::slug($request->$field[$lang]) . '-' . $counter;
                    }
                }
            } else {
                if($request->slug == $model::find($update_id)->slug) return $request->slug;
                
                if($model::where('slug', $request->slug)->exists()) {
                    $slug = $request->slug;
                    if($model::where('slug', $request->slug)->first()->id != $update_id) {
                        $slug = $request->slug . '-' . $counter;
                        while ($model::where('slug', $request->slug . '-' . $counter)->exists()) {
                            if($model::where('slug', $request->slug . '-' . $counter)->first()->id == $update_id) break;
                            $counter ++;
                            $slug = $request->slug . '-' . $counter;
                        }
                    }
                } else {
                    $slug = $request->slug;
                }
            }
        }

        return $slug;
    }
}
