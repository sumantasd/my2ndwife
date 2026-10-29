@extends('layouts.delivery')

@section('title', 'Delivery History')

@section('content')
<div class="space-y-6">

    <!-- Header & Date Range Filter -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E8E5EF] shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-base sm:text-lg font-black text-[#202033]">Delivery History</h1>
            <p class="text-xs text-[#55597D]">Log of all successfully completed food deliveries</p>
        </div>

        <form action="{{ route('delivery.history') }}" method="GET" class="w-full md:w-auto flex flex-col sm:flex-row items-center gap-2">
            <select name="date_range" onchange="this.form.submit()" class="w-full sm:w-auto px-4 py-2.5 rounded-2xl border border-[#E8E5EF] bg-white text-xs font-bold text-[#202033] focus:border-[#FF006E] focus:ring-2 focus:ring-[#FF006E]/20 focus:outline-none">
                <option value="today" {{ $dateRange === 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ $dateRange === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="this_week" {{ $dateRange === 'this_week' ? 'selected' : '' }}>This Week</option>
                <option value="this_month" {{ $dateRange === 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="all" {{ $dateRange === 'all' ? 'selected' : '' }}>All Time</option>
            </select>

            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#667085]">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search order #, customer..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-[#E8E5EF] bg-white text-xs text-[#202033] placeholder-[#667085] focus:border-[#FF006E] focus:ring-2 focus:ring-[#FF006E]/20 focus:outline-none">
            </div>
        </form>
    </div>

    <!-- Stats Summary for Selected Period -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="bg-white rounded-3xl p-5 border border-[#E8E5EF] shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085] block mb-1">Delivered in Period</span>
            <div class="text-2xl font-black text-[#202033]">{{ $orders->total() }}</div>
            <p class="text-[11px] text-[#55597D] mt-1">Total Lifetime: {{ $totalDeliveredCount }}</p>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-[#E8E5EF] shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085] block mb-1">Period Earnings</span>
            <div class="text-2xl font-black text-amber-600 font-mono">₹{{ number_format($filteredEarnings, 2) }}</div>
            <p class="text-[11px] text-[#55597D] mt-1">Rate: ₹{{ number_format($commissionRate, 0) }}/order</p>
        </div>

        <div class="col-span-2 sm:col-span-1 bg-white rounded-3xl p-5 border border-[#E8E5EF] shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085] block mb-1">Success Rate</span>
            <div class="text-2xl font-black text-[#00B884]">100%</div>
            <p class="text-[11px] text-[#00B884] font-bold mt-1">All assigned orders completed</p>
        </div>
    </div>

    <!-- History Cards / Table -->
    <div class="bg-white rounded-3xl border border-[#E8E5EF] shadow-xs overflow-hidden">
        <div class="divide-y divide-[#E8E5EF]">
            @forelse($orders as $order)
                <div class="p-5 hover:bg-gray-50/70 transition-colors flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="space-y-1.5 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono font-black text-sm text-[#202033]">#{{ $order->order_number }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-50 text-emerald-900 border border-emerald-200">
                                Delivered
                            </span>
                            <span class="text-xs text-[#667085] font-medium">
                                {{ $order->delivered_at ? $order->delivered_at->format('d M Y, h:i A') : $order->updated_at->format('d M Y, h:i A') }}
                            </span>
                        </div>

                        <p class="text-xs text-[#202033] font-bold">
                            {{ $order->customer_name }} • <span class="text-[#55597D] font-normal">{{ $order->customer_phone }}</span>
                        </p>

                        <p class="text-[11px] text-[#55597D] line-clamp-1">
                            {{ $order->delivery_address_text }}
                        </p>

                        @if($order->delivery_proof_notes)
                            <p class="text-[11px] text-emerald-950 font-medium bg-emerald-50 p-1.5 rounded-lg border border-emerald-200 inline-block">
                                Note: {{ $order->delivery_proof_notes }}
                            </p>
                        @endif
                    </div>

                    <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end border-t md:border-t-0 pt-3 md:pt-0 border-[#E8E5EF]">
                        <div class="text-right">
                            <span class="text-sm font-black text-amber-600 font-mono block">+₹{{ number_format($commissionRate, 2) }}</span>
                            <span class="text-[10px] text-[#667085] font-mono">Bill: ₹{{ number_format($order->total_amount, 2) }}</span>
                        </div>

                        <a href="{{ route('delivery.orders.show', $order) }}" 
                           class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-xs font-bold text-[#202033] transition-colors">
                            Details →
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-[#667085] text-xs">
                    <i data-lucide="history" class="w-8 h-8 mx-auto text-[#667085] mb-2"></i>
                    <p class="font-bold">No delivery history in this period.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if($orders->hasPages())
        <div class="pt-2">
            {{ $orders->links() }}
        </div>
    @endif

</div>
@endsection
