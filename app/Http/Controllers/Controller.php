<?php

namespace App\Http\Controllers;

use App\Models\{
    Products\Product,
    SmsHistory
};
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

    public function to_slug(\Illuminate\Http\Request $request, $model, $field, $lang = 'ru', $update_id = 0): string
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

    public function product_slug_create($info, $additional, $update_id = 0): string
    {
        $request_field = $info->name[$this->main_lang];
        if($additional) $request_field .= '-' . $additional;

        $request_field = strlen($request_field) > 250 ? substr($request_field, 0, 250) : $request_field;
        $slug = \Illuminate\Support\Str::slug($request_field);

        if($update_id == 0) {
            if(Product::where('slug', \Illuminate\Support\Str::slug($request_field))->exists()) {
                $slug = \Illuminate\Support\Str::slug($request_field) . '-' . Product::latest()->first()->id + 1;
            }
        } else {
            if($slug == Product::find($update_id)->slug || $slug.'-'.$update_id == Product::find($update_id)->slug) return Product::find($update_id)->slug;
            
            $slug = Product::where('slug', $slug)->exists() ? $slug.'-'.$update_id : $slug;
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
