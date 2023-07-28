<?php

namespace App\Http\Controllers\Orders;

use App\Models\{
    Products\Product,
    Orders\Order
};
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource. +
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orders = Order::latest();
        if(isset($request->status) && $request->status != '') $orders = $orders->where('status', $request->status);
        $orders = $orders->with('user', 'user_address', 'user_address.region', 'user_address.district', 'user_address.village')
            ->paginate($this->PAGINATE);

        foreach($orders as $order) {
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
            'orders' => $orders
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $order = Order::where('id', $order->id)
            ->with('client')
            ->first();

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

        return response([
            'order' => $order
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return response([
            'message' => 'Success'
        ]);
    }

    public function counts()
    {
        $orders = Order::all();

        $counts = [];
        foreach (['new', 'canceled', 'accepted', 'done', 'returned', 'pending'] as $status) {
            $counts[$status] = count($orders->where('status', $status));
        }

        return response([
            'counts' => $counts
        ]);
    }
}
