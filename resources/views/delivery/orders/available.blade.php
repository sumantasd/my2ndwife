@extends('layouts.delivery')

@section('title', 'Available Orders')

@section('content')
<div class="space-y-6">

    <!-- Header & Search -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E8E5EF] shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-base sm:text-lg font-black text-[#202033]">Available Orders to Claim</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-[#FFF0F6] text-[#FF006E] border border-[#FEE2EB]">
                    {{ $orders->total() }} Available
                </span>
            </div>
            <p class="text-xs text-[#55597D] mt-1">
                Browse unassigned delivery orders ready for fleet pickup. Claiming assigns the delivery to you instantly.
            </p>
        </div>

        <form action="{{ route('delivery.available.index') }}" method="GET" class="w-full md:w-80">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#667085]">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search order # or address..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-[#E8E5EF] bg-white text-xs text-[#202033] placeholder-[#667085] focus:border-[#FF006E] focus:ring-2 focus:ring-[#FF006E]/20 focus:outline-none transition-all">
            </div>
        </form>
    </div>

    <!-- Available Orders Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($orders as $order)
            <div class="bg-white rounded-3xl p-5 border border-[#E8E5EF] shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between gap-4">
                <div class="space-y-3">
                    <!-- Top Info -->
                    <div class="flex items-center justify-between gap-2 border-b border-[#E8E5EF] pb-3">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-black text-sm text-[#202033]">#{{ $order->order_number }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase border {{ $order->status_badge_color }}">
                                {{ $order->status_label }}
                            </span>
                        </div>
                        <span class="text-xs text-[#667085] font-medium">
                            {{ $order->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <!-- Destination & Distance -->
                    <div class="space-y-1 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Delivery Address</span>
                            @if($order->delivery_distance_km)
                                <span class="text-[11px] font-black text-[#FF006E]">
                                    {{ $order->delivery_distance_km }} km away
                                </span>
                            @endif
                        </div>
                        <p class="text-[#202033] font-medium line-clamp-2 leading-relaxed">
                            {{ $order->delivery_address_text ?? 'Kolkata delivery location' }}
                        </p>
                    </div>

                    <!-- Items & Schedule -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="text-[#55597D] font-bold">
                            {{ $order->items->count() }} item(s) • Total: ₹{{ number_format($order->total_amount, 2) }}
                        </span>
                        @if($order->order_schedule === 'scheduled')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                                Scheduled: {{ $order->scheduled_at ? $order->scheduled_at->format('d M, h:i A') : 'Later' }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-[#FFF0F6] text-[#FF006E] border border-[#FEE2EB]">
                                Instant Delivery
                            </span>
                        @endif
                    </div>

                    <!-- Earning Pill -->
                    <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-between text-xs">
                        <span class="font-black text-amber-900">Delivery Earning:</span>
                        <span class="font-black text-amber-700 text-sm font-mono">+₹{{ number_format($commissionRate, 2) }}</span>
                    </div>
                </div>

                <!-- Claim Button Form -->
                <div class="pt-2 border-t border-[#E8E5EF] flex items-center gap-2">
                    <form action="{{ route('delivery.orders.claim', $order) }}" method="POST" class="w-full"
                          onsubmit="return confirm('Do you want to claim order #{{ $order->order_number }} and start delivery?');">
                        @csrf
                        <button type="submit" 
                                class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-[#FF006E] to-[#E60063] hover:from-[#E60063] hover:to-[#CC0058] text-white text-xs font-black shadow-xs transition-all active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Claim This Delivery Order</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-[#E8E5EF] text-[#667085] space-y-2">
                <i data-lucide="inbox" class="w-10 h-10 mx-auto text-[#667085]"></i>
                <h3 class="font-extrabold text-sm text-[#202033]">No available orders right now</h3>
                <p class="text-xs text-[#55597D]">All current orders have either been claimed or already dispatched. Check back shortly!</p>
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
