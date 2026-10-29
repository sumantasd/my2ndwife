@extends('layouts.delivery')

@section('title', 'Rider Dashboard')

@section('content')
<div class="space-y-5 sm:space-y-6">

    @php
        $riderAvatar = (!empty($rider->avatar))
            ? (str_starts_with($rider->avatar, 'http') ? $rider->avatar : asset('storage/' . ltrim($rider->avatar, '/')))
            : null;
        $riderInitials = strtoupper(substr($rider->name ?? 'R', 0, 1));
    @endphp

    <!-- Welcome & Duty Status Card -->
    <div class="bg-white rounded-3xl p-4 sm:p-6 border border-[#E8E5EF] shadow-2xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3 sm:gap-4">
            <!-- Circular Avatar (Uses real photo or fallback to clean initials) -->
            @if($riderAvatar)
                <img src="{{ $riderAvatar }}" 
                     alt="{{ $rider->name }}" 
                     class="w-12 h-12 sm:w-14 sm:h-14 rounded-full object-cover border-2 border-white shadow-xs shrink-0 ring-2 ring-[#FF006E]/20">
            @else
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gradient-to-br from-[#FF006E] to-[#E60063] text-white font-black text-lg sm:text-xl flex items-center justify-center shadow-xs shrink-0 border-2 border-white ring-2 ring-[#FF006E]/20">
                    {{ $riderInitials }}
                </div>
            @endif

            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-base sm:text-lg font-black text-[#202033]">Hello, {{ $rider->name }}</h1>
                    @if($rider->is_online)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-[#00B884] border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#00B884]"></span>
                            <span>On Duty</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 text-[#D97706] border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#F5A623]"></span>
                            <span>Off Duty</span>
                        </span>
                    @endif
                </div>
                <p class="text-xs text-[#667085] font-medium mt-0.5">
                    {{ $rider->vehicle_type ?? 'Motorcycle' }} • {{ $rider->vehicle_number ?? 'WB Fleet' }} • <span class="font-mono text-[#202033] font-bold">{{ $rider->phone }}</span>
                </p>
                <p class="text-[11px] text-[#667085] font-medium mt-1 flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#FF006E]"></i>
                    <span>{{ now()->format('l, d M Y') }}</span>
                </p>
            </div>
        </div>

        <!-- Quick Access Buttons -->
        <div class="flex items-center gap-2 w-full md:w-auto">
            <a href="{{ route('delivery.available.index') }}" 
               class="flex-1 md:flex-initial px-3.5 sm:px-4 py-2.5 rounded-2xl bg-[#FFF0F6] hover:bg-[#FFE5F0] text-[#FF006E] text-xs font-black transition-all flex items-center justify-center gap-2 border border-[#FEE2EB] shadow-2xs">
                <i data-lucide="inbox" class="w-4 h-4 stroke-[#FF006E]"></i>
                <span>Available Orders ({{ $availableOrdersCount }})</span>
            </a>
            <a href="{{ route('delivery.orders.index') }}" 
               class="flex-1 md:flex-initial px-3.5 sm:px-4 py-2.5 rounded-2xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-black transition-all flex items-center justify-center gap-2 border border-blue-200 shadow-2xs">
                <i data-lucide="package" class="w-4 h-4 stroke-blue-700"></i>
                <span>My Assigned ({{ $activeDeliveriesCount }})</span>
            </a>
        </div>
    </div>

    <!-- 6 Real-Data Summary Cards (High Contrast, Uniform Design & Non-Overlapping) -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4">
        
        <!-- Card 1: Available Orders -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#E8E5EF] shadow-2xs flex flex-col justify-between hover:shadow-xs transition-shadow">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#667085] truncate">Available</span>
                <div class="w-8 h-8 rounded-xl bg-pink-50 text-[#FF006E] flex items-center justify-center border border-pink-100 shrink-0">
                    <i data-lucide="inbox" class="w-4 h-4 stroke-[#FF006E] stroke-[2.2]"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-[#202033] font-mono tracking-tight">{{ $availableOrdersCount }}</div>
            <a href="{{ route('delivery.available.index') }}" class="text-[11px] font-black text-[#FF006E] hover:underline mt-2 pt-2 border-t border-[#F2F0F7] flex items-center justify-between">
                <span>Claim orders</span>
                <span>→</span>
            </a>
        </div>

        <!-- Card 2: My Assigned Orders (Restored, High-Contrast & Prominent) -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#E8E5EF] shadow-2xs flex flex-col justify-between hover:shadow-xs transition-shadow">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#667085] truncate">Assigned</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                    <i data-lucide="package" class="w-4 h-4 stroke-blue-600 stroke-[2.2]"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-[#202033] font-mono tracking-tight">{{ $activeDeliveriesCount }}</div>
            <a href="{{ route('delivery.orders.index') }}" class="text-[11px] font-black text-blue-600 hover:underline mt-2 pt-2 border-t border-[#F2F0F7] flex items-center justify-between">
                <span>View assigned</span>
                <span>→</span>
            </a>
        </div>

        <!-- Card 3: Active Transit -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#E8E5EF] shadow-2xs flex flex-col justify-between hover:shadow-xs transition-shadow">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#667085] truncate">Active Transit</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 shrink-0">
                    <i data-lucide="bike" class="w-4 h-4 stroke-indigo-600 stroke-[2.2]"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-indigo-600 font-mono tracking-tight">{{ $inTransitCount ?? 0 }}</div>
            <div class="text-[11px] font-bold text-[#667085] mt-2 pt-2 border-t border-[#F2F0F7] truncate">
                {{ ($inTransitCount ?? 0) > 0 ? 'On the road' : 'Idle / awaiting' }}
            </div>
        </div>

        <!-- Card 4: Delivered Today -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#E8E5EF] shadow-2xs flex flex-col justify-between hover:shadow-xs transition-shadow">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#667085] truncate">Delivered Today</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#00B884] flex items-center justify-center border border-emerald-100 shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4 stroke-[#00B884] stroke-[2.2]"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-[#00B884] font-mono tracking-tight">{{ $todayCompletedCount }}</div>
            <div class="text-[11px] font-bold text-[#667085] mt-2 pt-2 border-t border-[#F2F0F7] truncate">
                Month: <strong class="text-[#202033]">{{ $monthCompletedCount }}</strong>
            </div>
        </div>

        <!-- Card 5: Today's Earnings -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#E8E5EF] shadow-2xs flex flex-col justify-between hover:shadow-xs transition-shadow">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#667085] truncate">Today's Earnings</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-[#D97706] flex items-center justify-center border border-amber-100 shrink-0">
                    <i data-lucide="wallet" class="w-4 h-4 stroke-[#D97706] stroke-[2.2]"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-[#D97706] font-mono tracking-tight">₹{{ number_format($todayEarnings, 2) }}</div>
            <div class="text-[11px] font-bold text-[#667085] mt-2 pt-2 border-t border-[#F2F0F7] truncate">
                Rate: ₹{{ number_format($commissionRate, 0) }}/drop
            </div>
        </div>

        <!-- Card 6: Monthly Earnings -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#E8E5EF] shadow-2xs flex flex-col justify-between hover:shadow-xs transition-shadow">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#667085] truncate">Monthly Earnings</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100 shrink-0">
                    <i data-lucide="trending-up" class="w-4 h-4 stroke-purple-600 stroke-[2.2]"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-purple-700 font-mono tracking-tight">₹{{ number_format($monthEarnings, 2) }}</div>
            <div class="text-[11px] font-bold text-[#667085] mt-2 pt-2 border-t border-[#F2F0F7] truncate">
                Total: <strong class="text-[#202033]">{{ $totalCompletedCount }} drops</strong>
            </div>
        </div>
    </div>

    <!-- Active Delivery Spotlight (If an order is ready or in transit) -->
    @if($activeOrder)
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#FF006E] shadow-xs relative overflow-hidden">
        
        <!-- Header Strip -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E8E5EF] pb-4 mb-5">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#FF006E] text-white">
                        Current Active Delivery
                    </span>
                    <span class="font-mono font-black text-sm text-[#202033]">
                        #{{ $activeOrder->order_number }}
                    </span>
                    @if($activeOrder->order_schedule === 'scheduled')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                            Scheduled: {{ $activeOrder->scheduled_at ? $activeOrder->scheduled_at->format('d M, h:i A') : 'Scheduled' }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-[#FFF0F6] text-[#FF006E] border border-[#FEE2EB]">
                            Instant Delivery
                        </span>
                    @endif
                </div>
                <h3 class="text-base sm:text-lg font-black text-[#202033] mt-1">{{ $activeOrder->customer_name }}</h3>
            </div>

            <!-- Current Status Badge -->
            <div>
                <span class="px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider {{ $activeOrder->order_status === 'out_for_delivery' ? 'bg-[#FF006E] text-white shadow-2xs' : 'bg-indigo-600 text-white' }}">
                    {{ $activeOrder->status_label }}
                </span>
            </div>
        </div>

        <!-- 3-Column Info Grid: Customer, Destination & Payment -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs mb-5">
            
            <!-- Customer Contact Column -->
            <div class="p-4 rounded-2xl bg-[#F7F7FC] border border-[#E8E5EF] space-y-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#667085] block">Customer Contact</span>
                <p class="font-black text-sm text-[#202033]">{{ $activeOrder->customer_name }}</p>
                <p class="font-mono font-bold text-[#55597D]">{{ $activeOrder->customer_phone }}</p>
                @if($activeOrder->customer_phone)
                    <a href="tel:{{ $activeOrder->customer_phone }}" 
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#00B884] hover:bg-emerald-700 text-white font-black text-xs transition-colors mt-2 shadow-2xs">
                        <i data-lucide="phone-call" class="w-3.5 h-3.5 stroke-white"></i>
                        <span>Call Customer</span>
                    </a>
                @endif
            </div>

            <!-- Destination Column & Google Maps Navigation Link -->
            <div class="p-4 rounded-2xl bg-[#F7F7FC] border border-[#E8E5EF] space-y-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#667085] block">Delivery Destination</span>
                <p class="text-xs font-bold text-[#202033] line-clamp-2 leading-relaxed">
                    {{ $activeOrder->delivery_address_text ?? 'Kolkata Delivery Area' }}
                </p>
                @if($activeOrder->delivery_distance_km)
                    <p class="text-[11px] text-[#FF006E] font-black">
                        Distance: {{ $activeOrder->delivery_distance_km }} km away
                    </p>
                @endif

                @php
                    $mapsDestination = (!empty($activeOrder->delivery_latitude) && !empty($activeOrder->delivery_longitude))
                        ? "https://www.google.com/maps/dir/?api=1&destination=" . (float)$activeOrder->delivery_latitude . "," . (float)$activeOrder->delivery_longitude
                        : "https://www.google.com/maps/search/?api=1&query=" . urlencode($activeOrder->delivery_address_text ?? 'Kolkata');
                @endphp
                <a href="{{ $mapsDestination }}" target="_blank" rel="noopener noreferrer" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs transition-colors mt-2 shadow-2xs">
                    <i data-lucide="navigation" class="w-3.5 h-3.5 stroke-white"></i>
                    <span>Start Navigation Map</span>
                </a>
            </div>

            <!-- Payment Collection Banner -->
            <div class="p-4 rounded-2xl bg-[#F7F7FC] border border-[#E8E5EF] space-y-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-[#667085] block">Payment Collection</span>
                @if($activeOrder->payment && $activeOrder->payment->payment_method === 'cash_on_delivery')
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
                        <span class="text-[10px] font-black uppercase tracking-wider block text-[#D97706]">CASH ON DELIVERY</span>
                        <div class="text-base font-black text-[#B45309] font-mono mt-0.5">COLLECT: ₹{{ number_format($activeOrder->total_amount, 2) }}</div>
                    </div>
                @else
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                        <span class="text-[10px] font-black uppercase tracking-wider block text-[#00B884]">PREPAID ONLINE</span>
                        <div class="text-xs font-bold text-[#065F46] mt-0.5">Do not collect cash from customer</div>
                    </div>
                @endif
                <p class="text-[11px] text-[#667085] font-bold">
                    {{ $activeOrder->items->count() }} item(s) • Rider Earning: +₹{{ number_format($commissionRate, 2) }}
                </p>
            </div>
        </div>

        <!-- Action Strip -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-[#E8E5EF]">
            <a href="{{ route('delivery.orders.show', $activeOrder) }}" 
               class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-[#202033] text-xs font-bold transition-colors">
                View Full Order Details →
            </a>

            @if($activeOrder->order_status === \App\Models\Order::STATUS_READY)
                <form action="{{ route('delivery.orders.status', $activeOrder) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="{{ \App\Models\Order::STATUS_OUT_FOR_DELIVERY }}">
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#FF006E] to-[#E60063] hover:from-[#E60063] text-white text-xs font-black shadow-xs flex items-center gap-2 cursor-pointer active:scale-[0.99]">
                        <i data-lucide="bike" class="w-4 h-4 stroke-white"></i>
                        <span>Pick Up & Start Delivery</span>
                    </button>
                </form>
            @elseif($activeOrder->order_status === \App\Models\Order::STATUS_OUT_FOR_DELIVERY)
                <a href="{{ route('delivery.orders.show', $activeOrder) }}" 
                   class="px-5 py-2.5 rounded-xl bg-[#00B884] hover:bg-emerald-700 text-white text-xs font-black shadow-xs flex items-center gap-2 cursor-pointer active:scale-[0.99]">
                    <i data-lucide="check-circle" class="w-4 h-4 stroke-white"></i>
                    <span>Confirm Delivery Complete</span>
                </a>
            @endif
        </div>
    </div>
    @endif

    <!-- Recent Assignments List -->
    <div class="bg-white rounded-3xl border border-[#E8E5EF] shadow-2xs overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-[#E8E5EF] flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-black text-[#202033]">Recent Assigned Orders</h2>
                <p class="text-xs text-[#667085] font-medium">Latest delivery dispatches to your fleet account</p>
            </div>
            <a href="{{ route('delivery.orders.index') }}" class="text-xs font-black text-[#FF006E] hover:underline">
                View All Assigned ({{ $activeDeliveriesCount }}) →
            </a>
        </div>

        <div class="divide-y divide-[#E8E5EF]">
            @forelse($recentAssignments as $order)
                <div class="p-4 sm:p-5 hover:bg-[#F7F7FC] transition-colors flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono font-black text-sm text-[#202033]">#{{ $order->order_number }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase border {{ $order->status_badge_color }}">
                                {{ $order->status_label }}
                            </span>
                            @if($order->order_schedule === 'scheduled')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    Scheduled: {{ $order->scheduled_at ? $order->scheduled_at->format('d M, h:i A') : 'Later' }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-[#202033] font-bold">
                            {{ $order->customer_name }} • <span class="text-[#55597D] font-mono">{{ $order->customer_phone }}</span>
                        </p>
                        <p class="text-[11px] text-[#667085] line-clamp-1">
                            {{ $order->delivery_address_text ?? 'Kolkata Address' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                        <div class="text-right">
                            <span class="text-xs font-mono font-black text-[#202033] block">₹{{ number_format($order->total_amount, 2) }}</span>
                            <span class="text-[10px] font-extrabold uppercase {{ $order->payment && $order->payment->payment_method === 'cash_on_delivery' ? 'text-[#D97706]' : 'text-[#00B884]' }}">
                                {{ $order->payment && $order->payment->payment_method === 'cash_on_delivery' ? 'COD' : 'Paid' }}
                            </span>
                        </div>
                        <a href="{{ route('delivery.orders.show', $order) }}" 
                           class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-xs font-black text-[#202033] transition-colors">
                            Details →
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-[#667085] text-xs">
                    <i data-lucide="inbox" class="w-8 h-8 mx-auto text-[#667085] mb-2"></i>
                    <p class="font-black text-[#202033]">No orders assigned currently.</p>
                    <p class="text-[11px] mt-1 text-[#667085]">Check Available Orders to self-claim ready deliveries.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
