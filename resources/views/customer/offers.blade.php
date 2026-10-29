@extends('layouts.app')

@section('title', 'Offers & Deals | FoodKing Style - My 2nd Wife')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-10">

    <!-- Header (FoodKing OffersComponent) -->
    <div class="border-b border-[#EFF0F6] pb-4">
        <h1 class="capitalize text-2xl sm:text-[26px] leading-[40px] font-bold text-[#FF006B] font-rubik">
            All Offers & Promotions
        </h1>
        <p class="text-xs text-[#6E7191] mt-1">Exclusive promotional codes and seasonal culinary savings</p>
    </div>

    <!-- 2-Column FoodKing Promotional Banner Grid (OfferComponent) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 md:gap-6">
        @forelse($offers as $offer)
            <div class="rounded-2xl p-6 sm:p-8 bg-gradient-to-r from-pink-500 via-[#FF006B] to-[#1F1F39] text-white relative overflow-hidden flex flex-col justify-between min-h-[200px] shadow-sm group">
                <div class="relative z-10 max-w-sm space-y-2">
                    <span class="text-[11px] font-black uppercase tracking-wider bg-white/20 px-2.5 py-0.5 rounded-full inline-block">
                        PROMO CODE: {{ $offer->code }}
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black font-rubik leading-tight">
                        {{ $offer->type === 'percentage' ? (int)$offer->discount_amount . '% OFF' : '₹' . (int)$offer->discount_amount . ' OFF' }}
                    </h3>
                    <p class="text-xs text-white/90">
                        Min. order of ₹{{ number_format($offer->min_order_amount, 0) }}. 
                        @if($offer->max_discount_amount)
                            Max discount ₹{{ number_format($offer->max_discount_amount, 0) }}.
                        @endif
                    </p>
                </div>

                <div class="relative z-10 pt-4 flex items-center justify-between">
                    <a href="{{ route('menu.index') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white text-[#FF006B] font-bold text-xs hover:bg-[#FFF0F6] transition-transform group-hover:scale-105 shadow-xs">
                        <span>Apply & Order</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                    <span class="text-[10px] text-white/70 font-semibold">Valid till {{ $offer->expires_at ? $offer->expires_at->format('M d, Y') : 'Ongoing' }}</span>
                </div>

                <div class="absolute right-3 bottom-2 text-8xl opacity-15 pointer-events-none">🎉</div>
            </div>
        @empty
            <div class="col-span-2 text-center py-12 text-[#6E7191] text-xs">
                No active promotional campaigns at this moment. Check back soon!
            </div>
        @endforelse
    </div>

    <!-- Discounted Signature Dishes Grid -->
    @if($discountedItems->isNotEmpty())
        <div class="pt-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl sm:text-[22px] font-bold text-[#1F1F39]">Dishes on Special Discount</h2>
                    <p class="text-xs text-[#6E7191] mt-0.5">Special pricing applied directly to menu items</p>
                </div>
                <a href="{{ route('menu.index') }}" class="text-xs font-bold text-[#FF006B] hover:underline flex items-center gap-1">
                    <span>View Menu</span>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5 sm:gap-6">
                @foreach($discountedItems as $product)
                    <x-food-card :product="$product" layout="grid" />
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
