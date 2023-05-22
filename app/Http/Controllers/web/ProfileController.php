<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Hash;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required|confirmed|min:6|max:255',
            'phone_number' => 'required|min:100000000001|max:999999999998|numeric',
        ]);

        if(!auth()->user()) return response([
            'message' => 'Unauthorized'
        ], 401);

        auth()->user()->update([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'login' => $request->phone_number,
            'password_updated' => 1,
            'address' => $request->address,
            'region_id' => $request->region_id,
            'district_id' => $request->district_id,
            'postcode' => $request->postcode,
            'email' => $request->email,
        ]);

        return response([
            'user' => auth()->user(),
            'message' => 'Soxraneno'
        ]);
    }

    public function orders()
    {

    }
}
