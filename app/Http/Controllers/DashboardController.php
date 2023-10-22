<?php

namespace App\Http\Controllers;

use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function get(Request $request)
    {
        $allProductsCount = Product::count();
        $allUsersCount = User::count();
        $allOrdersCount = Order::count();
        $lastMonthUsersCount = User::where('created_at', '>', date('Y-m-d', strtotime('-30 days')))
            ->count();
        $lastMonthOrdersCount = Order::where('created_at', '>', date('Y-m-d', strtotime('-30 days')))
            ->count();


        return response([
            'products_count' => $allProductsCount,
            'users_count' => $allUsersCount,
            'orders_count' => $allOrdersCount,
            'last_month_users_count' => $lastMonthUsersCount,
            'last_month_orders_count' => $lastMonthOrdersCount,
            'statistic' => $this->getStatistic(),
            'top_sales_products' => $this->getTopSalesProducts()
        ]);
    }

    public function getStatistic($begin = null, $end = null): array
    {
        $end = $end ?? date('Y-m-d');
        $begin = $begin ?? date('Y-m-d', strtotime('-30 days', strtotime($end)));
        $result = [];

        $difference = strtotime($end) - strtotime($begin);
        if ($difference/60/60/24 > 30) {
            $end = date('Y-m-d', strtotime('+30 days', strtotime($begin)));
        }
        for ($i=0; $i<30; $i++) {
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

    public function getTopSalesProducts(): \Illuminate\Support\Collection
    {
        return collect();
    }
}
