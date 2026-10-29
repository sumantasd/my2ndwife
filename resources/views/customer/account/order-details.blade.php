@extends('layouts.app')

@section('title', 'Order #' . $order->order_number . ' Details | My 2nd Wife')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" x-data="{ cancelModal: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#EFF0F6] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('account.orders') }}" class="text-xs font-bold text-[#6E7191] hover:text-primary transition-colors flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Orders
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[#1F1F39] font-rubik mt-1">Order #{{ $order->order_number }}</h1>
            <p class="text-xs text-[#6E7191] mt-1">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('order.track', ['token' => $order->tracking_token]) }}" 
               class="px-4 py-2.5 rounded-full bg-primary text-white text-xs font-bold hover:bg-[#E0005E] transition-colors shadow-sm flex items-center gap-1.5">
                <i data-lucide="navigation" class="w-4 h-4"></i>
                <span>Track Order Live</span>
            </a>

            <form action="{{ route('account.orders.reorder', ['orderNumber' => $order->order_number]) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-full bg-[#F7F7FC] text-[#1F1F39] text-xs font-bold hover:bg-gray-200 transition-colors flex items-center gap-1.5 border border-[#EFF0F6]">
                    <i data-lucide="repeat" class="w-4 h-4"></i>
                    <span>Re-Order Items</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Status & Overview -->
    <div class="bg-white p-5 rounded-2xl border border-[#EFF0F6] shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="text-[10px] text-[#6E7191] font-bold uppercase tracking-wider block font-rubik">Current Status</span>
            <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border {{ $order->status_badge_color }}">
                {{ $order->status_label }}
            </span>
        </div>

        <div>
            <span class="text-[10px] text-[#6E7191] font-bold uppercase tracking-wider block font-rubik">Payment Method</span>
            <span class="text-xs font-bold text-[#1F1F39] capitalize mt-1 block">
                {{ str_replace('_', ' ', $order->payment?->payment_method ?? 'Cash on Delivery') }} 
                ({{ $order->isPaid() ? 'Paid' : 'Pending' }})
            </span>
        </div>

        <div>
            <span class="text-[10px] text-[#6E7191] font-bold uppercase tracking-wider block font-rubik">Fulfillment</span>
            <span class="text-xs font-bold text-[#1F1F39] capitalize mt-1 block">{{ $order->order_type }}</span>
        </div>

        <div>
            <span class="text-[10px] text-[#6E7191] font-bold uppercase tracking-wider block font-rubik">Timing</span>
            <span class="text-xs font-bold text-[#1F1F39] mt-1 block">
                @if($order->order_schedule === 'scheduled')
                    📅 Scheduled ({{ $order->scheduled_at ? $order->scheduled_at->format('d M Y, h:i A') : 'Future' }})
                @else
                    ⚡ Instant Order
                @endif
            </span>
        </div>

        <!-- Cancellation Request Option if still Placed -->
        @if($order->order_status === 'placed')
        <div>
            <button @click="cancelModal = true" class="text-xs font-bold text-[#FB4E4E] hover:underline">
                Cancel Order
            </button>
        </div>
        @endif
    </div>

    <!-- Ordered Items Table -->
    <div class="bg-white p-6 sm:p-7 rounded-2xl border border-[#EFF0F6] shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-[#1F1F39] font-rubik">Items Details</h3>

        <div class="divide-y divide-[#EFF0F6]">
            @foreach($order->items as $item)
            <div class="py-4 flex items-start justify-between gap-4 text-xs">
                <div>
                    <h4 class="font-bold text-sm text-[#1F1F39]">{{ $item->quantity }}x {{ $item->product_name }}</h4>
                    @if($item->variation_name)
                        <p class="text-[#6E7191] mt-0.5">Size: {{ $item->variation_name }}</p>
                    @endif
                    @if($item->addons->count() > 0)
                        <p class="text-[11px] text-[#6E7191] mt-0.5">
                            Add-ons: {{ $item->addons->pluck('addon_name')->implode(', ') }}
                        </p>
                    @endif
                    @if($item->special_instructions)
                        <p class="text-[11px] text-amber-700 italic bg-amber-50 p-1.5 rounded-lg mt-1 inline-block">
                            "{{ $item->special_instructions }}"
                        </p>
                    @endif
                </div>
                <div class="text-right">
                    <span class="text-sm font-bold text-[#1F1F39] font-rubik">₹{{ number_format($item->line_total, 2) }}</span>
                    <span class="text-[11px] text-[#6E7191] block">₹{{ number_format($item->unit_price, 2) }} each</span>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Financial Breakdown -->
        <div class="pt-4 border-t border-[#EFF0F6] space-y-2 text-xs text-[#6E7191]">
            <div class="flex justify-between">
                <span>Items Subtotal</span>
                <span class="font-bold text-[#1F1F39]">₹{{ number_format($order->subtotal, 2) }}</span>
            </div>

            @if($order->discount_amount > 0)
            <div class="flex justify-between text-[#1AB759] font-bold">
                <span>Promo Discount ({{ $order->coupon_code }})</span>
                <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
            </div>
            @endif

            <div class="flex justify-between">
                <span>Delivery Charge</span>
                <span>₹{{ number_format($order->delivery_charge, 2) }}</span>
            </div>

            <div class="flex justify-between">
                <span>Packaging Charge</span>
                <span>₹{{ number_format($order->packaging_charge, 2) }}</span>
            </div>

            <div class="flex justify-between">
                <span>Taxes</span>
                <span>₹{{ number_format($order->tax_amount, 2) }}</span>
            </div>

            <div class="pt-3 border-t border-[#EFF0F6] flex justify-between items-baseline">
                <span class="text-sm font-bold text-[#1F1F39] font-rubik">Total Amount</span>
                <span class="text-2xl font-bold text-primary font-rubik">₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Delivery Address Info -->
    <div class="bg-white p-6 rounded-2xl border border-[#EFF0F6] shadow-sm space-y-2 text-xs">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-[#1F1F39] uppercase tracking-wider font-rubik">Delivery Details</h3>
            @if($order->delivery_latitude && $order->delivery_longitude)
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                    <i data-lucide="map-pin" class="w-3 h-3 text-emerald-600"></i>
                    GPS Pinpoint Location
                </span>
            @endif
        </div>
        <p class="font-bold text-[#1F1F39]">{{ $order->customer_name }} • {{ $order->customer_phone }}</p>
        <p class="text-[#6E7191] leading-relaxed">{{ $order->delivery_address_text ?? 'Doorstep delivery' }}</p>
        @if($order->delivery_instructions)
            <p class="text-amber-700 italic bg-amber-50 p-2 rounded-xl mt-1.5">"{{ $order->delivery_instructions }}"</p>
        @endif
    </div>

    <!-- Cancellation Modal -->
    <div x-show="cancelModal" x-cloak style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.outside="cancelModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-[#EFF0F6] space-y-4">
            <h3 class="text-base font-bold text-[#1F1F39] font-rubik">Cancel Order</h3>
            <p class="text-xs text-[#6E7191]">Are you sure you want to cancel this order? This action cannot be undone.</p>

            <form action="{{ route('account.orders.cancel', ['orderNumber' => $order->order_number]) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-[#1F1F39] mb-1">Reason for Cancellation:</label>
                    <select name="reason" class="w-full text-xs p-2.5 rounded-xl border border-[#EFF0F6] focus:border-primary focus:outline-none">
                        <option value="Changed my mind">Changed my mind</option>
                        <option value="Placed order by mistake">Placed order by mistake</option>
                        <option value="Need to change delivery address">Need to change delivery address</option>
                        <option value="Delivery time too long">Delivery time too long</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="cancelModal = false" class="px-4 py-2 text-xs font-bold text-[#6E7191] hover:text-[#1F1F39]">Back</button>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-[#FB4E4E] hover:bg-rose-700 text-white text-xs font-bold">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
