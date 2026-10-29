@props(['product', 'layout' => 'grid'])

@php
    $modalData = [
        'id' => $product->id,
        'name' => $product->name,
        'description' => $product->description,
        'current_price' => $product->current_price,
        'main_image_url' => $product->main_image_url,
        'is_veg' => $product->is_veg,
        'variations' => $product->activeVariations,
        'addon_groups' => $product->addonGroups->map(fn($g) => [
            'id' => $g->id,
            'name' => $g->name,
            'addons' => $g->activeAddons
        ])
    ];
@endphp

@if($layout === 'list')
    <!-- FoodKing List Card Style (FoodKing Reference Screenshots 2 & 3) -->
    <div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-[#EFF0F6] p-3 sm:p-3.5 flex items-center gap-3.5 hover:shadow-md transition-all duration-200 group shadow-xs']) }}>
        <!-- Thumbnail -->
        <div class="relative w-22 h-22 sm:w-24 sm:h-24 rounded-2xl overflow-hidden shrink-0 bg-[#FFF0F6] border border-[#EFF0F6]">
            <a href="{{ route('food.show', $product->slug) }}">
                <img src="{{ $product->main_image_url }}" alt="{{ $product->name }}" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                     onerror="this.src='{{ asset('images/defaults/food-placeholder.jpg') }}'">
            </a>
            <!-- Veg / Non-Veg dot badge -->
            <div class="absolute top-1.5 left-1.5 bg-white/95 backdrop-blur-xs p-0.5 rounded-sm shadow-xs">
                <div class="w-3 h-3 border {{ $product->is_veg ? 'border-[#1AB759]' : 'border-[#FB4E4E]' }} flex items-center justify-center p-0.5">
                    <div class="w-1.5 h-1.5 rounded-full {{ $product->is_veg ? 'bg-[#1AB759]' : 'bg-[#FB4E4E]' }}"></div>
                </div>
            </div>
        </div>

        <!-- Info & Actions -->
        <div class="flex-1 min-w-0 flex flex-col justify-between self-stretch py-0.5">
            <div>
                <!-- Title Row with (i) Info Button (FoodKing Reference) -->
                <div class="flex items-center justify-between gap-1.5">
                    <a href="{{ route('food.show', $product->slug) }}" class="min-w-0">
                        <h3 class="text-xs sm:text-sm font-bold text-[#1F1F39] group-hover:text-[#FF006B] transition-colors truncate">
                            {{ $product->name }}
                        </h3>
                    </a>
                    <button type="button" @click="openQuickView({{ json_encode($modalData) }})" 
                            title="Product details"
                            class="w-4 h-4 rounded-full bg-gray-100 text-gray-400 hover:text-[#FF006B] hover:bg-[#FFF0F6] flex items-center justify-center text-[10px] font-bold shrink-0 transition-colors">
                        i
                    </button>
                </div>

                <p class="text-[11px] text-[#6E7191] line-clamp-2 mt-0.5 leading-tight">
                    {{ $product->description }}
                </p>
            </div>

            <!-- Pricing & Add Button Row -->
            <div class="flex items-center justify-between pt-1.5 mt-auto">
                <div class="flex items-baseline gap-1">
                    <span class="text-xs sm:text-sm font-black text-[#1F1F39]">₹{{ number_format($product->current_price, 2) }}</span>
                    @if($product->has_discount)
                        <span class="text-[10px] text-[#A0A3BD] line-through">₹{{ number_format($product->regular_price, 2) }}</span>
                    @endif
                </div>

                <!-- Pink Add Button -->
                @if($product->is_available)
                    <button type="button" 
                            @click="openQuickView({{ json_encode($modalData) }})"
                            class="px-3 py-1 rounded-full bg-[#FFF0F6] text-[#FF006B] hover:bg-[#FF006B] hover:text-white font-bold text-xs border border-[#FEE2EB] transition-all flex items-center gap-1 shadow-xs active:scale-95">
                        <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                        <span>Add</span>
                    </button>
                @else
                    <span class="text-[9px] font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                        Sold Out
                    </span>
                @endif
            </div>
        </div>
    </div>
@else
    <!-- FoodKing Grid Card Style (FoodKing Reference Screenshots 1 & 4) -->
    <div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-[#EFF0F6] overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col justify-between group relative shadow-xs']) }}>
        <!-- Image Area -->
        <div class="relative h-32 sm:h-44 overflow-hidden bg-[#FFF0F6]">
            <a href="{{ route('food.show', $product->slug) }}">
                <img src="{{ $product->main_image_url }}" alt="{{ $product->name }}" 
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                     onerror="this.src='{{ asset('images/defaults/food-placeholder.jpg') }}'">
            </a>

            <!-- Veg / Non-Veg dot badge -->
            <div class="absolute top-2 left-2 bg-white/95 backdrop-blur-xs p-0.5 rounded-sm shadow-xs border border-gray-100">
                <div class="w-3 h-3 border {{ $product->is_veg ? 'border-[#1AB759]' : 'border-[#FB4E4E]' }} flex items-center justify-center p-0.5">
                    <div class="w-1.5 h-1.5 rounded-full {{ $product->is_veg ? 'bg-[#1AB759]' : 'bg-[#FB4E4E]' }}"></div>
                </div>
            </div>

            <!-- Discount or Best Seller Tag -->
            @if($product->has_discount)
                <div class="absolute top-2 right-2 bg-[#FF006B] text-white text-[9px] font-black px-1.5 py-0.5 rounded-full shadow-xs">
                    {{ $product->discount_percent }}% OFF
                </div>
            @elseif($product->is_best_seller)
                <div class="absolute top-2 right-2 bg-[#FF8A26] text-white text-[9px] font-extrabold px-1.5 py-0.5 rounded-full shadow-xs">
                    POPULAR
                </div>
            @endif
        </div>

        <!-- Content Area -->
        <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
            <div>
                <!-- Title Row with (i) Info Button (FoodKing Reference) -->
                <div class="flex items-center justify-between gap-1.5">
                    <a href="{{ route('food.show', $product->slug) }}" class="min-w-0">
                        <h3 class="text-xs sm:text-sm font-bold text-[#1F1F39] group-hover:text-[#FF006B] transition-colors truncate">
                            {{ $product->name }}
                        </h3>
                    </a>
                    <button type="button" @click="openQuickView({{ json_encode($modalData) }})" 
                            title="Product details"
                            class="w-4 h-4 rounded-full bg-gray-100 text-gray-400 hover:text-[#FF006B] hover:bg-[#FFF0F6] flex items-center justify-center text-[10px] font-bold shrink-0 transition-colors">
                        i
                    </button>
                </div>

                <p class="text-[11px] text-[#6E7191] line-clamp-2 mt-1 leading-snug">
                    {{ $product->description }}
                </p>
            </div>

            <!-- Footer: Price & Pink Add Button (FoodKing Reference: $2.50   🛍️ Add) -->
            <div class="mt-3 pt-2 border-t border-[#EFF0F6] flex items-center justify-between">
                <div>
                    <span class="text-xs sm:text-sm font-black text-[#1F1F39]">₹{{ number_format($product->current_price, 2) }}</span>
                    @if($product->has_discount)
                        <span class="text-[10px] text-[#A0A3BD] line-through block -mt-0.5">₹{{ number_format($product->regular_price, 2) }}</span>
                    @endif
                </div>

                <!-- Pink Add Button -->
                @if($product->is_available)
                    <button type="button" 
                            @click="openQuickView({{ json_encode($modalData) }})"
                            class="px-2.5 py-1 sm:px-3 sm:py-1 rounded-full bg-[#FFF0F6] text-[#FF006B] hover:bg-[#FF006B] hover:text-white font-bold text-xs border border-[#FEE2EB] transition-all flex items-center gap-1 shadow-xs active:scale-95">
                        <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                        <span>Add</span>
                    </button>
                @else
                    <span class="text-[9px] font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                        Sold Out
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif
