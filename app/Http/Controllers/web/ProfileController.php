<?php

namespace App\Http\Controllers\web;

use App\Models\Products\Product;
use App\Http\Controllers\Controller;
use Hash;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'current_password' => 'required|min:6|max:255',
            'password' => 'required|confirmed|min:6|max:255',
            'phone_number' => 'required|min:998000000001|max:999999999998|numeric',
            'region_id' => 'nullable|integer',
            'district_id' => 'nullable|integer',
            'address' => 'nullable',
            'email' => 'nullable|max:255',
            'postcode' => 'nullable|max:255',
        ]);

        if(!auth()->user()) return response([
            'message' => 'Unauthorized'
        ], 401);

        if(Hash::make($request->current_password) != auth()->user()->password) return response([
            'message' => 'Nepravilniy parol'
        ], 400);

        auth()->user()->update([
            'name' => $request->name,
            'password' => $request->password ? Hash::make($request->password) : auth()->user()->password,
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
            'message' => 'Saved'
        ]);
    }

    public function edit_name(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        if(!auth()->user()) return response([
            'message' => 'Unauthorized'
        ], 401);

        auth()->user()->update([
            'name' => $request->name
        ]);

        return response([
            'user' => auth()->user(),
            'message' => 'Saved'
        ]);
    }

    public function me()
    {
        $user = auth()->user()->with('orders')
            ->first();

        foreach($user->orders as $order) {
            /*
             * privyazka produkta k zakazu
             */
            $products = $order->products;

            foreach($products as $key => $product) {
                $new_arr = $product;

                $new_arr['product'] = Product::with('info', 'images')->find($product['product_id']);

                $products[$key] = $new_arr;
            }

            $order->products = $products;
        }

        return response([
            'user' => $user
        ]);
    }
}
