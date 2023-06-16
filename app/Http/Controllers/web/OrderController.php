<?php

namespace App\Http\Controllers\web;

use Illuminate\Support\Facades\DB;
use App\Models\{
    Products\Product,
    Orders\Order,
    Orders\OneClickOrder,
};
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
        $data['client_id'] = auth()->id();
        $data['status'] = 'new';
        $data['amount'] = $this->amount_calculate($request);
        $data['is_paid'] = 0;

        DB::beginTransaction();
        try {
            Order::create($data);

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

    public function one_click(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|numeric|min:998000000001|max:998999999998',
            'name' => 'required|max:255',
            'product_id' => 'required|integer',
        ]);
        $data = $request->all();

        OneClickOrder::create($data);

        return response([
            'message' => 'Successfully ordered'
        ]);
    }

    public function amount_calculate(Request $request)
    {
        $amount = 0;

        foreach ($request->products as $id) {
            $product = Product::find($id);
            if($product) {
                if(!$product->discount && !empty($product->discount)) {
                    if($product->discount->percent != null) {
                        $inner_amount = $product->price * $product->discount->percent / 100;
                    } else {
                        $inner_amount = $product->price - $product->discount->amount;
                        if($inner_amount < 0) $inner_amount = 0;
                    }

                    $amount += $inner_amount;
                }

                $amount += $product->price;
            }

        }

        return $amount;
    }
}
