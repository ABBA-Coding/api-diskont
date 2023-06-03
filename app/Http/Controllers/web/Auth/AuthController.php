<?php

namespace App\Http\Controllers\web\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function checkUser(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|max:12|min:12',
        ]);

        $cache_variables = [
            'sms_send'
        ];
        $sms_active_time = 300; //in seconds
        $sms_texts = [
            'sms_send' => 'Your code for auth in HOME24.uz: '
        ];

        $user_exist = User::where('login', $request->phone_number)
            ->exists();
        if(!$user_exist) {
            Cache::add($request->phone_number, $cache_variables[0], $sms_active_time);
//            $generated_code = $this->generate_code();
            $generated_code = 111111;
            $sent = $this->send_sms($request->phone_number, $sms_texts['sms_send'] . $generated_code);
            Cache::add($request->phone_number. 'code', $generated_code, $sms_active_time);
            if (!$sent) {
                return response([
                    'authorized' => 0,
                    'message' => __('sms.not_sent')
                ], 500);

            }

            return response([
                'authorized' => 0,
                'message' => __('sms.sent')
            ]);
        }

        return response([
            'authorized' => 1
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|min:12|max:12',
            'sms_code' => 'required|min:100000|max:999999|integer',
        ]);

        if(Cache::has($request->phone_number) && Cache::get($request->phone_number) == 'sms_send') {
            if(Cache::has($request->phone_number. 'code') && Cache::get($request->phone_number. 'code') == $request->sms_code) {
                $user = User::create([
                    'login' => $request->phone_number,
                    'password' => Hash::make($this->generateRandomString())
                ]);

                return response([
                    'token' => $user->createToken('auth-token')->plainTextToken
                ]);
            }
        } else {
            return response([
                'message' => __('messages.timed_out')
            ], 422);
        }

        return response([
            'message' => __('messages.incorrect_code')
        ], 422);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('login', $request->phone_number)
            ->first();
        if(!$user) {
            return response([
                'message' => __('messages.user_not_found')
            ], 404);
        }

        if(!auth()->attempt(['login' => $request->phone_number, 'password' => $request->password])) {
            return response([
                'message' => __('messages.invalid_credentials')
            ], 422);
        }

        return response([
            'token' => $user->createToken('auth-token')->plainTextToken
        ]);
    }

    public function generate_code()
    {
        return rand(100001, 999998);
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
