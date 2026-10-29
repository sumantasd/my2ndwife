@extends('layouts.app')

@section('title', 'Offer Details | My 2nd Wife')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="border-b border-[#EFF0F6] pb-4">
        <a href="{{ route('offers.index') }}" class="text-xs font-bold text-[#FF006B] hover:underline flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Back to all offers</span>
        </a>
        <h1 class="text-2xl sm:text-[26px] font-bold text-[#FF006B] font-rubik">
            {{ $coupon ? 'Offer Code: ' . $coupon->code : 'Promotional Offer' }}
        </h1>
        <p class="text-xs text-[#6E7191] mt-1">Enjoy discounted prices on our hand-crafted menu dishes</p>
    </div>

    <!-- Dish Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5 sm:gap-6">
        @foreach($items as $product)
            <x-food-card :product="$product" layout="grid" />
        @endforeach
    </div>

    <div class="pt-6 border-t border-[#EFF0F6]">
        {{ $items->links() }}
    </div>
</div>
@endsection
