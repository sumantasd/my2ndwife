@extends('layouts.app')

@section('title', 'Shopping Cart | My 2nd Wife')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">

    <!-- Mobile Header -->
    <div class="flex items-center justify-between pb-2 border-b border-[#EFF0F6]">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[#1F1F39] font-rubik">Shopping Cart</h1>
            <p class="text-xs text-[#6E7191] mt-0.5">Review items before checkout</p>
        </div>
        <a href="{{ route('menu.index') }}" class="text-xs font-bold text-[#FF006B] hover:underline flex items-center gap-1">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            <span>Add More</span>
        </a>
    </div>

    @if(!empty($totals['items']))
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Column: Cart Items List -->
        <div class="lg:col-span-8 space-y-4">
            
            <!-- Free Delivery Progress Bar -->
            @if(!$totals['is_free_delivery'])
            <div class="p-3.5 sm:p-4 rounded-2xl bg-[#FFF0F6] border border-[#FEE2EB] space-y-2 shadow-xs">
                <div class="flex items-center justify-between text-xs font-bold">
                    <span class="text-[#1F1F39] flex items-center gap-1.5">
                        <i data-lucide="truck" class="w-4 h-4 text-[#FF006B]"></i>
                        <span>Add <span class="text-[#FF006B]">₹{{ number_format($totals['free_delivery_remaining'], 2) }}</span> more for <strong>FREE DELIVERY</strong>!</span>
                    </span>
                    <span class="text-gray-500 font-normal hidden sm:inline">Threshold: ₹{{ number_format($totals['free_delivery_threshold'], 2) }}</span>
                </div>
                @php 
                    $percent = min(100, round(($totals['subtotal'] / max(1, $totals['free_delivery_threshold'])) * 100));
                @endphp
                <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-[#FF006B] transition-all duration-500 rounded-full" style="width: {{ $percent }}%"></div>
                </div>
            </div>
            @else
            <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-xs">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                <span>Your order qualifies for <strong>FREE DELIVERY</strong>!</span>
            </div>
            @endif

            <!-- Cart Items Vertical List -->
            <div class="bg-white rounded-2xl border border-[#EFF0F6] divide-y divide-[#EFF0F6] shadow-xs overflow-hidden">
                @foreach($totals['items'] as $item)
                <div class="p-3.5 sm:p-4 flex items-start justify-between gap-3 sm:gap-4">
                    <!-- Image -->
                    <div class="w-18 h-18 sm:w-20 sm:h-20 rounded-2xl overflow-hidden bg-[#F7F7FC] shrink-0 border border-[#EFF0F6]">
                        <img src="{{ $item['product_image'] }}" alt="{{ $item['product_name'] }}" class="w-full h-full object-cover">
                    </div>

                    <!-- Details -->
                    <div class="flex-1 min-w-0 space-y-1">
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 border {{ $item['is_veg'] ? 'border-[#1AB759]' : 'border-[#FB4E4E]' }} flex items-center justify-center p-0.5 rounded-[2px] shrink-0">
                                <div class="w-1.5 h-1.5 rounded-full {{ $item['is_veg'] ? 'bg-[#1AB759]' : 'bg-[#FB4E4E]' }}"></div>
                            </div>
                            <h3 class="text-xs sm:text-sm font-bold text-[#1F1F39] truncate font-rubik">{{ $item['product_name'] }}</h3>
                        </div>

                        @if($item['variation_name'])
                            <p class="text-[11px] text-[#FF006B] font-semibold">{{ $item['variation_name'] }}</p>
                        @endif

                        @if(!empty($item['addons']))
                            <div class="text-[10px] text-[#6E7191] leading-tight">
                                +{{ implode(', ', array_map(fn($a) => $a['name'], $item['addons'])) }}
                            </div>
                        @endif

                        @if(!empty($item['instructions']))
                            <p class="text-[10px] text-amber-700 italic bg-amber-50 px-2 py-0.5 rounded-lg inline-block">
                                "{{ $item['instructions'] }}"
                            </p>
                        @endif

                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs sm:text-sm font-black text-[#1F1F39]">₹{{ number_format($item['line_subtotal'], 2) }}</span>

                            <!-- Quantity Controls & Remove -->
                            <div class="flex items-center gap-2">
                                <div class="flex items-center border border-[#EFF0F6] rounded-full px-2 py-0.5 bg-[#F7F7FC] text-xs">
                                    <form action="{{ route('cart.update') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="item_key" value="{{ $item['key'] }}">
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                        <button type="submit" class="text-[#FF006B] font-black text-sm px-1.5 active:scale-95">-</button>
                                    </form>
                                    <span class="mx-2 text-xs font-bold text-[#1F1F39]">{{ $item['quantity'] }}</span>
                                    <form action="{{ route('cart.update') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="item_key" value="{{ $item['key'] }}">
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                        <button type="submit" class="text-[#FF006B] font-black text-sm px-1.5 active:scale-95">+</button>
                                    </form>
                                </div>

                                <form action="{{ route('cart.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="item_key" value="{{ $item['key'] }}">
                                    <button type="submit" title="Remove" class="p-1 rounded-lg text-gray-300 hover:text-rose-500 transition-colors">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Right Column: Bill Details & Promo Coupon -->
        <div class="lg:col-span-4 space-y-4">
            
            <!-- Promo Coupon Box -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-[#EFF0F6] shadow-xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#1F1F39] flex items-center gap-1.5 font-rubik">
                    <i data-lucide="tag" class="w-4 h-4 text-[#FF006B]"></i>
                    <span>Apply Promo Coupon</span>
                </h3>

                @if($totals['coupon'])
                    <div class="p-3 rounded-xl bg-[#FFF0F6] border border-[#FEE2EB] flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-[#FF006B] tracking-wide">{{ $totals['coupon']->code }}</span>
                            <span class="text-[11px] text-[#1F1F39] block font-medium">Applied: You save ₹{{ number_format($totals['discount_amount'], 2) }}</span>
                        </div>
                        <form action="{{ route('cart.coupon.remove') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs text-rose-500 font-bold hover:underline">Remove</button>
                        </form>
                    </div>
                @else
                    <form action="{{ route('cart.coupon.apply') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="coupon_code" placeholder="Coupon code (e.g. TASTE20)" required
                               class="flex-1 uppercase font-mono text-xs px-3.5 py-2.5 rounded-xl border border-[#EFF0F6] focus:border-[#FF006B] focus:outline-none">
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#FF006B] hover:bg-[#E60060] text-white text-xs font-bold transition-colors shadow-xs">
                            Apply
                        </button>
                    </form>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span class="text-[10px] text-[#6E7191]">Try:</span>
                        <button type="button" onclick="document.querySelector('input[name=coupon_code]').value='TASTE20'" class="text-[10px] font-mono font-bold bg-[#F7F7FC] text-[#FF006B] px-2 py-0.5 rounded border border-[#EFF0F6] hover:bg-pink-50">TASTE20</button>
                        <button type="button" onclick="document.querySelector('input[name=coupon_code]').value='WELCOME50'" class="text-[10px] font-mono font-bold bg-[#F7F7FC] text-[#FF006B] px-2 py-0.5 rounded border border-[#EFF0F6] hover:bg-pink-50">WELCOME50</button>
                    </div>
                @endif
            </div>

            <!-- Bill Summary Card -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#EFF0F6] shadow-xs space-y-3.5">
                <h3 class="text-xs sm:text-sm font-bold text-[#1F1F39] font-rubik">Bill Details</h3>

                <div class="space-y-2 text-xs text-[#6E7191]">
                    <div class="flex justify-between">
                        <span>Items Subtotal</span>
                        <span class="font-bold text-[#1F1F39]">₹{{ number_format($totals['subtotal'], 2) }}</span>
                    </div>

                    @if($totals['discount_amount'] > 0)
                    <div class="flex justify-between text-[#1AB759] font-bold">
                        <span>Coupon Discount ({{ $totals['coupon_code'] }})</span>
                        <span>-₹{{ number_format($totals['discount_amount'], 2) }}</span>
                    </div>
                    @endif

                    <div class="flex justify-between">
                        <span>Delivery Fee</span>
                        @if($totals['is_free_delivery'])
                            <span class="font-bold text-[#1AB759] uppercase">FREE</span>
                        @else
                            <span class="font-bold text-[#1F1F39]">₹{{ number_format($totals['delivery_charge'], 2) }}</span>
                        @endif
                    </div>

                    <div class="flex justify-between">
                        <span>Packaging Charge</span>
                        <span class="font-bold text-[#1F1F39]">₹{{ number_format($totals['packaging_charge'], 2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>GST Taxes ({{ $totals['tax_rate'] }}%)</span>
                        <span class="font-bold text-[#1F1F39]">₹{{ number_format($totals['tax_amount'], 2) }}</span>
                    </div>

                    <div class="pt-3 border-t border-[#EFF0F6] flex justify-between items-baseline">
                        <span class="text-sm font-bold text-[#1F1F39] font-rubik">To Pay</span>
                        <span class="text-xl sm:text-2xl font-black text-[#FF006B] font-rubik">₹{{ number_format($totals['total_amount'], 2) }}</span>
                    </div>
                </div>

                <!-- Minimum Order Warning if applicable -->
                @if(!$totals['is_min_order_met'])
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                    <p class="font-bold">Minimum Order Required</p>
                    <p class="mt-0.5 text-[11px]">Add ₹{{ number_format($totals['min_order_remaining'], 2) }} more to satisfy minimum order of ₹{{ number_format($totals['min_order_amount'], 2) }}.</p>
                </div>
                @endif

                <!-- Proceed to Checkout Button -->
                @if($totals['is_min_order_met'])
                    <a href="{{ route('checkout.index') }}" 
                       class="w-full py-3.5 rounded-full bg-[#FF006B] hover:bg-[#E60060] text-white font-extrabold text-sm flex items-center justify-center gap-2 shadow-lg shadow-pink-500/25 active:scale-[0.99] transition-all">
                        <span>Proceed to Checkout</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                @else
                    <button disabled class="w-full py-3.5 rounded-full bg-gray-200 text-gray-400 font-bold text-xs cursor-not-allowed">
                        Minimum Order ₹{{ number_format($totals['min_order_amount'], 0) }} Required
                    </button>
                @endif
            </div>
        </div>
    </div>
    @else
    <!-- Empty Cart State -->
    <div class="text-center py-16 px-4 rounded-2xl bg-white border border-[#EFF0F6] max-w-md mx-auto space-y-3.5 shadow-xs">
        <div class="w-16 h-16 rounded-full bg-[#FFF0F6] text-[#FF006B] flex items-center justify-center mx-auto text-3xl">
            🛍️
        </div>
        <h2 class="text-lg font-bold text-[#1F1F39] font-rubik">Your cart is empty</h2>
        <p class="text-xs text-[#6E7191] leading-relaxed max-w-sm mx-auto">
            Good food is waiting for you! Explore our authentic dum biryanis, gourmet burgers, and sourdough pizzas.
        </p>
        <div class="pt-2">
            <a href="{{ route('menu.index') }}" 
               class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#FF006B] hover:bg-[#E60060] text-white font-bold text-xs shadow-xs transition-all">
                <span>Browse Menu</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
    @endif

</div>
@endsection
