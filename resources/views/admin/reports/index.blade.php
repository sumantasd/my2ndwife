@extends('layouts.admin')

@section('title', 'Sales & Financial Reports')
@section('header_title', 'Analytics & Reports')

@section('content')
<div class="space-y-8">

    <!-- Filter and Export Header Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <div>
            <h2 class="text-xl font-black text-[#25232A]">Financial & Order Performance</h2>
            <p class="text-xs text-[#66636D] mt-0.5">
                Report window: <span class="font-bold text-[#F50070]">{{ $startDate->format('M d, Y') }}</span> to <span class="font-bold text-[#F50070]">{{ $endDate->format('M d, Y') }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Preset Quick Filters -->
            <div class="flex flex-wrap items-center bg-[#FFF3F8] p-1.5 rounded-2xl border border-[#F0E6EC] text-xs font-bold">
                <a href="{{ route('admin.reports.index', ['range' => 'today']) }}" 
                   class="px-3 py-1.5 rounded-xl transition-colors {{ $range === 'today' ? 'bg-[#F50070] text-white shadow-sm' : 'text-[#66636D] hover:text-[#25232A]' }}">
                    Today
                </a>
                <a href="{{ route('admin.reports.index', ['range' => 'yesterday']) }}" 
                   class="px-3 py-1.5 rounded-xl transition-colors {{ $range === 'yesterday' ? 'bg-[#F50070] text-white shadow-sm' : 'text-[#66636D] hover:text-[#25232A]' }}">
                    Yesterday
                </a>
                <a href="{{ route('admin.reports.index', ['range' => 'last_7_days']) }}" 
                   class="px-3 py-1.5 rounded-xl transition-colors {{ $range === 'last_7_days' ? 'bg-[#F50070] text-white shadow-sm' : 'text-[#66636D] hover:text-[#25232A]' }}">
                    Last 7 Days
                </a>
                <a href="{{ route('admin.reports.index', ['range' => 'this_month']) }}" 
                   class="px-3 py-1.5 rounded-xl transition-colors {{ $range === 'this_month' ? 'bg-[#F50070] text-white shadow-sm' : 'text-[#66636D] hover:text-[#25232A]' }}">
                    This Month
                </a>
                <a href="{{ route('admin.reports.index', ['range' => 'last_30_days']) }}" 
                   class="px-3 py-1.5 rounded-xl transition-colors {{ ($range === 'last_30_days' || !$range) ? 'bg-[#F50070] text-white shadow-sm' : 'text-[#66636D] hover:text-[#25232A]' }}">
                    Last 30 Days
                </a>
            </div>

            <!-- Export CSV Button -->
            <a href="{{ route('admin.reports.export', ['range' => $range, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-bold shadow-sm transition-transform active:scale-95">
                <i data-lucide="download" class="w-4 h-4"></i>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Custom Date Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-[#F0E6EC] shadow-sm">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-4 text-xs">
            <input type="hidden" name="range" value="custom">
            <span class="font-bold text-[#25232A]">Custom Date Range:</span>
            <div class="flex items-center gap-2">
                <label for="start_date" class="text-gray-500 font-semibold">From:</label>
                <input type="date" id="start_date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" 
                       class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:border-[#F50070]">
            </div>
            <div class="flex items-center gap-2">
                <label for="end_date" class="text-gray-500 font-semibold">To:</label>
                <input type="date" id="end_date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" 
                       class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:border-[#F50070]">
            </div>
            <button type="submit" class="px-4 py-1.5 bg-[#25232A] hover:bg-black text-white font-bold rounded-xl transition-colors">
                Apply Custom Range
            </button>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Net Revenue</span>
            <p class="text-2xl font-black text-[#F50070] mt-1">₹{{ number_format($totalRevenue, 2) }}</p>
            <p class="text-[11px] text-gray-500 mt-1">Excludes cancelled orders</p>
        </div>

        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Orders</span>
            <p class="text-2xl font-black text-[#25232A] mt-1">{{ number_format($totalOrders) }}</p>
            <p class="text-[11px] text-gray-500 mt-1">Placed in this period</p>
        </div>

        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Delivered Orders</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($completedOrders) }}</p>
            <p class="text-[11px] text-emerald-700 font-semibold mt-1">
                {{ $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0 }}% Fulfillment rate
            </p>
        </div>

        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Cancelled Orders</span>
            <p class="text-2xl font-black text-rose-600 mt-1">{{ number_format($cancelledOrders) }}</p>
            <p class="text-[11px] text-rose-700 font-semibold mt-1">
                {{ $totalOrders > 0 ? round(($cancelledOrders / $totalOrders) * 100, 1) : 0 }}% Cancellation rate
            </p>
        </div>

        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Avg. Order Value (AOV)</span>
            <p class="text-2xl font-black text-indigo-600 mt-1">₹{{ number_format($averageOrderValue, 2) }}</p>
            <p class="text-[11px] text-indigo-700 font-semibold mt-1">Per active order</p>
        </div>
    </div>

    <!-- Charts and Top Items Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Daily Revenue Trend Chart -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-[#F0E6EC] shadow-sm">
            <h3 class="text-base font-black text-[#25232A] mb-4">Revenue Trend (Daily)</h3>
            <div class="h-64">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>

        <!-- Top Selling Dishes -->
        <div class="bg-white p-6 rounded-3xl border border-[#F0E6EC] shadow-sm">
            <h3 class="text-base font-black text-[#25232A] mb-4">Top 10 Popular Dishes</h3>
            @if($topDishes->isNotEmpty())
                <div class="space-y-3.5">
                    @foreach($topDishes as $index => $dish)
                        <div class="flex items-center justify-between text-xs pb-3 border-b border-gray-100 last:border-b-0 last:pb-0">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 h-6 rounded-lg bg-[#FFF3F8] text-[#F50070] font-black flex items-center justify-center text-[10px]">
                                    #{{ $index + 1 }}
                                </span>
                                <div>
                                    <p class="font-bold text-[#25232A] line-clamp-1">{{ $dish->product_name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $dish->total_qty }} units sold</p>
                                </div>
                            </div>
                            <span class="font-black text-[#F50070]">₹{{ number_format($dish->total_revenue, 0) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-gray-400 text-xs">
                    No dish sales recorded in this period.
                </div>
            @endif
        </div>
    </div>

    <!-- Daily Breakdown Table -->
    <div class="bg-white rounded-3xl border border-[#F0E6EC] shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-black text-[#25232A]">Daily Financial Breakdown</h3>
            <span class="text-xs text-gray-500 font-semibold">{{ $dailyReports->count() }} active dates found</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#FFF3F8] text-[#66636D] font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-6">Date</th>
                        <th class="py-3 px-6 text-center">Total Orders</th>
                        <th class="py-3 px-6 text-center">Delivered</th>
                        <th class="py-3 px-6 text-center">Cancelled</th>
                        <th class="py-3 px-6 text-right">Discounts</th>
                        <th class="py-3 px-6 text-right">Delivery Fees</th>
                        <th class="py-3 px-6 text-right">Net Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($dailyReports as $row)
                        <tr class="hover:bg-pink-50/30 transition-colors">
                            <td class="py-4 px-6 font-bold text-[#25232A]">
                                {{ \Carbon\Carbon::parse($row->order_date)->format('M d, Y (D)') }}
                            </td>
                            <td class="py-4 px-6 text-center font-bold text-[#25232A]">
                                {{ $row->total_orders }}
                            </td>
                            <td class="py-4 px-6 text-center text-emerald-600 font-bold">
                                {{ $row->delivered_orders }}
                            </td>
                            <td class="py-4 px-6 text-center text-rose-600 font-bold">
                                {{ $row->cancelled_orders }}
                            </td>
                            <td class="py-4 px-6 text-right text-gray-500 font-semibold">
                                ₹{{ number_format($row->total_discount, 2) }}
                            </td>
                            <td class="py-4 px-6 text-right text-gray-500 font-semibold">
                                ₹{{ number_format($row->total_delivery_fee, 2) }}
                            </td>
                            <td class="py-4 px-6 text-right font-black text-[#F50070]">
                                ₹{{ number_format($row->daily_revenue, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                <i data-lucide="bar-chart-2" class="w-8 h-8 mx-auto mb-2 text-gray-300"></i>
                                No orders or financial activity recorded within this date range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('revenueTrendChart');
        if (ctx) {
            // Reverse chronological for left-to-right trend
            const dailyData = @json($dailyReports->reverse()->values());
            const labels = dailyData.map(d => d.order_date);
            const revenues = dailyData.map(d => parseFloat(d.daily_revenue || 0));

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.length ? labels : ['No Data'],
                    datasets: [{
                        label: 'Net Revenue (₹)',
                        data: revenues.length ? revenues : [0],
                        borderColor: '#F50070',
                        backgroundColor: 'rgba(245, 0, 112, 0.08)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#F50070',
                        pointBorderColor: '#FFFFFF',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Revenue: ₹' + Number(context.parsed.y).toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value;
                                }
                            },
                            grid: { color: '#F0E6EC' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
