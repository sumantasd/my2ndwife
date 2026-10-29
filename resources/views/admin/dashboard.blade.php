@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('header_title', 'Live Dashboard')

@section('content')
<div class="space-y-8">

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Today's Sales -->
        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Today's Sales</span>
                <p class="text-2xl font-black text-[#25232A]">₹{{ number_format($todaySales, 2) }}</p>
                <p class="text-[11px] text-[#F50070] font-semibold">{{ $todayOrders }} orders placed today</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#FFF3F8] text-[#F50070] flex items-center justify-center text-xl shadow-sm">
                💰
            </div>
        </div>

        <!-- Pending New Orders -->
        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Pending Orders</span>
                <p class="text-2xl font-black text-amber-600">{{ $pendingOrders }}</p>
                <p class="text-[11px] text-amber-700 font-semibold">Requires Kitchen Acceptance</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-sm">
                ⏳
            </div>
        </div>

        <!-- Preparing / In Kitchen -->
        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">In Preparation</span>
                <p class="text-2xl font-black text-purple-600">{{ $preparingOrders }}</p>
                <p class="text-[11px] text-purple-700 font-semibold">Accepted / Cooking / Packed</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shadow-sm">
                👨‍🍳
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Completed Deliveries</span>
                <p class="text-2xl font-black text-emerald-600">{{ $completedOrders }}</p>
                <p class="text-[11px] text-emerald-700 font-semibold">Total Revenue: ₹{{ number_format($totalSales, 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-sm">
                ✅
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- 7-Day Sales & Order Velocity Chart -->
        <div class="lg:col-span-8 p-6 sm:p-8 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-[#25232A]">Sales & Orders Velocity</h3>
                    <p class="text-xs text-gray-400">Last 7 days performance metrics</p>
                </div>
                <span class="text-xs font-bold text-[#F50070] bg-[#FFF3F8] px-3 py-1 rounded-full border border-[#F0E6EC]">Live Realtime Data</span>
            </div>

            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Order Status Distribution Donut -->
        <div class="lg:col-span-4 p-6 sm:p-8 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm space-y-4 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-extrabold text-[#25232A]">Order Status Distribution</h3>
                <p class="text-xs text-gray-400">Breakdown of orders in system</p>
            </div>

            <div class="h-52 relative flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>

            <div class="grid grid-cols-2 gap-2 text-[11px] pt-2 border-t border-gray-100">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Placed: {{ $pendingOrders }}</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Cooking: {{ $preparingOrders }}</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span> On Way: {{ $outForDeliveryOrders }}</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Delivered: {{ $completedOrders }}</div>
            </div>
        </div>
    </div>

    <!-- Tables Row: Recent Orders & Best Sellers -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Recent Orders (8 cols) -->
        <div class="lg:col-span-8 p-6 sm:p-8 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-[#25232A]">Recent Orders</h3>
                    <p class="text-xs text-gray-400">Latest customer orders needing fulfillment</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-[#F50070] hover:underline">
                    View All Orders →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-2">Order #</th>
                            <th class="py-3 px-2">Customer</th>
                            <th class="py-3 px-2">Type</th>
                            <th class="py-3 px-2">Status</th>
                            <th class="py-3 px-2">Total</th>
                            <th class="py-3 px-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentOrders as $order)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="py-3 px-2 font-mono font-bold text-[#25232A]">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-[#F50070] hover:underline">
                                    {{ $order->order_number }}
                                </a>
                                <span class="block text-[10px] text-gray-400 font-normal">{{ $order->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="py-3 px-2 font-medium">
                                <span class="font-bold text-[#25232A] block">{{ $order->customer_name }}</span>
                                <span class="text-gray-400 text-[10px]">{{ $order->customer_phone }}</span>
                            </td>
                            <td class="py-3 px-2">
                                <span class="capitalize px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">
                                    {{ $order->order_type }}
                                </span>
                            </td>
                            <td class="py-3 px-2">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase border {{ $order->status_badge_color }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-2 font-black text-[#25232A]">
                                ₹{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="py-3 px-2 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" 
                                   class="px-3 py-1.5 rounded-xl bg-[#FFF3F8] text-[#F50070] font-bold text-xs hover:bg-[#F50070] hover:text-white transition-colors border border-[#F0E6EC]">
                                    Manage
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-gray-400">No orders placed yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Selling Dishes (4 cols) -->
        <div class="lg:col-span-4 p-6 sm:p-8 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm space-y-4">
            <div>
                <h3 class="text-base font-extrabold text-[#25232A]">Top 5 Best-Sellers</h3>
                <p class="text-xs text-gray-400">Highest volume dishes ordered</p>
            </div>

            <div class="divide-y divide-gray-100 text-xs">
                @forelse($topDishes as $dish)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-[#25232A]">{{ $dish->product_name }}</h4>
                        <span class="text-[11px] text-gray-400 font-semibold">{{ $dish->total_qty }} portions ordered</span>
                    </div>
                    <span class="font-black text-[#F50070]">₹{{ number_format($dish->total_revenue, 2) }}</span>
                </div>
                @empty
                <p class="text-center text-gray-400 py-6 text-xs">Awaiting first batch of orders.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Sales Line & Bar Chart
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: @json($salesChartLabels),
                datasets: [
                    {
                        label: 'Sales Revenue (₹)',
                        data: @json($salesChartValues),
                        borderColor: '#F50070',
                        backgroundColor: 'rgba(245, 0, 112, 0.08)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointBackgroundColor: '#F50070',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F0E6EC' },
                        ticks: { font: { size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }

    // 2. Status Donut Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(@json($statusDistribution)),
                datasets: [{
                    data: Object.values(@json($statusDistribution)),
                    backgroundColor: ['#F59E0B', '#A855F7', '#F50070', '#10B981', '#EF4444'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
@endpush
