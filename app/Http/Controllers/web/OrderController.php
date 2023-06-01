<?php

namespace App\Http\Controllers\web;

use App\Models\{
    Products\Product,
    Orders\Order
};
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'delivery_method' => 'required|in:pickup,courier',
            'name' => 'required|max:255',
            'phone_number' => 'required|numeric|min:998000000001|max:998999999999',
            'region_id' => 'nullable|integer',
            'district_id' => 'nullable|integer',
            'address' => 'nullable',
            'postcode' => 'nullable|max:255',
            'email' => 'nullable|max:255',
            'comments' => 'nullable',
            'payment_method' => 'required|in:cash,payme,uzum,click,payze',
            'products' => 'required',
            'amount' => 'required|numeric',
        ]);

        $data = $request->all();

        DB::beginTransaction();
        try {
            $order =  Order::create([
                'client_id' => auth()->id(),
                'delivery_method' => $data['delivery_method'],
                'name' => $data['name'],
                'phone_number' => $data['phone_number'],
                'region_id' => $data['region_id'],
                'district_id' => $data['district_id'],
                'address' => $data['address'],
                'postcode' => $data['postcode'],
                'email' => $data['email'],
                'comments' => $data['comments'],
                'payment_method' => $data['payment_method'],
                'products' => $data['products'],
                'amount' => 0,
                'status' => 'new',
                'is_paid' => 0,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'message' => 'Successfully ordered',
            'data' => $request->all()
        ]);
    }

    public function get_products(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*' => 'required|integer',
        ]);

        $products = Product::whereIn('id', $request->products)
            ->with('info', 'images')
            ->get();

        return response([
            'products' => $products
        ]);
    }
}
