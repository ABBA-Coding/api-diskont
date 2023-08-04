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
            'sms_send',
            'sms_for_forget'
        ];
        $sms_active_time = 300; //in seconds
        $sms_texts = [
            'sms_send' => 'Your code for auth in e-shop: ',
            'sms_for_forget' => 'Your code for restore: ',
        ];

        if(isset($request->forget) && $request->forget == 1) {
            Cache::add($request->phone_number, $cache_variables[1], $sms_active_time);
           $generated_code = $this->generate_code();
            // $generated_code = 111111;
            $sent = $this->send_sms($request->phone_number, $sms_texts['sms_for_forget'] . $generated_code);
            Cache::add($request->phone_number. 'code', $generated_code, $sms_active_time);
            if (!$sent) {
                return response([
                    'authorized' => 1,
                    'message' => __('sms.not_sent')
                ], 500);

            }

            return response([
                'authorized' => 1,
                'message' => __('sms.sent')
            ]);
        } else {
            $user_exist = User::where('login', $request->phone_number)
                ->first();
            if(!$user_exist || !$user_exist->password_updated) {
                Cache::add($request->phone_number, $cache_variables[0], $sms_active_time);
               $generated_code = $this->generate_code();
                // $generated_code = 111111;
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
                $user = User::updateOrCreate([
                    'login' => $request->phone_number,
                ],[
                    'password' => Hash::make($this->generateRandomString())
                ]);

                $this->cache_forget([
                    $request->phone_number,
                    $request->phone_number. 'code'
                ]);

                return response([
                    'token' => $user->createToken('auth-token', ['client'])->plainTextToken
                ]);
            }
        } else if(Cache::has($request->phone_number) && Cache::get($request->phone_number) == 'sms_for_forget') {
            if(Cache::has($request->phone_number. 'code') && Cache::get($request->phone_number. 'code') == $request->sms_code) {
                $user = User::updateOrCreate([
                    'login' => $request->phone_number,
                ],[
                    'password' => Hash::make($this->generateRandomString()),
                    'password_updated' => 0,
                ]);

                $this->cache_forget([
                    $request->phone_number,
                    $request->phone_number. 'code'
                ]);

                return response([
                    'token' => $user->createToken('auth-token', ['client'])->plainTextToken
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

        $this->cache_forget([
            $request->phone_number,
            $request->phone_number. 'code'
        ]);
        return response([
            'token' => $user->createToken('auth-token', ['client'])->plainTextToken
        ]);
    }

    public function logout()
    {
        if(!auth('sanctum')->user()) return response([
            'message' => 'Unauthorized'
        ], 401);

        auth('sanctum')->user()->tokens()->delete();

        return response([
            'message' => 'Successfully logout'
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

    private function cache_forget($caches)
    {
        foreach($caches as $item) {
            Cache::forget($item);
        }
    }
}
