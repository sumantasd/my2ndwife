@extends('layouts.delivery')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Top Navigation & Header -->
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('delivery.orders.index') }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-[#55597D] hover:text-[#202033] transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Orders</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-black uppercase border {{ $order->status_badge_color }}">
                {{ $order->status_label }}
            </span>
            @if($order->order_schedule === 'scheduled')
                <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Scheduled: {{ $order->scheduled_at ? $order->scheduled_at->format('d M, h:i A') : 'Later' }}
                </span>
            @endif
        </div>
    </div>

    <!-- Quick Touch Action Banner for Field Delivery -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E8E5EF] shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#E8E5EF] pb-3">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Order Reference</span>
                <h1 class="text-lg sm:text-xl font-black text-[#202033]">Order #{{ $order->order_number }}</h1>
                <p class="text-xs text-[#55597D]">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085]">Your Earning</span>
                <div class="text-lg font-black text-amber-600 font-mono">+₹{{ number_format($commissionRate, 2) }}</div>
            </div>
        </div>

        <!-- Touch-Friendly Field Buttons: Phone Call & Maps Navigation -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
            @if($order->customer_phone)
                <a href="tel:{{ $order->customer_phone }}" 
                   class="py-3.5 px-4 rounded-2xl bg-[#00B884] hover:bg-emerald-700 text-white font-black text-xs shadow-xs transition-all flex items-center justify-center gap-2.5 active:scale-[0.99] cursor-pointer">
                    <i data-lucide="phone-call" class="w-4 h-4"></i>
                    <span>Call Customer ({{ $order->customer_phone }})</span>
                </a>
            @else
                <button disabled class="py-3.5 px-4 rounded-2xl bg-gray-100 text-gray-400 font-bold text-xs flex items-center justify-center gap-2 cursor-not-allowed">
                    <i data-lucide="phone-off" class="w-4 h-4"></i>
                    <span>No Customer Phone Available</span>
                </button>
            @endif

            @if($mapUrl)
                <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer"
                   class="py-3.5 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs shadow-xs transition-all flex items-center justify-center gap-2.5 active:scale-[0.99] cursor-pointer">
                    <i data-lucide="navigation" class="w-4 h-4"></i>
                    <span>Start Google Maps Navigation</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Status Advance Section -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E8E5EF] shadow-xs space-y-4">
        <h2 class="text-xs font-black text-[#202033] uppercase tracking-wider">Delivery Workflow Action</h2>

        @if($order->order_status === \App\Models\Order::STATUS_READY)
            <div class="p-5 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-900 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center shrink-0">
                        <i data-lucide="chef-hat" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black">Food is Ready at Restaurant Counter</h3>
                        <p class="text-[11px] text-indigo-700">Please collect food from the kitchen and tap the button below when you are departing.</p>
                    </div>
                </div>

                <form action="{{ route('delivery.orders.status', $order) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="{{ \App\Models\Order::STATUS_OUT_FOR_DELIVERY }}">
                    <button type="submit" 
                            class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-[#FF006E] to-[#E60063] hover:from-[#E60063] text-white font-black text-xs shadow-xs flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                        <i data-lucide="bike" class="w-4 h-4"></i>
                        <span>Confirm Pickup & Start Delivery (Out for Delivery)</span>
                    </button>
                </form>
            </div>
        @elseif($order->order_status === \App\Models\Order::STATUS_OUT_FOR_DELIVERY)
            <div class="p-5 rounded-2xl bg-[#FFF0F6] border border-[#FEE2EB] space-y-4">
                <div class="flex items-center gap-3 text-[#FF006E]">
                    <div class="w-8 h-8 rounded-full bg-[#FF006E] text-white flex items-center justify-center shrink-0">
                        <i data-lucide="bike" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-[#202033]">Order is in Transit to Customer</h3>
                        <p class="text-[11px] text-[#55597D]">Once delivered, please enter any proof note below and confirm delivery.</p>
                    </div>
                </div>

                <form action="{{ route('delivery.orders.status', $order) }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="status" value="{{ \App\Models\Order::STATUS_DELIVERED }}">

                    <div>
                        <label for="delivery_proof_notes" class="block text-xs font-bold text-[#202033] mb-1">
                            Proof of Delivery Note (Optional)
                        </label>
                        <input type="text" 
                                id="delivery_proof_notes"
                                name="delivery_proof_notes" 
                                placeholder="e.g. Handed to customer at door / Left with security"
                                class="w-full p-3 rounded-xl border border-[#E8E5EF] bg-white text-xs text-[#202033] focus:border-[#FF006E] focus:outline-none focus:ring-2 focus:ring-[#FF006E]/20">
                    </div>

                    <button type="submit" 
                            onclick="return confirm('Have you safely handed the order to the customer?');"
                            class="w-full py-3.5 px-4 rounded-2xl bg-[#00B884] hover:bg-emerald-700 text-white font-black text-xs shadow-xs flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Confirm Order Delivered Successfully</span>
                    </button>
                </form>
            </div>
        @elseif($order->order_status === \App\Models\Order::STATUS_DELIVERED)
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-[#00B884] text-white flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-xs font-black">Delivered Successfully!</h3>
                    <p class="text-[11px] text-emerald-800">
                        Delivered on {{ $order->delivered_at ? $order->delivered_at->format('d M Y, h:i A') : $order->updated_at->format('d M Y, h:i A') }}
                    </p>
                    @if($order->delivery_proof_notes)
                        <p class="text-[11px] text-emerald-950 font-bold mt-1 bg-white/70 p-2 rounded-lg border border-emerald-200">
                            Proof Note: {{ $order->delivery_proof_notes }}
                        </p>
                    @endif
                </div>
            </div>
        @else
            <div class="p-4 rounded-2xl bg-gray-50 border border-[#E8E5EF] text-[#55597D] text-xs flex items-center gap-3">
                <i data-lucide="clock" class="w-5 h-5 text-[#667085]"></i>
                <div>
                    <span class="font-bold text-[#202033]">Current Order Status: {{ $order->status_label }}</span>
                    <p class="text-[11px] text-[#667085]">The kitchen is preparing this order. Delivery actions will become available once kitchen marks it Ready.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Cash Collection Box -->
    <div class="rounded-3xl p-5 border shadow-xs {{ $order->payment && $order->payment->payment_method === 'cash_on_delivery' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-emerald-50 border-emerald-200 text-emerald-900' }}">
        <div class="flex items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-wider">Payment Method:</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $order->payment && $order->payment->payment_method === 'cash_on_delivery' ? 'bg-amber-200 text-amber-900' : 'bg-emerald-200 text-emerald-900' }}">
                        {{ $order->payment ? strtoupper(str_replace('_', ' ', $order->payment->payment_method)) : 'CASH ON DELIVERY' }}
                    </span>
                </div>

                @if($order->payment && $order->payment->payment_method === 'cash_on_delivery')
                    @if($order->order_status === \App\Models\Order::STATUS_DELIVERED)
                        <p class="text-xs font-bold text-emerald-700">
                            ✓ Cash of ₹{{ number_format($order->total_amount, 2) }} collected and recorded.
                        </p>
                    @else
                        <p class="text-xs font-extrabold text-amber-900">
                            ⚠️ Please collect exactly <span class="text-base text-amber-950 font-black font-mono">₹{{ number_format($order->total_amount, 2) }}</span> in cash from the customer before handing over the food.
                        </p>
                    @endif
                @else
                    <p class="text-xs font-bold text-emerald-800">
                        ✓ Order is PREPAID ONLINE. Do not request or collect any payment from customer.
                    </p>
                @endif
            </div>

            <div class="text-right shrink-0">
                <span class="text-[10px] uppercase font-bold text-[#667085] block">Total Bill</span>
                <span class="text-xl font-black font-mono text-[#202033]">₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Location & Customer Delivery Details -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E8E5EF] shadow-xs space-y-4">
        <h2 class="text-xs font-black text-[#202033] uppercase tracking-wider">Customer & Delivery Location</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- Customer -->
            <div class="p-4 rounded-2xl bg-gray-50 border border-[#E8E5EF] space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085] block">Recipient</span>
                <p class="text-sm font-black text-[#202033]">{{ $order->customer_name }}</p>
                <p class="font-mono text-[#55597D]">{{ $order->customer_phone }}</p>
                @if($order->customer_email)
                    <p class="text-[#667085]">{{ $order->customer_email }}</p>
                @endif
            </div>

            <!-- Address & GPS -->
            <div class="p-4 rounded-2xl bg-gray-50 border border-[#E8E5EF] space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085] block">Destination</span>
                <p class="text-[#202033] font-bold leading-relaxed">
                    {{ $order->delivery_address_text ?? 'Kolkata Delivery Area' }}
                </p>
                @if($order->delivery_distance_km)
                    <p class="text-[#FF006E] font-black">
                        Calculated Distance: {{ $order->delivery_distance_km }} km
                    </p>
                @endif
                @if(!empty($order->delivery_latitude) && !empty($order->delivery_longitude))
                    <p class="text-[10px] text-[#667085] font-mono">
                        GPS: {{ (float)$order->delivery_latitude }}, {{ (float)$order->delivery_longitude }}
                    </p>
                @endif
            </div>
        </div>

        @if($order->delivery_instructions)
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-900 block mb-1">
                    Special Delivery Instructions
                </span>
                <p class="text-amber-950 font-bold leading-relaxed">
                    {{ $order->delivery_instructions }}
                </p>
            </div>
        @endif

        @if($order->special_instructions)
            <div class="p-4 rounded-2xl bg-purple-50 border border-purple-200 text-xs">
                <span class="text-[10px] font-black uppercase tracking-wider text-purple-900 block mb-1">
                    Customer Kitchen Instructions
                </span>
                <p class="text-purple-950 font-medium leading-relaxed">
                    {{ $order->special_instructions }}
                </p>
            </div>
        @endif
    </div>

    <!-- Food Items Breakdown -->
    <div class="bg-white rounded-3xl border border-[#E8E5EF] shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#E8E5EF]">
            <h2 class="text-xs font-black text-[#202033] uppercase tracking-wider">
                Order Items Package ({{ $order->items->count() }} items)
            </h2>
        </div>

        <div class="divide-y divide-[#E8E5EF] p-5 sm:p-6 space-y-3">
            @foreach($order->items as $item)
                <div class="flex items-start justify-between gap-4 text-xs pt-2">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-gray-100 text-[#202033] font-black flex items-center justify-center text-[10px]">
                                {{ $item->quantity }}x
                            </span>
                            <span class="font-extrabold text-sm text-[#202033]">{{ $item->product_name }}</span>
                        </div>
                        @if($item->variation_name)
                            <p class="text-[#55597D] pl-7 text-[11px]">Size/Option: {{ $item->variation_name }}</p>
                        @endif
                        @if($item->addons && $item->addons->count() > 0)
                            <div class="pl-7 space-y-0.5 text-[11px] text-[#667085]">
                                @foreach($item->addons as $addon)
                                    <div>+ {{ $addon->addon_name }}</div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="font-mono font-bold text-[#202033] text-sm">
                        ₹{{ number_format($item->line_total, 2) }}
                    </div>
                </div>
            @endforeach

            <!-- Pricing Breakdown -->
            <div class="pt-4 border-t border-[#E8E5EF] space-y-1.5 text-xs text-[#55597D]">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span class="font-mono text-[#202033]">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-[#00B884] font-bold">
                        <span>Discount:</span>
                        <span class="font-mono">-₹{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span>Packaging Charge:</span>
                    <span class="font-mono text-[#202033]">₹{{ number_format($order->packaging_charge, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Delivery Charge:</span>
                    <span class="font-mono text-[#202033]">₹{{ number_format($order->delivery_charge, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Tax:</span>
                    <span class="font-mono text-[#202033]">₹{{ number_format($order->tax_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm font-black text-[#202033] pt-2 border-t border-[#E8E5EF]">
                    <span>Total Amount:</span>
                    <span class="font-mono">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Status History Audit Timeline -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E8E5EF] shadow-xs space-y-4">
        <h2 class="text-xs font-black text-[#202033] uppercase tracking-wider">Order Status Timeline</h2>

        <div class="space-y-4 relative pl-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#E8E5EF]">
            @forelse($order->statusHistories as $history)
                <div class="relative text-xs">
                    <div class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-[#FF006E]"></div>
                    <div class="font-extrabold text-[#202033] uppercase tracking-wider text-[11px]">
                        {{ ucfirst(str_replace('_', ' ', $history->to_status)) }}
                    </div>
                    <p class="text-[#55597D] mt-0.5 leading-relaxed">{{ $history->comment }}</p>
                    <span class="text-[10px] text-[#667085] font-mono">
                        {{ $history->created_at->format('d M, h:i A') }} • by {{ $history->changedBy ? $history->changedBy->name : 'System' }}
                    </span>
                </div>
            @empty
                <p class="text-xs text-[#667085]">No status events recorded yet.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
