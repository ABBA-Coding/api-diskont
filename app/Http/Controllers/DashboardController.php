<?php

namespace App\Http\Controllers;

use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Settings\Region;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function get(Request $request)
    {
//        $activeProductsCount = Product::where('status', 'active')
//            ->count();
//        $inactiveProductsCount = Product::where('status', 'inactive')
//            ->count();
        $allUsersCount = User::count();
        $allOrdersCount = Order::count();
        $allOrdersAmount = Order::where('status', 'done')
            ->sum('amount');

        $todayOrdersCount = Order::whereDate('created_at', date('Y-m-d'))
            ->count();
        $todayOrdersAmount = Order::whereDate('created_at', date('Y-m-d'))
            ->sum('amount');
        $todayNewUsersCount = User::whereDate('created_at', date('Y-m-d'))
            ->count();

//        $lastMonthUsersCount = User::where('created_at', '>', date('Y-m-d', strtotime('-30 days')))
//            ->count();
//        $lastMonthOrdersCount = Order::where('created_at', '>', date('Y-m-d', strtotime('-30 days')))
//            ->count();


        return response([
//            'products_count' => [
//                'active' => $activeProductsCount,
//                'inactive' => $inactiveProductsCount,
//            ],
            'users_count' => $allUsersCount,
            'orders_count' => $allOrdersCount,
            'orders_amount' => $allOrdersAmount,

            'today_users_count' => $todayNewUsersCount,
            'today_orders_count' => $todayOrdersCount,
            'today_orders_amount' => $todayOrdersAmount,

//            'last_month_users_count' => $lastMonthUsersCount,
//            'last_month_orders_count' => $lastMonthOrdersCount,
            'statistic' => $this->getStatistic($request->input('begin') ?? null, $request->input('end') ?? null),
            'clients_from' => $this->getClientsFromRegions(),
            'top_sales_products' => $this->getTopSalesProducts()
        ]);
    }

    public function getStatistic($begin = null, $end = null): array
    {
        $end = $end ?? date('Y-m-d');
        $begin = $begin ?? date('Y-m-d', strtotime('-30 days', strtotime($end)));
        $result = [];

        $difference = strtotime($end) - strtotime($begin);
        $days = $difference/60/60/24;
        if ($difference/60/60/24 > 30) {
            $end = date('Y-m-d', strtotime('+30 days', strtotime($begin)));
            $days = 30;
        }
        for ($i=0; $i<$days; $i++) {
            $orders = Order::whereDate('created_at', date('Y-m-d', strtotime('+'.$i.' days', strtotime($begin))))
                ->get();
            $result[] = [
                'date' => date('Y-m-d', strtotime('+'.$i.' days', strtotime($begin))),
                'all_orders' => $orders->count(),
                'completed_orders' => $orders->where('status', 'done')->count(),
                'completed_orders_sum' => $orders->where('status', 'done')->sum('amount')
            ];
        }

        return $result;
    }

    public function getTopSalesProducts(): array
    {
        $productIds = [];
        $productsCount = [];

        $orders = Order::select('products')
            ->get();
        foreach ($orders as $order) {
            foreach ($order->products as $product) {
                if (in_array($product['product_id'], $productIds)) {
                    $key = key($productIds);
                    $productsCount[$key] += $product['count'];
                } else {
                    $productIds[] = $product['product_id'];
                    $productsCount[] = $product['count'];
                }
            }
        }

        asort($productsCount);
        $sortedArray = array_reverse($productsCount, true);

        $topProducts = [];
        $counter = 0;
        foreach ($sortedArray as $key => $productId) {
            $topProducts[] = Product::where('id', $productIds[$key])
                ->with('images')
                ->first();

            if ($counter > 10) break;

            $counter ++;
        }

        return $topProducts;
    }

    public function getClientsFromRegions(): array
    {
        $regions = Region::all();

        $result = [];

        foreach ($regions as $key => $region) {
            $result[$key] = [
                'region' => $region,
                'clients' => User::whereHas('addresses', function ($q) use ($region) {
                    $q->where('region_id', $region->id);
                })->count(),
            ];

            $orders = 0;
            $users = User::whereHas('addresses', function ($q) use ($region) {
                $q->where('region_id', $region->id);
            })->get();
            foreach ($users as $item) {
                $orders += $item->orders->count();
            }
            $result[$key]['orders'] = $orders;
        }

        return $result;
    }
}
