<?php

namespace App\Http\Controllers\web;

use App\Models\UserAddress;
use App\Models\Dicoin\Dicoin;
use App\Models\Dicoin\DicoinHistory;
use App\Models\RegionGroup;
use App\Models\ExchangeRate;
use App\Traits\CategoryTrait;
use Illuminate\Support\Facades\DB;
use App\Models\{Category, Products\Product, Orders\Order, Orders\OneClickOrder};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    use CategoryTrait;

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
            'dicoin' => 'nullable|integer'
        ]);
        $data = $request->all();
        $data['user_id'] = auth('sanctum')->id();
        $data['status'] = 'new';
        $data['is_paid'] = 0;
        if(!$this->get_delivery_price($request)['success']) return response([
            'message' => $this->get_delivery_price($request)['message']
        ], 400);
        $data['delivery_price'] = $this->get_delivery_price($request)['delivery_price'];
        $data['products'] = $this->products_reformat($data['products']);

        if(!$this->check_products($data['products'])) return response([
            'message' => 'Netu takoe kolichestvo produktov v sklade'
        ], 400);
        // $data['amount'] = $this->amount_calculate($data['products'], $data['region_id'], $data['delivery_method']);

        $kurs = ExchangeRate::latest()
            ->first()
            ->exchange_rate;
        $total_used_dicoins_amount = 0;
        foreach ($data['products'] as $item) {
            $product = Product::find($item['product_id']);

            // if(!$product->discount && !empty($product->discount)) {
            //     if($product->discount->percent != null) {
            //         $inner_amount = $product->price * (1 - ($product->discount->percent / 100));
            //     } else {
            //         $inner_amount = $product->price - $product->discount->amount;
            //         if($inner_amount < 0) $inner_amount = 0;
            //     }

            //     $item['price_with_discount'] = $inner_amount;
            // } else {
            //     $item['price_with_discount'] = $product->price * $kurs;
            // }

            if($product->dicoin) {
                $total_used_dicoins_amount += $item['price_with_discount'] * ($product->dicoin / 100);
            }
        }

        // amount calculate
        $amount = 0;
        foreach ($data['products'] as $product) {
            $amount += $product['price_with_discount'] * $product['count'];
        }
        $data['amount'] = $amount + $this->get_delivery_price($request)['delivery_price'];


        // est li u polzovatelya takoe kolichestvo dicoinov
        if(auth('sanctum')->user()->dicoin['quantity'] < $data['dicoin']) return response([
            'message' => 'U vas net takoe kolichestvo dicoinov'
        ], 400);

        // otnimem summu, kotoriy ispolzoval dicoini
        if($data['dicoin']) {
            $which_count_dicoin_use = floor($total_used_dicoins_amount / Dicoin::latest()->first()->dicoin_to_sum);
            if($data['dicoin'] > $which_count_dicoin_use) {
                return response([
                    'message' => 'Vi mojete ispolzovat tolko '.$which_count_dicoin_use.' dicoinov'
                ], 400);
            } else {
                $dicoin_data = [
                    'user_id' => auth('sanctum')->id(),
                    'type' => 'minus',
                    'order_id' => null,
                    'quantity' => $data['dicoin']
                ];
                $dicoin_history = DicoinHistory::create($dicoin_data);

                $data['amount'] = $data['amount'] - ($data['dicoin'] * Dicoin::latest()->first()->dicoin_to_sum);
            }
        }

        DB::beginTransaction();
        try {
            $order = Order::create($data);

            if($data['dicoin']) $dicoin_history->update(['order_id' => $order->id]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        $redirect_url = null;
        if($order->payment_method == 'click' || $order->payment_method == 'payme') {
            $redirect_url = url('/').'/pay/'.$order->payment_method.'/'.$order->id.'/'.$order->amount;
        }

        return response([
            'message' => 'Successfully ordered',
            'order' => $order,
            'redirect_url' => $redirect_url
        ]);
    }

    public function get_products(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*' => 'required|integer',
        ]);

        // get categories
        $categories = Category::whereHas('product_infos', function($q) use ($request) {
            $q->whereHas('products', function($qi) use ($request) {
                $qi->whereIn('id', $request->input('products'));
            });
        })
            ->with('parent')
            ->get();

        $products = Product::whereIn('id', $request->products);
        if ($request->input('category') != null && $request->input('category') != '') {
            $getParentCategoryCategories = $this->getParentCategoryCategories($request->input('category'), $categories);

            $products = $products->whereHas('info', function ($q) use ($getParentCategoryCategories, $request) {
                $q->whereIn('category_id', $getParentCategoryCategories->pluck('id')->toArray());
            });
        }
        $products = $products
            ->with('info', 'info.brand', 'info.category', 'images')
            ->get();

        foreach ($products as $product) {
            $this->without_lang([$product, $product->info, $product->info->category]);
        }

        return response([
            'products' => $products
        ]);
    }

    // berilgan $categories lardan $parentCategory ga tegishlilarini qaytaradi
    private function getParentCategoryCategories($parentCategoryId, $categories): \Illuminate\Support\Collection
    {
        $parentCategory = Category::find($parentCategoryId);

        $parentCategoryCategories = $this->get_children($parentCategory);
        $parentCategoryCategories = $parentCategoryCategories->filter(function ($item) use ($categories) {
            return in_array($item->id, $categories->pluck('id')->toArray());
        })->values();

        return $parentCategoryCategories;
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

        $oneClickOrder = OneClickOrder::create($data);
        $this->sendMessageToTelegram($oneClickOrder);

        return response([
            'message' => 'Successfully ordered'
        ]);
    }

    public function sendMessageToTelegram($oneClickOrder)
    {
        $botToken = env('BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');
        $baseUrl = 'https://api.telegram.org/bot';

        $text = '<b>Номер телефона:</b> '.$oneClickOrder->phone_number.';'.PHP_EOL.'<b>ФИО: </b>'.$oneClickOrder->name.';'.PHP_EOL.'<b>Продукт:</b>'.($oneClickOrder->product->name['ru'] ?? '--').';'.PHP_EOL.'<b>Кол-во: </b>'.$oneClickOrder->count;

        Http::get($baseUrl.$botToken.'/sendMessage?chat_id='.$chatId.'&text='.$text.'&parse_mode=HTML');
    }

    public function get_delivery_price(Request $request): array
    {
        $delivery_price = 0;
        $data = $request->all();

        if(!$data['user_address_id']) return [
            'success' => 1,
            'delivery_price' => $delivery_price
        ];

        if($data['delivery_method'] != 'courier') return [
            'success' => 1,
            'delivery_price' => $delivery_price
        ];

        $address = UserAddress::find($data['user_address_id']);
        if(!$address) return [
            'success' => 0,
            'message' => 'Resurs udalen'
        ];

        $region_group = RegionGroup::whereHas('regions', function ($q) use ($address) {
                $q->where('id', $address->region_id);
            })
            ->first();

        if(!$region_group) return [
            'success' => 0,
            'message' => 'Resurs udalen'
        ];

        return [
            'success' => 1,
            'delivery_price' => $region_group->delivery_price
        ];
    }

    public function products_reformat($products): array
    {
        $result = [];
        $kurs = ExchangeRate::latest()
            ->first()
            ->exchange_rate;

        foreach ($products as $key => $item) {
            $result[$key] = $item;
            $product = Product::find($item['product_id'])->toArray();
            $product['price'] = $product['price'] * $kurs;
            $result[$key]['price'] = $product['price'];
            if($product['discount']) {
                if($product['discount']['pivot']['percent']) {
                    $result[$key]['price_with_discount'] = $product['price'] * (1 - ($product['discount']['pivot']['percent'] / 100));
                } else if($product['discount']['pivot']['amount']) {
                    $result[$key]['price_with_discount'] = $product['price'] - $product['discount']['pivot']['amount'];
                    if($result[$key]['price_with_discount'] < 0) $result[$key]['price_with_discount'] = 0;
                } else {
                    $result[$key]['price_with_discount'] = $product['price'];
                }
            } else {
                $result[$key]['price_with_discount'] = $product['price'];
            }
        }

        return $result;
    }

    // est li takoe kol-vo tovarov na sklade
    function check_products($products)
    {
        /*
            $products = [
                [
                    "count" => 4
                    "product_id" => 237
                    "price" => 4437500
                    "price_with_discount" => 4437500
                ],
                ...
            ];
        */

        foreach ($products as $key => $product) {

            $model = Product::find($product['product_id']);
            if(!$model || $model->stock < $product['count']) return false;
        }

        return true;
    }
}
