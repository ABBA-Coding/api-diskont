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
            $request_field = strlen($request->$field) > 250 ? substr($request->$field, 0, 250) : $request->$field;
            $slug = \Illuminate\Support\Str::slug($request_field);
        } else {
            $request_field = strlen($request->$field[$lang]) > 250 ? substr($request->$field[$lang], 0, 250) : $request->$field[$lang];
            $slug = \Illuminate\Support\Str::slug($request_field);
        }

        if($update_id == 0) {
            if($model::where('slug', \Illuminate\Support\Str::slug($request_field))->exists()) {
                $slug = \Illuminate\Support\Str::slug($request_field) . '-' . $model::latest()->first()->id + 1;
            }
        } else {
            if($slug == $model::find($update_id)->slug || $slug.'-'.$update_id == $model::find($update_id)->slug) return $model::find($update_id)->slug;
            
            $slug = $model::where('slug', $slug)->exists() ? $slug.'-'.$update_id : $slug;
        }

        return $slug;
    }

    public function product_slug_create($info, $additional, $update_id = 0)
    {
        $slug = \Illuminate\Support\Str::slug($info->name[$this->main_lang]);
        if($additional) $slug .= '-' . $additional;

        $counter = 1;


        if($update_id == 0) {
            if(\App\Models\Products\Product::where('slug', $slug)->exists()) {
                $slug = \Illuminate\Support\Str::slug($info->name[$this->main_lang]) . '-' . $counter;
                while (\App\Models\Products\Product::where('slug', \Illuminate\Support\Str::slug($info->name[$this->main_lang]) . '-' . $counter)->exists()) {
                    $counter ++;
                    $slug = \Illuminate\Support\Str::slug($info->name[$this->main_lang]) . '-' . $counter;
                }
            }
            // if(\App\Models\Products\Product::where('slug', \Illuminate\Support\Str::slug($info->name[$this->main_lang]) . '-' . $additional)->exists()) {
            //     $slug = \Illuminate\Support\Str::slug($info->name[$this->main_lang]) . '-' . $additional . '-' . $counter;
            //     while (\App\Models\Products\Product::where('slug', \Illuminate\Support\Str::slug($info->name[$this->main_lang]) . '-' . $additional . '-' . $counter)->exists()) {
            //         $counter ++;
            //         $slug = \Illuminate\Support\Str::slug($info->name[$this->main_lang]) . '-' . $additional . '-' . $counter;
            //     }
            // }
        } else {
            $req_slug = \Illuminate\Support\Str::slug($info->name[$this->main_lang]) . '-' . $additional;
            if($req_slug == \App\Models\Products\Product::find($update_id)->slug) return $req_slug;
            
            if(\App\Models\Products\Product::where('slug', $req_slug)->exists()) {
                $slug = $req_slug;
                if(\App\Models\Products\Product::where('slug', $req_slug)->first()->id != $update_id) {
                    $slug = $req_slug . '-' . $counter;
                    while (\App\Models\Products\Product::where('slug', $req_slug . '-' . $counter)->exists()) {
                        if(\App\Models\Products\Product::where('slug', $req_slug . '-' . $counter)->first()->id == $update_id) break;
                        $counter ++;
                        $slug = $req_slug . '-' . $counter;
                    }
                }
            } else {
                $slug = $req_slug;
            }
        }

        return $slug;
    }

    public function delete_files($paths)
    {
        foreach($paths as $path) {
            if(file_exists($path)) unlink($path);
        }
    }

    public function for_search(\Illuminate\Http\Request $request, $fields)
    {
        $result = '';

        if(count($fields) == 0) return '';

        foreach($fields as $field) {
            $result .= isset($request->$field[$this->main_lang]) ? ($request->$field[$this->main_lang] . ' ') : '';
        }

        return $result;
    }
}
