<?php

namespace App\Http\Controllers\web;

use App\Models\UserAddress;
use App\Models\User;
use App\Models\Dicoin\Dicoin;
use App\Models\RegionGroup;
use Illuminate\Support\Facades\DB;
use App\Models\{Products\Product, Orders\Order, Orders\OneClickOrder, Settings\Region};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'delivery_method' => 'required|in:pickup,courier',
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'phone_number' => 'required|numeric|min:998000000001|max:998999999999',
            'user_address_id' => 'nullable|integer',
            // 'district_id' => 'nullable|integer',
            // 'address' => 'nullable',
            'postcode' => 'nullable|max:255',
            'email' => 'nullable|max:255',
            'comments' => 'nullable',
            'payment_method' => 'required|in:cash,payme,uzum,click,payze',
            'products' => 'required',
            'amount' => 'required|numeric',
        ]);
        $data = $request->all();
        $data['user_id'] = auth('sanctum')->id();
        $data['status'] = 'new';
        $data['is_paid'] = 0;
        $data['delivery_price'] = $this->get_delivery_price($request);
        $data['products'] = $this->products_reformat($data['products']);
        // $data['amount'] = $this->amount_calculate($data['products'], $data['region_id'], $data['delivery_method']);

        foreach ($data['products'] as $item) {
            $product = Product::find($item['product_id']);

            if(!$product->discount && !empty($product->discount)) {
                if($product->discount->percent != null) {
                    $inner_amount = $product->price * $product->discount->percent / 100;
                } else {
                    $inner_amount = $product->price - $product->discount->amount;
                    if($inner_amount < 0) $inner_amount = 0;
                }

                $item['price_with_discount'] = $inner_amount;
            } else {
                $item['price_with_discount'] = $product->price;
            }
        }

        // amount calculate
        $amount = 0;
        foreach ($data['products'] as $product) {
            $amount += $product['price_with_discount'];
        }
        $data['amount'] = $amount + $this->get_delivery_price($request);

        DB::beginTransaction();
        try {
            $order = Order::create($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'message' => 'Successfully ordered',
            'order' => $order
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

        foreach ($products as $product) {
//            dd($product);
            $this->without_lang([$product->info]);
        }

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
            'count' => 'required|integer'
        ]);
        $data = $request->all();

        OneClickOrder::create($data);

        return response([
            'message' => 'Successfully ordered'
        ]);
    }

    public function get_delivery_price(Request $request)
    {
        $delivery_price = 0;
        $data = $request->all();

        if(!$data['user_address_id']) return $delivery_price;

        if($data['delivery_method'] != 'courier') return $delivery_price;

        $address = UserAddress::find($data['user_address_id']);
        if(!$address) return reponse([
            'message' => 'Resurs udalen'
        ], 404);
            
        $region_group = RegionGroup::whereHas('regions', function ($q) use ($address) {
                $q->where('id', $address->region_id);
            })
            ->first();

        if(!$region_group) return reponse([
            'message' => 'Resurs udalen'
        ], 404);

        return $region_group->delivery_price;
    }

    public function products_reformat($products): array
    {
        $result = [];

        foreach ($products as $key => $item) {
            $result[$key] = $item;
            $product = Product::find($item['product_id'])->toArray();
            $result[$key]['price'] = $product['price'];
            if($product['discount']) {
                if($product['discount']['percent']) {
                    $result[$key]['price_with_discount'] = $product['price'] * $product['discount']['percent'] / 100;
                } else if($product['discount']['amount']) {
                    $result[$key]['price_with_discount'] = $product['price'] - $product['discount']['amount'];
                    if($result[$key]['price_with_discount'] > 0) $result[$key]['price_with_discount'] = 0;
                } else {
                    $result[$key]['price_with_discount'] = $product['price'];
                }
            } else {
                $result[$key]['price_with_discount'] = $product['price'];
            }
        }

        return $result;
    }
}
