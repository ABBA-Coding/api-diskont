<?php

namespace App\Http\Controllers\Orders;

use App\Models\{
    Products\Product,
    Orders\Order,
    Dicoin\DicoinHistory,
    Dicoin\Dicoin
};
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orders = Order::latest();
        if(isset($request->status) && $request->status != '') $orders = $orders->where('status', $request->status);
        $orders = $orders->with('user', 'user_address', 'user_address.region', 'user_address.district', 'user_address.village', 'operator')
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
            ->with('user', 'user_address', 'user_address.region', 'user_address.district', 'user_address.village', 'operator')
            ->first();

        $history = Order::where('id', '!=', $order->id)
            ->whereHas('user', function($q) use ($order) {
                $q->where('id', $order->user->id);
            })
            ->with('user', 'user_address', 'user_address.region', 'user_address.district', 'user_address.village')
            ->paginate($this->PAGINATE);

        // status new -> pending
        if($order->status == 'new') $order->update([
            'status' => 'pending',
            'operator_id' => auth('sanctum')->id()
        ]);

        return response([
            'order' => $order,
            'history' => $history,
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
        $request->validate([
            'status' => 'required'
        ]);
        switch ($order->status) {
            case 'pending':
                $request->validate([
                    'status' => 'in:pending,accepted,canceled'
                ]);
                break;

            case 'accepted':
                $request->validate([
                    'status' => 'in:accepted,returned,done'
                ]);
                break;

            case 'returned':
                $request->validate([
                    'status' => 'in:returned'
                ]);
                break;

            case 'done':
                $request->validate([
                    'status' => 'in:done'
                ]);
                break;

            case 'canceled':
                $request->validate([
                    'status' => 'in:canceled'
                ]);
                break;

            case 'new':
                return response([
                    'message' => 'Problemi so statusami'
                ], 500);
                break;

        }
        $data = $request->all();

        DB::beginTransaction();
        try {
            if($data['status'] == 'accepted' && $order->req_sent == 0) {
                if ($order->payment_method == 'click' || $order->payment_method == 'payme' || $order->payment_method == 'cash' || $order->payment_method == 'uzum') {

                    $success = $this->req_to_stock($order);
                    if(!$success) return response([
                        'message' => 'Problemi s 1c'
                    ], 500);
                }

                if($order->status != 'accepted') $this->dicoins_for_order($order, 'plus');
            }

            if($data['status'] == 'returned') {
                if ($order->payment_method == 'click' || $order->payment_method == 'payme' || $order->payment_method == 'cash' || $order->payment_method == 'uzum') {
                    $success = $this->return_order($order);
                    if(!$success) return response([
                        'message' => 'Problemi s 1c'
                    ], 500);
                }

                if($order->status != 'returned') $this->dicoins_for_order($order, 'minus');
            }

            if($data['status'] == 'canceled' && $order->status != 'canceled') {
                if (isset($order->dicoin_history)) $order->dicoin_history->delete();
            }

            $order->update($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'message' => 'Successfully updated'
        ]);
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

    public function req_to_stock(Order $order)
    {
        $data = [
            'method' => 'create_order',
            'store' => '70e8ebf0-7548-11ed-a20f-00155d0b8c00',
            'tarif' => 1,
            'total' => $order->amount,
            'client' => $this->get_payment_1c_id($order->payment_method),
        ];

        foreach ($order->products as $key => $product) {
            $data['products'][$key]['product_id'] = Product::find($product['product_id'])->c_id;
            $data['products'][$key]['count'] = $product['count'];
            $data['products'][$key]['amount'] = $product['price_with_discount'];
        }

        // send request to 1c
        $username = env('C_USERNAME');
        $password = env('C_PASSWORD');
        $url_old = 'http://80.80.212.224:8080/Diskont/hs/web';
        $url = env('C_BASE_URL');

        $req = Http::withBasicAuth($username, $password)
            ->post($url, $data);

        if(!isset($req->json()['result']) || $req->json()['result'] != 'success!') return false;

        $order->update([
            'req_sent' => 1,
            'c_id' => $req->json()['id'],
        ]);

        foreach ($order->products as $key => $product) {

            $product_model = Product::find($product['product_id']);

            $product_model->update(['stock' => $product_model->stock - $product['count']]);
        }

        return true;
    }

    public function get_payment_1c_id($payment_method)
    {
        $data = [
            'payme' => 'ca5d62d8-26d2-11ee-a232-00155d0b8c00',
            'click' => 'bfff6436-26d2-11ee-a232-00155d0b8c00',
            'uzum' => 'd0ae983f-26d2-11ee-a232-00155d0b8c00',
            'cash' => '87c7a5df-26d5-11ee-a232-00155d0b8c00',
        ];

        return $data[$payment_method];
    }

    public function return_order(Order $order)
    {
        $data = [
            'method' => 'return_order',
            'order_id' => $order->c_id,
            'comment' => '',
        ];

        // send request to 1c
        $username = env('C_USERNAME');
        $password = env('C_PASSWORD');
        $url_old = 'http://80.80.212.224:8080/Diskont/hs/web'; // http://80.80.212.224:8080/diskont_test/hs/web
        $url = env('C_BASE_URL');

        $req = Http::withBasicAuth($username, $password)
            ->post($url, $data);

        if(!isset($req->json()['result']) || $req->json()['result'] != 'success!') return false;

        $order->update([
            'req_sent' => 0,
            'c_id' => null,
        ]);

        foreach ($order->products as $key => $product) {
            $product_model = Product::find($product['product_id']);

            $product_model->update(['stock' => $product_model->stock + $product['count']]);
        }

        return true;
    }

    public function dicoins_for_order(Order $order, $do) {
        $dicoin_save_data = [
            'user_id' => $order->user_id,
            'type' => $do,
            'order_id' => $order->id,
            'quantity' => floor($order->amount / Dicoin::latest()->first()->sum_to_dicoin)
        ];
        DicoinHistory::create($dicoin_save_data);
    }
}
