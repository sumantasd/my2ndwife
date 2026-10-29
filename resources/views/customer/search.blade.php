@extends('layouts.app')

@section('title', 'Search Dishes | My 2nd Wife')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-5"
     x-data="{
         searchVal: '{{ addslashes($query ?? '') }}',
         viewMode: '{{ request('view', 'grid') }}'
     }">

    <!-- 1. Mobile & Desktop Search Header (FoodKing Screenshot 4) -->
    <div class="flex items-center gap-3 py-1">
        <a href="{{ route('home') }}" 
           class="md:hidden w-9 h-9 rounded-full border border-[#EFF0F6] bg-white flex items-center justify-center text-[#FF006B] shadow-xs active:scale-95 transition-all shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <h1 class="text-base sm:text-2xl font-bold text-[#1F1F39] font-rubik">
            <span class="md:hidden">Search</span>
            <span class="hidden md:inline">Search Food Dishes</span>
            <span class="sr-only">Search Food Dishes</span>
        </h1>
    </div>

    <!-- 2. Large Rounded Search Bar with Red Clear (x) Button (FoodKing Screenshot 4) -->
    <form action="{{ route('search.index') }}" method="GET" class="relative max-w-2xl" id="searchPageForm">
        <div class="relative w-full">
            <input type="text" name="q" x-model="searchVal" autofocus
                   placeholder="Search"
                   class="w-full bg-[#F4F5F7] text-[#1F1F39] text-xs sm:text-sm pl-11 pr-10 py-3 rounded-2xl border border-transparent focus:outline-none focus:border-[#FF006B] focus:bg-white transition-all placeholder:text-gray-400">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-4 top-3.5"></i>
            
            <!-- Red Circular Clear (x) Button (FoodKing Screenshot 4) -->
            <button type="button" 
                    x-show="searchVal.length > 0" 
                    @click="searchVal = ''; window.location.href = '{{ route('search.index') }}'"
                    class="w-5 h-5 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center absolute right-3.5 top-3.5 transition-colors">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    </form>

    <!-- Popular Search Pills (When idle or for quick suggestions) -->
    @if(!$query)
        <div class="pt-2 space-y-2">
            <span class="text-xs font-bold text-[#6E7191] block">Popular Searches:</span>
            <div class="flex flex-wrap gap-2">
                @foreach($popularSearches as $tag)
                    <a href="{{ route('search.index', ['q' => $tag]) }}" 
                       class="px-3.5 py-1.5 rounded-full bg-white border border-[#EFF0F6] text-[#1F1F39] text-xs font-medium hover:border-[#FF006B] hover:text-[#FF006B] shadow-xs transition-colors">
                        {{ $tag }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 3. Results Header & Count (FoodKing Screenshot 4: "55 Items Available" + Layout Toggle) -->
    @if($query)
        <div class="flex items-center justify-between pt-2 border-t border-[#EFF0F6]">
            <!-- Items Available Count in Pink (FoodKing Screenshot 4) -->
            <h2 class="text-xs sm:text-sm font-bold text-[#FF006B]">
                {{ $products->total() }} Items Available
            </h2>

            <!-- Layout Toggle Icons (= and ::) -->
            <div class="flex items-center gap-1.5">
                <button type="button" @click="viewMode = 'list'"
                        :class="viewMode === 'list' ? 'text-[#FF006B] bg-[#FFF0F6]' : 'text-gray-400 hover:text-[#1F1F39]'"
                        class="p-1.5 rounded-lg transition-colors">
                    <i data-lucide="rows-3" class="w-5 h-5"></i>
                </button>
                <button type="button" @click="viewMode = 'grid'"
                        :class="viewMode === 'grid' ? 'text-[#FF006B] bg-[#FFF0F6]' : 'text-gray-400 hover:text-[#1F1F39]'"
                        class="p-1.5 rounded-lg transition-colors">
                    <i data-lucide="layout-grid" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- 4. Products Results Presentation -->
        @if($products->count() > 0)
            <!-- List View Mode -->
            <div x-show="viewMode === 'list'" {{ request('view', 'grid') !== 'list' ? 'x-cloak style=display:none;' : '' }} class="space-y-3 sm:space-y-4">
                @foreach($products as $product)
                    <x-food-card :product="$product" layout="list" />
                @endforeach
            </div>

            <!-- Grid View Mode (FoodKing Screenshot 4: 2-column grid) -->
            <div x-show="viewMode === 'grid'" {{ request('view', 'grid') !== 'grid' ? 'x-cloak style=display:none;' : '' }} class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-5">
                @foreach($products as $product)
                    <x-food-card :product="$product" layout="grid" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-6 border-t border-[#EFF0F6]">
                {{ $products->links() }}
            </div>
        @else
            <!-- Empty Results State -->
            <div class="text-center py-16 px-4 rounded-2xl bg-white border border-[#EFF0F6] space-y-3.5 max-w-md mx-auto shadow-xs">
                <div class="w-14 h-14 rounded-full bg-[#FFF0F6] text-[#FF006B] flex items-center justify-center mx-auto text-2xl">
                    🔍
                </div>
                <h3 class="text-base font-bold text-[#1F1F39]">No dishes found</h3>
                <p class="text-xs text-[#6E7191]">We couldn't find any dishes matching "{{ $query }}". Check your spelling or browse our full menu.</p>
                <a href="{{ route('menu.index') }}" 
                   class="inline-block px-5 py-2 rounded-full bg-[#FF006B] hover:bg-[#E60060] text-white text-xs font-bold transition-all shadow-xs">
                    Browse Full Menu
                </a>
            </div>
        @endif
    @endif

</div>
@endsection
