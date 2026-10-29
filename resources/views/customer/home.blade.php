@extends('layouts.app')

@section('title', 'My 2nd Wife | Premium Food Delivery in Kolkata')

@section('content')
<div class="space-y-8 sm:space-y-12 pb-4 sm:pb-6">

    <!-- 1. Homepage Promotional Banner (Responsive 20:7 Ratio on Desktop & Mobile) -->
    @php
        $heroBannersList = isset($heroBanners) ? $heroBanners : $banners;
    @endphp
    @if($heroBannersList->count() > 0)
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-3 sm:pt-6" aria-label="Promotional Banners">
        <div class="relative w-full overflow-hidden rounded-2xl sm:rounded-3xl shadow-xs border border-gray-100/70 bg-white group"
             x-data="{
                activeSlide: 0,
                slidesCount: {{ $heroBannersList->count() }},
                isPaused: false,
                timer: null,
                init() {
                    if (this.slidesCount > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        this.startTimer();
                    }
                },
                startTimer() {
                    this.timer = setInterval(() => {
                        if (!this.isPaused) {
                            this.next();
                        }
                    }, 5500);
                },
                next() {
                    this.activeSlide = (this.activeSlide + 1) % this.slidesCount;
                },
                prev() {
                    this.activeSlide = (this.activeSlide - 1 + this.slidesCount) % this.slidesCount;
                }
             }"
             @mouseenter="isPaused = true"
             @mouseleave="isPaused = false"
             @focusin="isPaused = true"
             @focusout="isPaused = false">

            <!-- 20:7 Aspect Ratio Banner Container -->
            <div class="homepage-banner relative w-full bg-white">
                @foreach($heroBannersList as $index => $banner)
                <div x-show="activeSlide === {{ $index }}"
                     x-transition:enter="transition-opacity ease-in-out duration-500"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-in-out duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute inset-0 w-full h-full flex items-center justify-center bg-white"
                     @if($heroBannersList->count() > 1 && $index > 0) x-cloak @endif>
                    
                    @if($banner->button_url)
                    <a href="{{ $banner->button_url }}" 
                       class="block w-full h-full focus:outline-hidden focus:ring-2 focus:ring-[#F50070]"
                       title="{{ $banner->title ?? $banner->name }}">
                        <img src="{{ $banner->image_url }}" 
                             alt="{{ $banner->alt_text ?: ($banner->name ?: ($banner->title ?: 'My 2nd Wife Food Delivery promotional banner')) }}"
                             class="block w-full h-full object-contain object-center"
                             onerror="this.src='{{ asset('images/defaults/banner-placeholder.jpg') }}'"
                             loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                    </a>
                    @else
                    <img src="{{ $banner->image_url }}" 
                         alt="{{ $banner->alt_text ?: ($banner->name ?: ($banner->title ?: 'My 2nd Wife Food Delivery promotional banner')) }}"
                         class="block w-full h-full object-contain object-center"
                         onerror="this.src='{{ asset('images/defaults/banner-placeholder.jpg') }}'"
                         loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Slide Indicators (High-contrast, only when multiple banners) -->
            @if($heroBannersList->count() > 1)
            <div class="absolute bottom-2 sm:bottom-4 left-0 right-0 flex justify-center items-center z-20 pointer-events-auto">
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-black/40 backdrop-blur-md border border-white/20 shadow-sm">
                    @foreach($heroBannersList as $index => $banner)
                    <button type="button" @click="activeSlide = {{ $index }}" 
                            aria-label="Go to banner {{ $index + 1 }}"
                            class="h-1.5 sm:h-2 rounded-full transition-all cursor-pointer"
                            :class="activeSlide === {{ $index }} ? 'w-5 sm:w-7 bg-white' : 'w-1.5 sm:w-2 bg-white/40 hover:bg-white/80'"></button>
                    @endforeach
                </div>
            </div>

            <!-- Navigation Chevrons (Desktop on hover) -->
            <button type="button" @click="prev()" 
                    aria-label="Previous banner"
                    class="hidden sm:flex absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/40 hover:bg-black/70 text-white backdrop-blur-md items-center justify-center transition-all opacity-0 group-hover:opacity-100 z-20 cursor-pointer shadow-md">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </button>
            <button type="button" @click="next()" 
                    aria-label="Next banner"
                    class="hidden sm:flex absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/40 hover:bg-black/70 text-white backdrop-blur-md items-center justify-center transition-all opacity-0 group-hover:opacity-100 z-20 cursor-pointer shadow-md">
                <i data-lucide="chevron-right" class="w-5 h-5"></i>
            </button>
            @endif
        </div>
    </section>
    @endif

    <!-- 2. "Our Menu" Category Section (7 Categories Fit on Desktop Row, Carousel for More) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 sm:pt-4"
             x-data="{
                 scroll(direction) {
                     const el = this.$refs.catTrack;
                     if (!el) return;
                     const scrollAmount = el.clientWidth * 0.75;
                     el.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
                 },
                 canScrollLeft: false,
                 canScrollRight: false,
                 checkScroll() {
                     const el = this.$refs.catTrack;
                     if (!el) return;
                     this.canScrollLeft = el.scrollLeft > 10;
                     this.canScrollRight = el.scrollLeft < (el.scrollWidth - el.clientWidth - 10);
                 }
             }"
             x-init="$nextTick(() => checkScroll()); window.addEventListener('resize', () => checkScroll())">
        <!-- Section Header: "Our Menu" with Chevrons and pink "View All" -->
        <div class="flex items-center justify-between mb-3.5 sm:mb-4">
            <div>
                <h2 class="text-lg sm:text-2xl font-bold text-[#1F1F39] font-rubik">Our Menu</h2>
            </div>

            <div class="flex items-center gap-2">
                <!-- Navigation Arrows on Desktop (active when categories exceed visible width) -->
                <div class="hidden sm:flex items-center gap-1.5" x-show="canScrollLeft || canScrollRight">
                    <button type="button" @click="scroll(-1)" 
                            :disabled="!canScrollLeft"
                            :class="canScrollLeft ? 'opacity-100 hover:bg-[#FF006B] hover:text-white cursor-pointer' : 'opacity-30 cursor-not-allowed'"
                            aria-label="Previous categories"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border border-gray-200 bg-white text-[#1F1F39] flex items-center justify-center transition-all shadow-xs">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                    <button type="button" @click="scroll(1)" 
                            :disabled="!canScrollRight"
                            :class="canScrollRight ? 'opacity-100 hover:bg-[#FF006B] hover:text-white cursor-pointer' : 'opacity-30 cursor-not-allowed'"
                            aria-label="Next categories"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border border-gray-200 bg-white text-[#1F1F39] flex items-center justify-center transition-all shadow-xs">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                </div>

                <a href="{{ route('menu.index') }}" class="text-xs sm:text-sm font-bold text-[#FF006B] hover:underline flex items-center gap-1 ml-1 sm:ml-2">
                    <span>View All</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Horizontal Carousel Track -->
        <div x-ref="catTrack"
             @scroll.passive="checkScroll()"
             tabindex="0"
             @keydown.arrow-left="scroll(-1)"
             @keydown.arrow-right="scroll(1)"
             class="flex items-stretch gap-3 sm:gap-3.5 lg:gap-4 overflow-x-auto pb-2.5 pt-1 no-scrollbar scroll-smooth snap-x snap-mandatory focus:outline-hidden">
            @foreach($categories as $category)
                <a href="{{ route('menu.index', ['category' => $category->slug]) }}" 
                   class="category-carousel-item flex flex-col items-center justify-between rounded-2xl bg-white border border-[#EFF0F6] hover:border-[#FF006B] hover:shadow-md transition-all p-3 text-center group shadow-xs">
                    <!-- Enlarged Category Icon -->
                    <div class="w-16 h-16 sm:w-20 sm:h-20 lg:w-20 lg:h-20 rounded-2xl bg-[#FFF8FA] border border-[#FFEBF2] flex items-center justify-center p-2 group-hover:scale-105 group-hover:bg-[#FFF0F6] transition-all duration-300">
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" 
                             class="w-full h-full object-contain"
                             onerror="this.src='{{ asset('images/defaults/category-placeholder.svg') }}'">
                    </div>
                    <!-- Clear Category Name without truncation -->
                    <span class="text-xs sm:text-sm font-bold text-[#1F1F39] group-hover:text-[#FF006B] transition-colors text-center line-clamp-2 leading-tight mt-2 px-0.5">
                        {{ $category->name }}
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- 3. NEW "Explore Our Menu" Product Carousel (5 visible on desktop, swipeable on mobile) -->
    @if(isset($exploreMenuProducts) && $exploreMenuProducts->count() > 0)
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
             x-data="{
                 scroll(direction) {
                     const el = this.$refs.exploreTrack;
                     if (!el) return;
                     const scrollAmount = el.clientWidth * 0.8;
                     el.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
                 },
                 canScrollLeft: false,
                 canScrollRight: true,
                 checkScroll() {
                     const el = this.$refs.exploreTrack;
                     if (!el) return;
                     this.canScrollLeft = el.scrollLeft > 10;
                     this.canScrollRight = el.scrollLeft < (el.scrollWidth - el.clientWidth - 10);
                 }
             }"
             x-init="$nextTick(() => checkScroll()); window.addEventListener('resize', () => checkScroll())">
        
        <!-- Section Header: "Explore Our Menu" + Carousel Controls + "See All" -->
        <div class="flex items-center justify-between mb-3.5 sm:mb-4">
            <div>
                <h2 class="text-lg sm:text-2xl font-bold text-[#1F1F39] font-rubik">Explore Our Menu</h2>
                <p class="text-xs text-[#6E7191] hidden sm:block mt-0.5">Chef's handcrafted homestyle dishes cooked fresh for you</p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Navigation Arrows on Desktop -->
                <div class="hidden sm:flex items-center gap-1.5">
                    <button type="button" @click="scroll(-1)" 
                            :disabled="!canScrollLeft"
                            :class="canScrollLeft ? 'opacity-100 hover:bg-[#FF006B] hover:text-white cursor-pointer' : 'opacity-30 cursor-not-allowed'"
                            aria-label="Previous dishes"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border border-gray-200 bg-white text-[#1F1F39] flex items-center justify-center transition-all shadow-xs">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                    <button type="button" @click="scroll(1)" 
                            :disabled="!canScrollRight"
                            :class="canScrollRight ? 'opacity-100 hover:bg-[#FF006B] hover:text-white cursor-pointer' : 'opacity-30 cursor-not-allowed'"
                            aria-label="Next dishes"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border border-gray-200 bg-white text-[#1F1F39] flex items-center justify-center transition-all shadow-xs">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                </div>

                <a href="{{ route('menu.index') }}" class="text-xs sm:text-sm font-bold text-[#FF006B] hover:underline flex items-center gap-1 ml-1 sm:ml-2">
                    <span>See All</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Horizontal Product Carousel Track (5 Visible on Desktop Row) -->
        <div x-ref="exploreTrack"
             @scroll.passive="checkScroll()"
             tabindex="0"
             @keydown.arrow-left="scroll(-1)"
             @keydown.arrow-right="scroll(1)"
             class="flex items-stretch gap-3 sm:gap-4 overflow-x-auto pb-3 pt-1 no-scrollbar scroll-smooth snap-x snap-mandatory focus:outline-hidden">
            @foreach($exploreMenuProducts as $product)
                <div class="product-carousel-item flex flex-col">
                    <x-food-card :product="$product" layout="grid" class="h-full" />
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- 4. Promotional Pastel Banners (FoodKing Screenshot 2) -->
    @php
        $hasOffer1 = isset($offerBanner1) && $offerBanner1;
        $hasOffer2 = isset($offerBanner2) && $offerBanner2;
        $showOffer1 = $hasOffer1 ? $offerBanner1->is_active : true;
        $showOffer2 = $hasOffer2 ? $offerBanner2->is_active : true;
    @endphp
    @if($showOffer1 || $showOffer2)
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-5">
            <!-- Left Promo Banner: Offer Banner 1 -->
            @if($showOffer1)
                @php
                    $ob1 = $hasOffer1 ? $offerBanner1 : null;
                    $ob1Theme = $ob1->bg_gradient ?? 'mint';
                    $ob1Bg = 'bg-[#E8F8F2] border-[#D5EFE5]';
                    $ob1Badge = 'text-[#FF006B]';
                    if ($ob1Theme === 'peach') {
                        $ob1Bg = 'bg-[#FFF6E9] border-[#FBEAD2]';
                        $ob1Badge = 'text-[#FF8A26]';
                    } elseif ($ob1Theme === 'rose') {
                        $ob1Bg = 'bg-[#FFF3F8] border-[#FFE4F0]';
                        $ob1Badge = 'text-[#F50070]';
                    } elseif ($ob1Theme === 'lavender') {
                        $ob1Bg = 'bg-[#F3E8FF] border-[#E9D5FF]';
                        $ob1Badge = 'text-[#9333EA]';
                    }
                    $ob1BadgeText = $ob1->badge_text ?? 'Uplifting anytime';
                    $ob1Title = $ob1->title ?? 'Flat 20% off on Dum Biryani & Starters';
                    $ob1Subtitle = $ob1->subtitle ?? 'Slow dum-cooked with royal saffron & spices';
                    $ob1ButtonText = $ob1->button_text ?? 'Order Now';
                    $ob1ButtonUrl = $ob1->button_url ?? route('menu.index');
                    $ob1ImageUrl = $ob1 ? $ob1->image_url : asset('images/banners/banner-biryani.svg');
                    $ob1Alt = $ob1 ? ($ob1->alt_text ?: $ob1->title) : 'Dum Biryani Special';
                @endphp
                <div class="rounded-2xl p-4 sm:p-5 {{ $ob1Bg }} border flex items-center justify-between gap-4 shadow-xs relative overflow-hidden group">
                    <div class="space-y-1.5 z-10 max-w-[65%]">
                        @if($ob1BadgeText)
                            <span class="text-xs font-bold {{ $ob1Badge }} block">{{ $ob1BadgeText }}</span>
                        @endif
                        <h3 class="text-xs sm:text-sm font-bold text-[#1F1F39] leading-tight">
                            {{ $ob1Title }}
                        </h3>
                        @if($ob1Subtitle)
                            <p class="text-[11px] sm:text-xs text-gray-600 line-clamp-1">{{ $ob1Subtitle }}</p>
                        @endif
                        <div class="pt-2">
                            <a href="{{ $ob1ButtonUrl }}" 
                                class="inline-block px-3.5 py-1 sm:px-4 sm:py-1.5 rounded-full border border-[#1F1F39] text-[#1F1F39] hover:bg-[#1F1F39] hover:text-white font-bold text-[11px] sm:text-xs transition-colors">
                                {{ $ob1ButtonText }}
                            </a>
                        </div>
                    </div>

                    <!-- Banner Image Right -->
                    <div class="w-24 h-20 sm:w-32 sm:h-24 shrink-0 rounded-xl overflow-hidden bg-white/40 flex items-center justify-center p-1 border border-white/60">
                        <img src="{{ $ob1ImageUrl }}" alt="{{ $ob1Alt }}" 
                             class="w-full h-full object-contain group-hover:scale-105 transition-transform"
                             onerror="this.src='{{ asset('images/defaults/banner-placeholder.jpg') }}'">
                    </div>
                </div>
            @endif

            <!-- Right Promo Banner: Offer Banner 2 -->
            @if($showOffer2)
                @php
                    $ob2 = $hasOffer2 ? $offerBanner2 : null;
                    $ob2Theme = $ob2->bg_gradient ?? 'peach';
                    $ob2Bg = 'bg-[#FFF6E9] border-[#FBEAD2]';
                    $ob2Badge = 'text-[#FF8A26]';
                    if ($ob2Theme === 'mint') {
                        $ob2Bg = 'bg-[#E8F8F2] border-[#D5EFE5]';
                        $ob2Badge = 'text-[#FF006B]';
                    } elseif ($ob2Theme === 'rose') {
                        $ob2Bg = 'bg-[#FFF3F8] border-[#FFE4F0]';
                        $ob2Badge = 'text-[#F50070]';
                    } elseif ($ob2Theme === 'lavender') {
                        $ob2Bg = 'bg-[#F3E8FF] border-[#E9D5FF]';
                        $ob2Badge = 'text-[#9333EA]';
                    }
                    $ob2BadgeText = $ob2->badge_text ?? 'Savory and Satisfying';
                    $ob2Title = $ob2->title ?? 'Flat 15% off on Kolkata Rolls & Kebabs';
                    $ob2Subtitle = $ob2->subtitle ?? 'Crispy parathas with spiced chicken & chutney';
                    $ob2ButtonText = $ob2->button_text ?? 'Order Now';
                    $ob2ButtonUrl = $ob2->button_url ?? route('menu.index');
                    $ob2ImageUrl = $ob2 ? $ob2->image_url : asset('images/banners/banner-rolls.svg');
                    $ob2Alt = $ob2 ? ($ob2->alt_text ?: $ob2->title) : 'Kolkata Rolls Special';
                @endphp
                <div class="rounded-2xl p-4 sm:p-5 {{ $ob2Bg }} border flex items-center justify-between gap-4 shadow-xs relative overflow-hidden group">
                    <div class="space-y-1.5 z-10 max-w-[65%]">
                        @if($ob2BadgeText)
                            <span class="text-xs font-bold {{ $ob2Badge }} block">{{ $ob2BadgeText }}</span>
                        @endif
                        <h3 class="text-xs sm:text-sm font-bold text-[#1F1F39] leading-tight">
                            {{ $ob2Title }}
                        </h3>
                        @if($ob2Subtitle)
                            <p class="text-[11px] sm:text-xs text-gray-600 line-clamp-1">{{ $ob2Subtitle }}</p>
                        @endif
                        <div class="pt-2">
                            <a href="{{ $ob2ButtonUrl }}" 
                                class="inline-block px-3.5 py-1 sm:px-4 sm:py-1.5 rounded-full border border-[#1F1F39] text-[#1F1F39] hover:bg-[#1F1F39] hover:text-white font-bold text-[11px] sm:text-xs transition-colors">
                                {{ $ob2ButtonText }}
                            </a>
                        </div>
                    </div>

                    <!-- Banner Image Right -->
                    <div class="w-24 h-20 sm:w-32 sm:h-24 shrink-0 rounded-xl overflow-hidden bg-white/40 flex items-center justify-center p-1 border border-white/60">
                        <img src="{{ $ob2ImageUrl }}" alt="{{ $ob2Alt }}" 
                             class="w-full h-full object-contain group-hover:scale-105 transition-transform"
                             onerror="this.src='{{ asset('images/defaults/banner-placeholder.jpg') }}'">
                    </div>
                </div>
            @endif
        </div>
    </section>
    @endif

    <!-- 5. "Featured Items" Section (4 Cards Per Row on Desktop = 2 Rows of 4; 2 Cards Per Row on Mobile = 4 Rows of 2) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg sm:text-2xl font-bold text-[#1F1F39] font-rubik">Featured Items</h2>
            </div>
            <a href="{{ route('menu.index') }}" class="text-xs sm:text-sm font-bold text-[#FF006B] hover:underline flex items-center gap-1">
                <span>See All</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <!-- 4-Column on Desktop (2 rows x 4 = 8), 2-Column on Mobile (4 rows x 2 = 8) -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
            @foreach($featuredProducts as $product)
                <x-food-card :product="$product" layout="grid" class="h-full" />
            @endforeach
        </div>
    </section>

    <!-- 5. "Most Popular Items" Section (FoodKing Screenshot 2) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg sm:text-2xl font-bold text-[#1F1F39] font-rubik">Most Popular Items</h2>
            </div>
            <a href="{{ route('menu.index', ['sort' => 'popular']) }}" class="text-xs sm:text-sm font-bold text-[#FF006B] hover:underline flex items-center gap-1">
                <span>View All</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <!-- List format on mobile, 2-column list on tablet/desktop (FoodKing Screenshot 2) -->
        <div class="space-y-3 sm:grid sm:grid-cols-2 sm:space-y-0 sm:gap-4">
            @foreach($popularProducts as $product)
                <x-food-card :product="$product" layout="list" />
            @endforeach
        </div>
    </section>

    <!-- 6. Fast Delivery Quality Banner -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl sm:rounded-3xl bg-white border border-[#EFF0F6] p-5 sm:p-8 shadow-xs flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="space-y-3 max-w-xl text-center lg:text-left">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-black bg-[#FFF0F6] text-[#FF006B] uppercase tracking-wider">
                    ⚡ Fast 30-Minute Delivery
                </span>
                <h3 class="text-xl sm:text-3xl font-black font-rubik text-[#1F1F39] leading-tight">
                    Craving delicious hot meals? We deliver in minutes!
                </h3>
                <p class="text-xs sm:text-sm text-[#6E7191] leading-relaxed">
                    Every dish is freshly prepared upon order confirmation and dispatched in thermal insulated tamper-proof packaging to ensure royal flavors.
                </p>
                <div class="pt-1 flex flex-wrap items-center justify-center lg:justify-start gap-3">
                    <a href="{{ route('menu.index') }}" 
                       class="px-5 py-2.5 rounded-full bg-[#FF006B] hover:bg-[#E60060] text-white font-extrabold text-xs shadow-xs transition-transform active:scale-95">
                        Start Your Order
                    </a>
                    <a href="{{ route('offers.index') }}" 
                       class="px-5 py-2.5 rounded-full border border-[#EFF0F6] text-[#1F1F39] hover:bg-[#F7F7FC] font-bold text-xs transition-colors">
                        View Active Offers
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-3 text-center">
                <div class="p-4 sm:p-5 rounded-2xl bg-[#F7F7FC] border border-[#EFF0F6] space-y-1">
                    <p class="text-2xl sm:text-3xl font-black text-[#FF006B] font-rubik">4.9 ★</p>
                    <p class="text-xs font-bold text-[#1F1F39]">Food Quality</p>
                    <p class="text-[10px] text-[#6E7191]">5,000+ reviews</p>
                </div>
                <div class="p-4 sm:p-5 rounded-2xl bg-[#F7F7FC] border border-[#EFF0F6] space-y-1">
                    <p class="text-2xl sm:text-3xl font-black text-emerald-600 font-rubik">30m</p>
                    <p class="text-xs font-bold text-[#1F1F39]">Avg. Delivery</p>
                    <p class="text-[10px] text-[#6E7191]">Thermal bags</p>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
