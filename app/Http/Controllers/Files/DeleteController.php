<?php

namespace App\Http\Controllers\Files;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class DeleteController extends Controller
{
    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|max:255'
        ]);

        $path = substr($request->path, strlen(url('/')));

        $path_200 = explode('/', $path);
        $path_200[] = $path_200[count($path_200) - 1];
        $path_200[count($path_200) - 2] = '200';
        $path_200 = implode('/', $path_200);

        $path_600 = explode('/', $path);
        $path_600[] = $path_600[count($path_600) - 1];
        $path_600[count($path_600) - 2] = '600';
        $path_600 = implode('/', $path_600);

        if(Storage::disk('public')->exists($path) && Storage::disk('public')->exists($path_200) && Storage::disk('public')->exists($path_600)) {
            Storage::disk('public')->delete($path);
            Storage::disk('public')->delete($path_200);
            Storage::disk('public')->delete($path_600);
        } else {
            return response([
                'message' => __('messages.file_not_found')
            ], 404);
        }

        return response([
            'message' => __('messages.successfully_deleted')
        ]);
    }
}
