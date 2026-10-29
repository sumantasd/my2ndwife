@extends('layouts.app')

@section('title', $product->name . ' | My 2nd Wife')

@section('content')
<script>
    function productCustomizerData() {
        return {
            basePrice: {{ (float) ($product->activeVariations->isNotEmpty() ? ($product->activeVariations->firstWhere('is_default', true)?->price ?? $product->activeVariations->first()->price) : $product->current_price) }},
            selectedVariation: {{ $product->activeVariations->isNotEmpty() ? ($product->activeVariations->firstWhere('is_default', true)?->id ?? $product->activeVariations->first()->id) : 'null' }},
            selectedAddons: {},
            quantity: 1,
            adding: false,

            updateVariationPrice(price) {
                this.basePrice = parseFloat(price) || 0;
            },

            toggleAddon(id, price) {
                if (this.selectedAddons[id] !== undefined) {
                    delete this.selectedAddons[id];
                } else {
                    this.selectedAddons[id] = parseFloat(price) || 0;
                }
            },

            calculateTotal() {
                let addonsTotal = 0;
                for (const key in this.selectedAddons) {
                    addonsTotal += parseFloat(this.selectedAddons[key]) || 0;
                }
                return ((this.basePrice + addonsTotal) * this.quantity).toFixed(2);
            },

            async submitForm(event) {
                if (this.adding) return;
                this.adding = true;
                const form = event.target;
                const formData = new FormData(form);
                const productName = @json($product->name);

                try {
                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const data = await res.json();
                    this.adding = false;

                    if (data && data.success) {
                        const name = data.product_name || productName;
                        if (window.showToast) {
                            window.showToast({
                                type: 'success',
                                title: 'Item added to cart',
                                message: name ? `${name} added to your cart.` : (data.message || 'Item added to your cart!'),
                                productName: name
                            });
                        }
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                    } else {
                        if (window.showToast) {
                            window.showToast({
                                type: 'error',
                                title: 'Unable to Add Item',
                                message: data?.message || 'Could not add this item to cart.'
                            });
                        }
                    }
                } catch (err) {
                    this.adding = false;
                    form.submit();
                }
            }
        };
    }
</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12"
     x-data="productCustomizerData()">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-[#F50070]">Home</a>
        <span>/</span>
        <a href="{{ route('menu.index') }}" class="hover:text-[#F50070]">Menu</a>
        <span>/</span>
        <a href="{{ route('menu.index', ['category' => $product->category->slug]) }}" class="hover:text-[#F50070]">{{ $product->category->name }}</a>
        <span>/</span>
        <span class="text-[#25232A] font-bold">{{ $product->name }}</span>
    </nav>

    <!-- Product Showcase & Customizer -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        
        <!-- Left Column: Image & Media -->
        <div class="lg:col-span-6 space-y-4">
            <div class="relative rounded-3xl overflow-hidden bg-[#FFF3F8] border border-[#F0E6EC] shadow-md aspect-4/3">
                <img src="{{ $product->main_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                
                <!-- Veg / Non-Veg Badge -->
                <div class="absolute top-4 left-4 bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-full border border-gray-100 flex items-center gap-2 shadow-sm">
                    <div class="w-4 h-4 border {{ $product->is_veg ? 'border-emerald-600' : 'border-rose-600' }} flex items-center justify-center p-0.5">
                        <div class="w-2.5 h-2.5 rounded-full {{ $product->is_veg ? 'bg-emerald-600' : 'bg-rose-600' }}"></div>
                    </div>
                    <span class="text-xs font-black tracking-wide uppercase {{ $product->is_veg ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $product->is_veg ? 'Pure Veg' : 'Non-Veg' }}
                    </span>
                </div>

                @if($product->has_discount)
                <div class="absolute top-4 right-4 bg-[#F50070] text-white text-xs font-black px-3 py-1 rounded-full shadow-lg">
                    {{ $product->discount_percent }}% OFF
                </div>
                @endif
            </div>

            <!-- Thumbnail Gallery if multiple images -->
            @if($product->images->count() > 0)
            <div class="grid grid-cols-4 gap-3">
                <div class="rounded-2xl border-2 border-[#F50070] overflow-hidden aspect-square bg-[#FFF3F8]">
                    <img src="{{ $product->main_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                </div>
                @foreach($product->images as $img)
                <div class="rounded-2xl border border-[#F0E6EC] overflow-hidden aspect-square bg-gray-50 hover:border-[#F50070] transition-colors">
                    <img src="{{ $img->url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                </div>
                @endforeach
            </div>
            @endif

            <!-- Feature Pills -->
            <div class="grid grid-cols-3 gap-3 pt-2">
                <div class="p-3 rounded-2xl bg-[#FFF3F8] border border-[#F0E6EC] text-center">
                    <i data-lucide="clock" class="w-4 h-4 text-[#F50070] mx-auto mb-1"></i>
                    <span class="text-[11px] font-bold text-[#25232A] block">{{ $product->prep_time_minutes }} mins</span>
                    <span class="text-[10px] text-gray-500">Cook Time</span>
                </div>
                <div class="p-3 rounded-2xl bg-[#FFF3F8] border border-[#F0E6EC] text-center">
                    <i data-lucide="package" class="w-4 h-4 text-[#F50070] mx-auto mb-1"></i>
                    <span class="text-[11px] font-bold text-[#25232A] block">Sealed Box</span>
                    <span class="text-[10px] text-gray-500">Packaging</span>
                </div>
                <div class="p-3 rounded-2xl bg-[#FFF3F8] border border-[#F0E6EC] text-center">
                    <i data-lucide="award" class="w-4 h-4 text-[#F50070] mx-auto mb-1"></i>
                    <span class="text-[11px] font-bold text-[#25232A] block">Chef Special</span>
                    <span class="text-[10px] text-gray-500">Signature</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Product Customizer Form -->
        <div class="lg:col-span-6 space-y-6 bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
            <div>
                <a href="{{ route('menu.index', ['category' => $product->category->slug]) }}" 
                   class="text-xs font-bold text-[#F50070] uppercase tracking-wider hover:underline">
                    {{ $product->category->name }}
                </a>
                <h1 class="text-2xl sm:text-3xl font-black text-[#25232A] mt-1">{{ $product->name }}</h1>
                <p class="text-xs text-gray-400 font-mono mt-0.5">SKU: {{ $product->sku }}</p>
                
                <p class="text-sm text-[#66636D] mt-3 leading-relaxed">
                    {{ $product->description }}
                </p>
            </div>

            <!-- Price Display -->
            <div class="flex items-baseline gap-3 pt-2 pb-4 border-b border-[#F0E6EC]">
                <span class="text-3xl font-black text-[#25232A]">₹<span x-text="calculateTotal()">{{ number_format($product->activeVariations->isNotEmpty() ? ($product->activeVariations->firstWhere('is_default', true)?->price ?? $product->activeVariations->first()->price) : $product->current_price, 2) }}</span></span>
                @if($product->has_discount)
                    <span class="text-sm text-gray-400 line-through">₹{{ number_format($product->regular_price, 2) }}</span>
                    <span class="text-xs font-extrabold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        Save {{ $product->discount_percent }}%
                    </span>
                @endif
            </div>

            <!-- Customization Form -->
            <form action="{{ route('cart.add') }}" method="POST" @submit.prevent="submitForm($event)" class="space-y-6">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <!-- 1. Variations / Portion Sizes -->
                @if($product->activeVariations->count() > 0)
                <div class="space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#25232A] flex items-center justify-between">
                        <span>Choose Portion Size:</span>
                        <span class="text-[11px] text-[#F50070] lowercase font-semibold">required</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach($product->activeVariations as $var)
                        <label class="flex items-center justify-between p-3.5 rounded-2xl border cursor-pointer transition-all text-xs"
                                :class="selectedVariation == {{ $var->id }} ? 'border-[#F50070] bg-[#FFF3F8] text-[#25232A] font-bold shadow-sm' : 'border-gray-200 text-gray-700 hover:bg-gray-50'">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" name="variation_id" value="{{ $var->id }}" 
                                       x-model="selectedVariation"
                                       @change="updateVariationPrice({{ (float) $var->price }})"
                                       class="text-[#F50070] focus:ring-[#F50070]">
                                <span>{{ $var->name }}</span>
                            </div>
                            <span class="text-[#F50070] font-black">₹{{ number_format($var->price, 2) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- 2. Add-ons & Extras -->
                @if($product->addonGroups->count() > 0)
                <div class="space-y-4 pt-4 border-t border-[#F0E6EC]">
                    @foreach($product->addonGroups as $group)
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[#25232A]">{{ $group->name }}</h3>
                            <span class="text-[11px] text-gray-400">Optional</span>
                        </div>
                        <div class="grid grid-cols-1 gap-2">
                            @foreach($group->activeAddons as $addon)
                            <label class="flex items-center justify-between p-2.5 rounded-xl border border-gray-100 hover:bg-[#FFF3F8] cursor-pointer text-xs transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" name="addons[]" value="{{ $addon->id }}" 
                                           @change="toggleAddon({{ $addon->id }}, {{ (float) $addon->price }})"
                                           class="rounded text-[#F50070] focus:ring-[#F50070]">
                                    <span>{{ $addon->name }}</span>
                                </div>
                                <span class="text-gray-600 font-bold">+₹{{ number_format($addon->price, 2) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- 3. Special Instructions -->
                <div class="pt-4 border-t border-[#F0E6EC]">
                    <label class="block text-xs font-bold text-[#25232A] mb-1.5">Special Cooking Instructions:</label>
                    <textarea name="instructions" rows="2" placeholder="E.g., extra spicy, no green chilies, separate dip containers..."
                              class="w-full text-xs p-3 rounded-2xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none"></textarea>
                </div>

                <!-- 4. Quantity and Add to Cart Button -->
                <div class="pt-4 flex items-center gap-4">
                    <!-- Quantity Stepper -->
                    <div class="flex items-center border border-[#F0E6EC] rounded-full px-3 py-2 bg-[#FFF3F8]">
                        <button type="button" @click="if(quantity > 1) quantity--" class="text-[#F50070] font-black text-lg px-2 hover:opacity-75 cursor-pointer">-</button>
                        <span class="mx-3 text-sm font-extrabold text-[#25232A]" x-text="quantity">1</span>
                        <button type="button" @click="if(quantity < 20) quantity++" class="text-[#F50070] font-black text-lg px-2 hover:opacity-75 cursor-pointer">+</button>
                        <input type="hidden" name="quantity" :value="quantity">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" :disabled="adding"
                            class="flex-1 py-4 px-6 rounded-full bg-[#F50070] hover:bg-[#D80063] text-white font-extrabold text-sm sm:text-base flex items-center justify-between shadow-lg shadow-pink-500/25 transition-all transform hover:-translate-y-0.5 disabled:opacity-60 cursor-pointer">
                        <span class="flex items-center gap-2">
                            <span x-show="adding" x-cloak class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full" style="display: none;"></span>
                            <i x-show="!adding" data-lucide="shopping-bag" class="w-5 h-5"></i>
                            <span x-text="adding ? 'Adding...' : 'Add to Cart'">Add to Cart</span>
                        </span>
                        <span>₹<span x-text="calculateTotal()">{{ number_format($product->activeVariations->isNotEmpty() ? ($product->activeVariations->firstWhere('is_default', true)?->price ?? $product->activeVariations->first()->price) : $product->current_price, 2) }}</span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Related Dishes / Recommendations -->
    @if($relatedProducts->count() > 0)
    <div class="pt-12 border-t border-[#F0E6EC]">
        <div class="flex items-center justify-between mb-6">
            <div>
                <span class="text-xs font-extrabold text-[#F50070] uppercase tracking-widest">Recommended Pairing</span>
                <h2 class="text-2xl font-extrabold text-[#25232A] mt-1">You Might Also Crave</h2>
            </div>
            <a href="{{ route('menu.index', ['category' => $product->category->slug]) }}" class="text-xs font-bold text-[#F50070] hover:underline">
                View all in {{ $product->category->name }}
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $related)
                <x-food-card :product="$related" />
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
