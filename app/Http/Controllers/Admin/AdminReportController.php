<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function index(Request $request): View
    {
        $range = $request->query('range', 'last_30_days');
        [$startDate, $endDate] = $this->parseDateRange($range, $request);

        $ordersQuery = Order::whereBetween('created_at', [$startDate, $endDate]);

        $totalOrders = (clone $ordersQuery)->count();
        $completedOrders = (clone $ordersQuery)->where('order_status', Order::STATUS_DELIVERED)->count();
        $cancelledOrders = (clone $ordersQuery)->where('order_status', Order::STATUS_CANCELLED)->count();
        $totalRevenue = (clone $ordersQuery)->where('order_status', '!=', Order::STATUS_CANCELLED)->sum('total_amount');
        $averageOrderValue = $totalOrders > 0 ? ($totalRevenue / max(1, $totalOrders - $cancelledOrders)) : 0;

        // Daily breakdown
        $dailyReports = Order::select(
            DB::raw('DATE(created_at) as order_date'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(CASE WHEN order_status = "delivered" THEN 1 ELSE 0 END) as delivered_orders'),
            DB::raw('SUM(CASE WHEN order_status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders'),
            DB::raw('SUM(CASE WHEN order_status != "cancelled" THEN total_amount ELSE 0 END) as daily_revenue'),
            DB::raw('SUM(discount_amount) as total_discount'),
            DB::raw('SUM(delivery_charge) as total_delivery_fee')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('order_date')
            ->orderByDesc('order_date')
            ->get();

        // Top 10 Items
        $topDishes = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(line_total) as total_revenue'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        return view('admin.reports.index', compact(
            'range',
            'startDate',
            'endDate',
            'totalOrders',
            'completedOrders',
            'cancelledOrders',
            'totalRevenue',
            'averageOrderValue',
            'dailyReports',
            'topDishes'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $range = $request->query('range', 'last_30_days');
        [$startDate, $endDate] = $this->parseDateRange($range, $request);

        $fileName = 'sales_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';

        $dailyReports = Order::select(
            DB::raw('DATE(created_at) as order_date'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(CASE WHEN order_status = "delivered" THEN 1 ELSE 0 END) as delivered_orders'),
            DB::raw('SUM(CASE WHEN order_status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders'),
            DB::raw('SUM(CASE WHEN order_status != "cancelled" THEN total_amount ELSE 0 END) as daily_revenue'),
            DB::raw('SUM(discount_amount) as total_discount'),
            DB::raw('SUM(delivery_charge) as total_delivery_fee')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('order_date')
            ->orderByDesc('order_date')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return Response::stream(function () use ($dailyReports) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Date',
                'Total Orders',
                'Delivered Orders',
                'Cancelled Orders',
                'Revenue (INR)',
                'Discounts Given (INR)',
                'Delivery Fees (INR)',
            ]);

            foreach ($dailyReports as $row) {
                fputcsv($handle, [
                    $row->order_date,
                    $row->total_orders,
                    $row->delivered_orders,
                    $row->cancelled_orders,
                    $row->daily_revenue,
                    $row->total_discount,
                    $row->total_delivery_fee,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function parseDateRange(string $range, Request $request): array
    {
        return match ($range) {
            'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'last_7_days' => [Carbon::today()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfDay()],
            'custom' => [
                Carbon::parse($request->query('start_date', Carbon::today()->subDays(30)))->startOfDay(),
                Carbon::parse($request->query('end_date', Carbon::today()))->endOfDay(),
            ],
            default => [Carbon::today()->subDays(29)->startOfDay(), Carbon::now()->endOfDay()],
        };
    }
}
