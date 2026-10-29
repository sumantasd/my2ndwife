@extends('layouts.app')

@section('title', 'Menu | FoodKing Style Dining - My 2nd Wife')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">

    <!-- 1. Mobile Menu Header (FoodKing Screenshot 3: Back arrow, Logo, Search icon) -->
    <div class="md:hidden flex items-center justify-between py-1">
        <a href="{{ route('home') }}" 
           class="w-9 h-9 rounded-full border border-[#EFF0F6] bg-white flex items-center justify-center text-[#1F1F39] hover:text-[#FF006B] shadow-xs active:scale-95 transition-all">
            <i data-lucide="arrow-left" class="w-4 h-4 text-[#FF006B]"></i>
        </a>

        @php
            $logoUrl = asset(\App\Models\RestaurantSetting::get('restaurant_logo', 'images/defaults/logo.svg'));
            $restaurantName = \App\Models\RestaurantSetting::get('restaurant_name', 'My 2nd Wife');
        @endphp
        <img src="{{ $logoUrl }}" alt="{{ $restaurantName }}" class="h-8 w-auto">

        <a href="{{ route('search.index') }}" 
           class="w-9 h-9 rounded-full border border-[#EFF0F6] bg-white flex items-center justify-center text-[#1F1F39] hover:text-[#FF006B] shadow-xs active:scale-95 transition-all">
            <i data-lucide="search" class="w-4 h-4 text-[#1F1F39]"></i>
        </a>
    </div>

    <!-- 2. Horizontal Category Tabs with Pink Active Indicator (FoodKing Screenshot 3) -->
    <div class="flex items-center gap-4 sm:gap-6 overflow-x-auto pb-2 pt-1 no-scrollbar scroll-smooth border-b border-[#EFF0F6]">
        <!-- "All Dishes" Option -->
        <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" 
           class="flex flex-col items-center shrink-0 group min-w-[70px] select-none">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center p-2 transition-all">
                <span class="text-2xl">🍽️</span>
            </div>
            <span class="text-xs font-bold text-center truncate max-w-[80px] {{ !request('category') ? 'text-[#FF006B]' : 'text-[#1F1F39] group-hover:text-[#FF006B]' }}">
                All Dishes
            </span>
            <!-- Pink Active Underline Indicator (FoodKing Screenshot 3) -->
            <div class="h-1 rounded-full w-full mt-2 transition-all {{ !request('category') ? 'bg-[#FF006B]' : 'bg-transparent' }}"></div>
        </a>

        <!-- Category Items -->
        @foreach($categories as $cat)
            @php $isActive = request('category') === $cat->slug; @endphp
            <a href="{{ request()->fullUrlWithQuery(['category' => $cat->slug]) }}" 
               class="flex flex-col items-center shrink-0 group min-w-[75px] select-none">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center p-1 transition-all">
                    <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" 
                         class="w-10 h-10 sm:w-12 sm:h-12 object-contain group-hover:scale-105 transition-transform duration-200"
                         onerror="this.src='{{ asset('images/defaults/category-placeholder.svg') }}'">
                </div>
                <span class="text-xs font-bold text-center truncate max-w-[85px] {{ $isActive ? 'text-[#FF006B]' : 'text-[#1F1F39] group-hover:text-[#FF006B]' }}">
                    {{ $cat->name }}
                </span>
                <!-- Pink Active Underline Indicator (FoodKing Screenshot 3) -->
                <div class="h-1 rounded-full w-full mt-2 transition-all {{ $isActive ? 'bg-[#FF006B]' : 'bg-transparent' }}"></div>
            </a>
        @endforeach
    </div>

    <!-- 3. Dietary Filter Pills (FoodKing Screenshot 3: 🍗 Non-Veg / 🥬 Veg) -->
    <div class="flex items-center gap-2.5">
        <!-- Non-Veg Filter Pill -->
        <a href="{{ request('diet') === 'non-veg' ? request()->fullUrlWithQuery(['diet' => null]) : request()->fullUrlWithQuery(['diet' => 'non-veg']) }}" 
           class="flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold transition-all border shadow-xs select-none {{ request('diet') === 'non-veg' ? 'bg-[#FFF0F6] border-[#FF006B] text-[#FF006B]' : 'bg-white border-[#EFF0F6] text-[#1F1F39] hover:bg-gray-50' }}">
            <span class="text-sm">🍗</span>
            <span>Non-Veg</span>
            @if(request('diet') === 'non-veg')
                <i data-lucide="x" class="w-3.5 h-3.5 text-[#FF006B]"></i>
            @endif
        </a>

        <!-- Veg Filter Pill -->
        <a href="{{ request('diet') === 'veg' ? request()->fullUrlWithQuery(['diet' => null]) : request()->fullUrlWithQuery(['diet' => 'veg']) }}" 
           class="flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold transition-all border shadow-xs select-none {{ request('diet') === 'veg' ? 'bg-[#FFF0F6] border-[#FF006B] text-[#FF006B]' : 'bg-white border-[#EFF0F6] text-[#1F1F39] hover:bg-gray-50' }}">
            <span class="text-sm">🥬</span>
            <span>Veg</span>
            @if(request('diet') === 'veg')
                <i data-lucide="x" class="w-3.5 h-3.5 text-[#FF006B]"></i>
            @endif
        </a>
    </div>

    <!-- 4. Category Title & Layout Switcher (FoodKing Screenshot 3) -->
    <div class="flex items-center justify-between pt-1">
        @php
            $currentCatName = 'All Dishes';
            if(request('category')) {
                $cObj = $categories->firstWhere('slug', request('category'));
                if($cObj) $currentCatName = $cObj->name;
            }
        @endphp
        <!-- Category Title in Bold Pink (FoodKing Screenshot 3: "Appetizers") -->
        <h1 class="text-lg sm:text-2xl font-bold text-[#FF006B] font-rubik tracking-tight">
            {{ $currentCatName }}
        </h1>

        <!-- View Mode Switcher: List (=) vs Grid (::) -->
        <div class="flex items-center gap-1.5">
            <!-- List View Button -->
            <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" 
               title="List View"
               class="p-1.5 rounded-lg transition-colors {{ request('view') === 'list' ? 'text-[#FF006B] bg-[#FFF0F6]' : 'text-gray-400 hover:text-[#1F1F39]' }}">
                <i data-lucide="rows-3" class="w-5 h-5"></i>
            </a>

            <!-- Grid View Button -->
            <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" 
               title="Grid View"
               class="p-1.5 rounded-lg transition-colors {{ request('view', 'grid') === 'grid' ? 'text-[#FF006B] bg-[#FFF0F6]' : 'text-gray-400 hover:text-[#1F1F39]' }}">
                <i data-lucide="layout-grid" class="w-5 h-5"></i>
            </a>
        </div>
    </div>

    <!-- 5. Dishes Grid or List Presentation -->
    @if($products->count() > 0)
        @if(request('view') === 'list')
            <div class="space-y-3 sm:space-y-4">
                @foreach($products as $product)
                    <x-food-card :product="$product" layout="list" />
                @endforeach
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-5">
                @foreach($products as $product)
                    <x-food-card :product="$product" layout="grid" />
                @endforeach
            </div>
        @endif

        <!-- Pagination -->
        <div class="pt-6 border-t border-[#EFF0F6]">
            {{ $products->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-16 px-4 rounded-2xl bg-white border border-[#EFF0F6] space-y-3.5 max-w-md mx-auto shadow-xs">
            <div class="w-14 h-14 rounded-full bg-[#FFF0F6] text-[#FF006B] flex items-center justify-center mx-auto text-2xl">
                🍽️
            </div>
            <h3 class="text-base font-bold text-[#1F1F39]">No food dishes found</h3>
            <p class="text-xs text-[#6E7191]">We could not find any dishes matching your current selection. Try resetting your dietary filter or category.</p>
            <a href="{{ route('menu.index') }}" 
               class="inline-block px-5 py-2 rounded-full bg-[#FF006B] hover:bg-[#E60060] text-white text-xs font-bold transition-all shadow-xs">
                Reset Filters
            </a>
        </div>
    @endif

</div>
@endsection
