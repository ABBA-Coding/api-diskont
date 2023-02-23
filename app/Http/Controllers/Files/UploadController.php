<?php

namespace App\Http\Controllers\Files;

use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    // soxranyaet zagrujennie fayli v vremennuyu paplu
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:4096',
        ]);

        $file = $request->file('file');

        $file_name = Str::random(16) . '.' . $file->extension();
        $saved_img = $file->move(public_path('/uploads/temp'), $file_name);
        
        if (!File::exists('uploads/temp/200')) {
            File::makeDirectory(public_path('uploads/temp/200'), 0700, true, true);
        }
        Image::make($saved_img)->resize(200, null, function ($constraint) {
            $constraint->aspectRatio();
        })->save(public_path() . '/uploads/temp/200/' . $file_name, 60);

        if (!File::exists('uploads/temp/600')) {
            File::makeDirectory(public_path('uploads/temp/600'), 0700, true, true);
        }
        Image::make($saved_img)->resize(600, null, function ($constraint) {
            $constraint->aspectRatio();
        })->save(public_path() . '/uploads/temp/600/' . $file_name, 80);

        return response([
            'path' => url('/uploads/temp') . '/' . $file_name,
        ]);
    }
}
