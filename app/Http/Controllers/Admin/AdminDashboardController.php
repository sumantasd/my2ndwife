<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today();

        // Real Database KPI Metrics
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $todaySales = Order::whereDate('created_at', $today)
            ->where('order_status', '!=', Order::STATUS_CANCELLED)
            ->sum('total_amount');

        $totalOrders = Order::count();
        $totalSales = Order::where('order_status', '!=', Order::STATUS_CANCELLED)->sum('total_amount');

        $pendingOrders = Order::where('order_status', Order::STATUS_PLACED)->count();
        $preparingOrders = Order::whereIn('order_status', [Order::STATUS_ACCEPTED, Order::STATUS_PREPARING, Order::STATUS_READY])->count();
        $outForDeliveryOrders = Order::where('order_status', Order::STATUS_OUT_FOR_DELIVERY)->count();
        $completedOrders = Order::where('order_status', Order::STATUS_DELIVERED)->count();
        $cancelledOrders = Order::where('order_status', Order::STATUS_CANCELLED)->count();

        $totalCustomers = User::where('role', 'customer')->count();
        $totalProducts = Product::count();

        // Recent Orders
        $recentOrders = Order::with(['items', 'payment'])
            ->latest()
            ->take(8)
            ->get();

        // Top 5 Best-Selling Dishes
        $topDishes = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(line_total) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // 7-day Sales Chart Data
        $salesChartLabels = [];
        $salesChartValues = [];
        $ordersChartValues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $salesChartLabels[] = $date->format('D, M d');

            $daySales = Order::whereDate('created_at', $date)
                ->where('order_status', '!=', Order::STATUS_CANCELLED)
                ->sum('total_amount');
            $salesChartValues[] = (float) $daySales;

            $dayOrdersCount = Order::whereDate('created_at', $date)->count();
            $ordersChartValues[] = $dayOrdersCount;
        }

        // Status Distribution
        $statusDistribution = [
            'Placed' => $pendingOrders,
            'Preparing' => $preparingOrders,
            'Out for Delivery' => $outForDeliveryOrders,
            'Delivered' => $completedOrders,
            'Cancelled' => $cancelledOrders,
        ];

        return view('admin.dashboard', compact(
            'todayOrders',
            'todaySales',
            'totalOrders',
            'totalSales',
            'pendingOrders',
            'preparingOrders',
            'outForDeliveryOrders',
            'completedOrders',
            'cancelledOrders',
            'totalCustomers',
            'totalProducts',
            'recentOrders',
            'topDishes',
            'salesChartLabels',
            'salesChartValues',
            'ordersChartValues',
            'statusDistribution'
        ));
    }
}
