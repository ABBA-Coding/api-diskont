<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
    	$request->validate([
    		'username' => 'required|max:255',
    		'password' => 'required|max:255',
    	]);

    	$admin = Admin::where('username', $request->username)
            ->first();
        if(!$admin) {
            return response([
                'message' => __('messages.user_not_found')
            ], 404);
        }

        if(!auth('admin')->attempt(['username' => $request->username, 'password' => $request->password])) {
            return response([
                'message' => __('messages.invalid_credentials')
            ], 422);
        }

        return response([
            'token' => $admin->createToken('auth-token', ['admin'])->plainTextToken
        ]);
    }
}
