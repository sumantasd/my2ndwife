@extends('layouts.delivery')

@section('title', 'My Assigned Orders')

@section('content')
<div class="space-y-6">

    <!-- Header & Search Bar -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E8E5EF] shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-base sm:text-lg font-black text-[#202033]">My Assigned Orders</h1>
            <p class="text-xs text-[#55597D]">Deliveries assigned to you by restaurant dispatch</p>
        </div>

        <form action="{{ route('delivery.orders.index') }}" method="GET" class="w-full md:w-80">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#667085]">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search order #, customer, phone..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-[#E8E5EF] bg-white text-xs text-[#202033] placeholder-[#667085] focus:border-[#FF006E] focus:ring-2 focus:ring-[#FF006E]/20 focus:outline-none transition-all">
            </div>
        </form>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-2 border-b border-[#E8E5EF] pb-2 overflow-x-auto text-xs font-bold scrollbar-none">
        <a href="{{ route('delivery.orders.index', ['tab' => 'active', 'search' => request('search')]) }}" 
           class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $tab === 'active' ? 'bg-[#FF006E] text-white shadow-xs' : 'text-[#55597D] hover:text-[#202033] hover:bg-gray-100' }}">
            <span>Active Deliveries</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $tab === 'active' ? 'bg-white/20 text-white' : 'bg-gray-100 text-[#202033]' }}">
                {{ $activeCount }}
            </span>
        </a>

        <a href="{{ route('delivery.orders.index', ['tab' => 'delivered', 'search' => request('search')]) }}" 
           class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 shrink-0 {{ $tab === 'delivered' ? 'bg-[#FF006E] text-white shadow-xs' : 'text-[#55597D] hover:text-[#202033] hover:bg-gray-100' }}">
            <span>Delivered</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $tab === 'delivered' ? 'bg-white/20 text-white' : 'bg-gray-100 text-[#202033]' }}">
                {{ $deliveredCount }}
            </span>
        </a>

        <a href="{{ route('delivery.orders.index', ['tab' => 'all', 'search' => request('search')]) }}" 
           class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $tab === 'all' ? 'bg-[#FF006E] text-white shadow-xs' : 'text-[#55597D] hover:text-[#202033] hover:bg-gray-100' }}">
            All Orders
        </a>
    </div>

    <!-- Orders Cards List (Mobile-optimized cards) -->
    <div class="space-y-4">
        @forelse($orders as $order)
            <div class="bg-white rounded-3xl p-5 border border-[#E8E5EF] shadow-xs hover:shadow-md transition-shadow space-y-4">
                <!-- Card Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#E8E5EF] pb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono font-black text-sm text-[#202033]">#{{ $order->order_number }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase border {{ $order->status_badge_color }}">
                            {{ $order->status_label }}
                        </span>
                        @if($order->order_schedule === 'scheduled')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                                Scheduled: {{ $order->scheduled_at ? $order->scheduled_at->format('d M, h:i A') : 'Scheduled' }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-[#FFF0F6] text-[#FF006E] border border-[#FEE2EB]">
                                Instant
                            </span>
                        @endif
                    </div>
                    <span class="text-xs text-[#667085] font-medium">
                        Assigned: {{ $order->created_at->diffForHumans() }}
                    </span>
                </div>

                <!-- Customer Details & Address -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <!-- Customer Information -->
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Customer</span>
                        <p class="font-bold text-[#202033] text-sm">{{ $order->customer_name }}</p>
                        <p class="font-mono text-[#55597D]">{{ $order->customer_phone }}</p>
                        @if($order->customer_phone)
                            <a href="tel:{{ $order->customer_phone }}" 
                               class="inline-flex items-center gap-1.5 text-xs font-black text-[#00B884] hover:text-emerald-700 hover:underline pt-1">
                                <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                <span>Call Customer</span>
                            </a>
                        @endif
                    </div>

                    <!-- Destination & Map -->
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Delivery Address</span>
                        <p class="text-[#202033] line-clamp-2 leading-relaxed">
                            {{ $order->delivery_address_text ?? 'Address will be confirmed upon call' }}
                        </p>
                        @if($order->delivery_distance_km)
                            <p class="text-[11px] font-black text-[#FF006E]">
                                Distance: {{ $order->delivery_distance_km }} km
                            </p>
                        @endif

                        @php
                            $mapsUrl = (!empty($order->delivery_latitude) && !empty($order->delivery_longitude))
                                ? "https://www.google.com/maps/dir/?api=1&destination=" . (float)$order->delivery_latitude . "," . (float)$order->delivery_longitude
                                : "https://www.google.com/maps/search/?api=1&query=" . urlencode($order->delivery_address_text ?? 'Kolkata');
                        @endphp
                        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline pt-1">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                            <span>Open Navigation Map</span>
                        </a>
                    </div>
                </div>

                <!-- Footer Summary & Actions -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-3 border-t border-[#E8E5EF]">
                    <div class="flex items-center gap-3">
                        @if($order->payment && $order->payment->payment_method === 'cash_on_delivery')
                            <span class="px-2.5 py-1 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px] font-black">
                                COD: Collect ₹{{ number_format($order->total_amount, 2) }}
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-[11px] font-black">
                                PREPAID (No Cash)
                            </span>
                        @endif
                        <span class="text-xs text-[#667085] font-medium">
                            {{ $order->items->count() }} item(s)
                        </span>
                    </div>

                    <a href="{{ route('delivery.orders.show', $order) }}" 
                       class="w-full sm:w-auto px-5 py-2.5 rounded-2xl bg-gradient-to-r from-[#FF006E] to-[#E60063] hover:from-[#E60063] hover:to-[#CC0058] text-white text-xs font-black shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                        <span>Manage & Deliver</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 text-center border border-[#E8E5EF] text-[#667085] space-y-2">
                <i data-lucide="package-open" class="w-10 h-10 mx-auto text-[#667085]"></i>
                <h3 class="font-extrabold text-sm text-[#202033]">No orders found</h3>
                <p class="text-xs text-[#55597D]">
                    {{ request('search') ? 'No assigned orders match your search criteria.' : 'You have no assigned orders in this tab.' }}
                </p>
                @if($tab === 'active')
                    <div class="pt-2">
                        <a href="{{ route('delivery.available.index') }}" class="px-4 py-2 rounded-xl bg-[#FFF0F6] text-[#FF006E] border border-[#FEE2EB] text-xs font-bold hover:bg-[#FFE5EE] transition-colors inline-block">
                            Browse Available Orders to Claim →
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="pt-2">
            {{ $orders->links() }}
        </div>
    @endif

</div>
@endsection
