@extends('layouts.app')

@section('title', 'My Orders | My 2nd Wife')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <div class="border-b border-[#EFF0F6] pb-5">
        <h1 class="text-2xl font-bold text-[#1F1F39] font-rubik">My Orders</h1>
        <p class="text-xs text-[#6E7191] mt-1">Review your past orders and track current food deliveries</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Sidebar Navigation -->
        <div class="space-y-1 bg-white p-3 rounded-2xl border border-[#EFF0F6] shadow-sm self-start">
            <a href="{{ route('account.profile') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium text-[#6E7191] hover:text-[#1F1F39] hover:bg-[#F7F7FC] transition-colors">
                <i data-lucide="user" class="w-4 h-4 text-[#6E7191]"></i> Profile Details
            </a>
            <a href="{{ route('account.addresses') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium text-[#6E7191] hover:text-[#1F1F39] hover:bg-[#F7F7FC] transition-colors">
                <i data-lucide="map-pin" class="w-4 h-4 text-[#6E7191]"></i> Saved Addresses
            </a>
            <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-[#FFF0F6] text-primary">
                <i data-lucide="package" class="w-4 h-4 text-primary"></i> Order History
            </a>
        </div>

        <!-- Orders List -->
        <div class="md:col-span-3 space-y-4">
            @forelse($orders as $order)
            <div class="bg-white p-5 rounded-2xl border border-[#EFF0F6] shadow-sm space-y-3.5 hover:border-pink-200 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#EFF0F6] pb-3">
                    <div>
                        <span class="text-sm font-bold text-[#1F1F39] font-rubik">#{{ $order->order_number }}</span>
                        <span class="text-xs text-[#6E7191] block mt-0.5">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border self-start sm:self-auto {{ $order->status_badge_color }}">
                        {{ $order->status_label }}
                    </span>
                </div>

                <!-- Items Preview -->
                <div class="text-xs text-[#6E7191] space-y-1">
                    @foreach($order->items->take(3) as $item)
                        <p class="truncate">• {{ $item->quantity }}x {{ $item->product_name }} @if($item->variation_name) ({{ $item->variation_name }}) @endif</p>
                    @endforeach
                    @if($order->items->count() > 3)
                        <p class="text-[11px] text-[#6E7191] font-semibold">+ {{ $order->items->count() - 3 }} more item(s)</p>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-[#EFF0F6]">
                    <div>
                        <span class="text-[10px] text-[#6E7191] uppercase tracking-wider block">Total Amount</span>
                        <span class="text-base font-bold text-primary font-rubik">₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('order.track', ['token' => $order->tracking_token]) }}" 
                           class="px-3.5 py-1.5 rounded-full bg-[#FFF0F6] text-primary font-bold text-xs hover:bg-primary hover:text-white transition-colors border border-pink-200">
                            Track Live
                        </a>
                        <a href="{{ route('account.orders.details', ['orderNumber' => $order->order_number]) }}" 
                           class="px-3.5 py-1.5 rounded-full bg-[#F7F7FC] text-[#1F1F39] font-bold text-xs hover:bg-gray-200 transition-colors border border-[#EFF0F6]">
                            Details
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white p-12 rounded-2xl border border-[#EFF0F6] text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-[#F7F7FC] text-primary flex items-center justify-center mx-auto text-xl border border-[#EFF0F6]">
                    📦
                </div>
                <h3 class="text-base font-bold text-[#1F1F39] font-rubik">No Past Orders Yet</h3>
                <p class="text-xs text-[#6E7191]">When you place an order, its details and live progress will appear here.</p>
                <a href="{{ route('menu.index') }}" class="inline-block px-5 py-2.5 rounded-full bg-primary text-white text-xs font-bold hover:bg-[#E0005E] transition-colors">
                    Explore Food Menu
                </a>
            </div>
            @endforelse

            <div class="pt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

</div>
@endsection
