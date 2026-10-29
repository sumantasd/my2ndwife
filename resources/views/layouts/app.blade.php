<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Models\RestaurantSetting::get('meta_title', 'My 2nd Wife | Premium Food Delivery'))</title>
    <meta name="description" content="@yield('meta_description', \App\Models\RestaurantSetting::get('meta_description', 'Order artisanal dum biryanis, smash burgers & sourdough pizzas in Kolkata.'))">

    <!-- PWA & Android WebView Readiness -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#FF006B">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="icon" type="image/svg+xml" href="{{ asset(\App\Models\RestaurantSetting::get('restaurant_favicon', 'images/defaults/favicon.svg')) }}">

    <!-- Google Fonts: Rubik & Public Sans (Exact FoodKing Typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&family=Rubik:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @php
        $gmapsKey = \App\Models\RestaurantSetting::get('google_maps_api_key');
        $gmapsJsEnabled = (bool) \App\Models\RestaurantSetting::get('google_maps_js_enabled', false);
    @endphp
    @if($gmapsJsEnabled && !empty($gmapsKey))
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $gmapsKey }}&libraries=places" async defer></script>
    @endif

    <!-- Prevent Alpine.js FOUC, ghost backdrops, and premature modal overlay rendering -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        function globalAppData() {
            return {
                cartDrawerOpen: false,
                cartCount: {{ (int) app(\App\Services\CartService::class)->count() }},
                cartSubtotal: {{ (float) (app(\App\Services\CartService::class)->calculateTotals()['subtotal'] ?? 0) }},
                quickModalOpen: false,
                modalProduct: null,
                selectedVariation: null,
                selectedAddons: [],
                modalQuantity: 1,
                modalInstructions: '',
                orderType: 'delivery',
                toasts: [],
                addingToCart: false,

                openQuickView(product) {
                    this.modalProduct = product;
                    this.selectedVariation = product.variations && product.variations.length ? product.variations[0].id : null;
                    this.selectedAddons = [];
                    this.modalQuantity = 1;
                    this.modalInstructions = '';
                    this.quickModalOpen = true;
                },

                toggleAddon(id) {
                    const idx = this.selectedAddons.indexOf(id);
                    if (idx > -1) {
                        this.selectedAddons.splice(idx, 1);
                    } else {
                        this.selectedAddons.push(id);
                    }
                },

                calculateModalTotal() {
                    if (!this.modalProduct) return '0.00';
                    let base = parseFloat(this.modalProduct.current_price) || 0;
                    if (this.selectedVariation && this.modalProduct.variations) {
                        let v = this.modalProduct.variations.find(x => x.id == this.selectedVariation);
                        if (v) base = parseFloat(v.price) || 0;
                    }
                    let addonsTotal = 0;
                    if (this.modalProduct.addon_groups) {
                        this.modalProduct.addon_groups.forEach(g => {
                            if (g.addons) {
                                g.addons.forEach(a => {
                                    if (this.selectedAddons.some(id => id == a.id)) {
                                        addonsTotal += parseFloat(a.price) || 0;
                                    }
                                });
                            }
                        });
                    }
                    return ((base + addonsTotal) * this.modalQuantity).toFixed(2);
                },

                addToast(toast) {
                    const id = 'toast_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7);
                    const duration = toast.duration || 3000;
                    const newToast = {
                        id,
                        type: toast.type || 'success',
                        title: toast.title || (toast.type === 'error' ? 'Notice' : 'Success'),
                        message: toast.message || '',
                        productName: toast.productName || null,
                        timer: null,
                    };
                    this.toasts.push(newToast);

                    newToast.timer = setTimeout(() => {
                        this.removeToast(id);
                    }, duration);

                    this.$nextTick(() => {
                        if (window.renderLucide && typeof window.renderLucide === 'function') {
                            window.renderLucide();
                        } else if (window.lucide && typeof window.lucide.createIcons === 'function') {
                            window.lucide.createIcons();
                        }
                    });
                },

                removeToast(id) {
                    const idx = this.toasts.findIndex(t => t.id === id);
                    if (idx > -1) {
                        if (this.toasts[idx].timer) {
                            clearTimeout(this.toasts[idx].timer);
                        }
                        this.toasts.splice(idx, 1);
                    }
                },

                async submitQuickAdd(event) {
                    if (this.addingToCart) return;
                    this.addingToCart = true;
                    const form = event.target;
                    const formData = new FormData(form);
                    const productName = this.modalProduct ? this.modalProduct.name : '';

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
                        this.addingToCart = false;

                        if (data && data.success) {
                            this.quickModalOpen = false;
                            if (data.cart_count !== undefined) {
                                this.cartCount = data.cart_count;
                            }
                            if (data.cart_subtotal !== undefined) {
                                this.cartSubtotal = data.cart_subtotal;
                            }
                            const name = data.product_name || productName;
                            this.addToast({
                                type: 'success',
                                title: 'Item added to cart',
                                message: name ? `${name} added to your cart.` : (data.message || 'Item added to your cart!'),
                                productName: name
                            });
                            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                        } else {
                            this.addToast({
                                type: 'error',
                                title: 'Unable to Add Item',
                                message: data?.message || 'Could not add this item to cart.'
                            });
                        }
                    } catch (err) {
                        this.addingToCart = false;
                        form.submit();
                    }
                },

                init() {
                    window.showToast = (toast) => this.addToast(toast);
                    window.addEventListener('show-toast', (e) => this.addToast(e.detail));
                    window.addEventListener('cart-updated', (e) => {
                        if (e.detail && e.detail.cart_count !== undefined) {
                            this.cartCount = e.detail.cart_count;
                        }
                        if (e.detail && e.detail.cart_subtotal !== undefined) {
                            this.cartSubtotal = e.detail.cart_subtotal;
                        }
                    });

                    @if(session('success'))
                        this.addToast({
                            type: 'success',
                            title: 'Success',
                            message: @json(session('success'))
                        });
                    @endif
                    @if(session('error'))
                        this.addToast({
                            type: 'error',
                            title: 'Notice',
                            message: @json(session('error'))
                        });
                    @endif
                    @if(session('info'))
                        this.addToast({
                            type: 'info',
                            title: 'Information',
                            message: @json(session('info'))
                        });
                    @endif
                }
            };
        }
    </script>
</head>
<body class="min-h-full flex flex-col bg-[#F7F7FC] text-[#6E7191] font-sans antialiased selection:bg-[#FFF0F6] selection:text-[#FF006B]"
      x-data="globalAppData()">

    @php
        $isTempClosed = (bool) \App\Models\RestaurantSetting::get('is_temporarily_closed', false);
        $isKitchenOpen = (bool) \App\Models\RestaurantSetting::get('is_restaurant_open', true);
        $tempNotice = \App\Models\RestaurantSetting::get('temporary_closed_notice', 'Kitchen is temporarily closed.');
        $announcementEnabled = (bool) \App\Models\RestaurantSetting::get('announcement_bar_enabled', true);
        $announcementText = \App\Models\RestaurantSetting::get('announcement_bar_text', '');
        $logoUrl = asset(\App\Models\RestaurantSetting::get('restaurant_logo', 'images/defaults/logo.svg'));
        $restaurantName = \App\Models\RestaurantSetting::get('restaurant_name', 'My 2nd Wife');
    @endphp

    @if($isTempClosed || !$isKitchenOpen)
        <div class="bg-amber-600 text-white text-xs font-bold py-2.5 px-4 text-center shadow-md flex items-center justify-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-amber-200"></i>
            <span>{{ $tempNotice ?: 'Our kitchen is currently closed and not accepting new orders.' }}</span>
        </div>
    @elseif($announcementEnabled && $announcementText)
        <div class="bg-gradient-to-r from-pink-600 via-rose-600 to-pink-700 text-white text-xs font-bold py-1.5 px-4 text-center shadow-xs">
            <span>{{ $announcementText }}</span>
        </div>
    @endif

    <!-- Top Contact & Branch Indicator Bar (Desktop Only) -->
    <div class="hidden md:block bg-[#1F1F39] text-white text-xs py-2 px-4 border-b border-gray-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <!-- Left: Restaurant Contact & Operating Hours -->
            <div class="flex items-center gap-4 text-[11px] font-medium text-gray-300">
                <span class="flex items-center gap-1.5">
                    <i data-lucide="phone" class="w-3.5 h-3.5 text-[#FF006B]"></i>
                    <span>{{ \App\Models\RestaurantSetting::get('restaurant_phone', '+91 98300 12345') }}</span>
                </span>
                <span class="hidden md:inline-block text-gray-600">•</span>
                <span class="hidden md:flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-[#FF006B]"></i>
                    <span>{{ \App\Models\RestaurantSetting::get('opening_time', '11:00 AM') }} - {{ \App\Models\RestaurantSetting::get('closing_time', '11:30 PM') }}</span>
                </span>
            </div>

            <!-- Center/Right: Live Status & Location -->
            <div class="flex items-center gap-3 text-[11px]">
                <div class="flex items-center gap-1.5 text-white bg-white/10 px-2.5 py-0.5 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full {{ ($isKitchenOpen && !$isTempClosed) ? 'bg-[#1AB759] animate-pulse' : 'bg-amber-400' }}"></span>
                    <span class="font-semibold {{ ($isKitchenOpen && !$isTempClosed) ? 'text-emerald-400' : 'text-amber-300' }}">
                        {{ ($isKitchenOpen && !$isTempClosed) ? 'Kitchen Open' : 'Kitchen Closed' }}
                    </span>
                </div>
                <div class="flex items-center gap-1 text-gray-300">
                    <i data-lucide="map-pin" class="w-3 h-3 text-[#FF006B]"></i>
                    <span>{{ \App\Models\RestaurantSetting::get('restaurant_city', 'Kolkata') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header (FoodKing Mobile & Desktop Navbar) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-[#EFF0F6] transition-all duration-200"
            id="mainHeader">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Mobile Compact Header (FoodKing Screenshot 1 & 2) -->
            <div class="md:hidden py-3">
                <div class="flex items-center justify-between gap-3">
                    <!-- Brand Logo -->
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <img src="{{ $logoUrl }}" alt="{{ $restaurantName }}" class="h-8 w-auto">
                    </a>

                    <!-- Location / Store Pill (FoodKing Reference: Mirpur-1 (main) + pink shop badge) -->
                    <div class="flex items-center gap-2">
                        <div class="text-right">
                            <span class="text-xs font-bold text-[#1F1F39] block leading-tight">
                                {{ \App\Models\RestaurantSetting::get('restaurant_city', 'Park Street') }} (main)
                            </span>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-[#FFF0F6] text-[#FF006B] flex items-center justify-center shrink-0 border border-[#FEE2EB] shadow-xs">
                            <i data-lucide="store" class="w-4 h-4 text-[#FF006B]"></i>
                        </div>
                    </div>
                </div>

                <!-- Large Rounded Search Bar below header (FoodKing Screenshot 1 & 2) -->
                @if(!request()->routeIs('search.index'))
                <form action="{{ route('search.index') }}" method="GET" class="mt-2.5">
                    <div class="relative w-full">
                        <input type="text" name="q" value="{{ request('q') ?? request('search') }}"
                               placeholder="Search"
                               class="w-full bg-[#F4F5F7] text-[#1F1F39] text-xs sm:text-sm pl-11 pr-4 py-2.5 rounded-2xl border border-transparent focus:outline-none focus:border-[#FF006B] focus:bg-white transition-all placeholder:text-gray-400">
                        <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-4 top-3"></i>
                    </div>
                </form>
                @endif
            </div>

            <!-- Desktop Navigation Header Layout -->
            <div class="hidden md:flex items-center justify-between h-20 gap-4">
                
                <!-- Logo -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                        <img src="{{ $logoUrl }}" alt="{{ $restaurantName }}" class="h-10 md:h-12 w-auto transition-transform group-hover:scale-105">
                    </a>
                </div>

                <!-- Center Nav Links -->
                <nav class="flex items-center gap-8 text-sm font-semibold">
                    <a href="{{ route('home') }}" 
                       class="transition-colors hover:text-[#FF006B] {{ request()->routeIs('home') ? 'text-[#FF006B] font-bold' : 'text-[#1F1F39]' }}">
                        Home
                    </a>
                    <a href="{{ route('menu.index') }}" 
                       class="transition-colors hover:text-[#FF006B] {{ request()->routeIs('menu.*') ? 'text-[#FF006B] font-bold' : 'text-[#1F1F39]' }}">
                        Menu
                    </a>
                    <a href="{{ route('offers.index') }}" 
                       class="transition-colors hover:text-[#FF006B] {{ request()->routeIs('offers.*') ? 'text-[#FF006B] font-bold' : 'text-[#1F1F39]' }}">
                        Offers
                    </a>
                </nav>

                <!-- Search Bar (Desktop) -->
                <form action="{{ route('search.index') }}" method="GET" class="flex flex-1 max-w-xs mx-4">
                    <div class="relative w-full">
                        <input type="text" name="q" value="{{ request('q') ?? request('search') }}"
                               placeholder="Search dishes..."
                               class="w-full bg-[#F7F7FC] text-[#1F1F39] text-xs pl-10 pr-4 py-2.5 rounded-full border border-[#EFF0F6] focus:outline-none focus:border-[#FF006B] focus:bg-white transition-all placeholder:text-gray-400">
                        <i data-lucide="search" class="w-4 h-4 text-[#FF006B] absolute left-3.5 top-3"></i>
                    </div>
                </form>

                <!-- Right Action Buttons -->
                <div class="flex items-center gap-3 md:gap-4">
                    <!-- FoodKing Cart Button: Bag Icon + Count Badge + Formatted Subtotal -->
                    <button type="button" @click="cartDrawerOpen = true" 
                            class="flex items-center gap-2.5 px-3.5 py-2 rounded-full bg-[#FFF0F6] text-[#1F1F39] hover:bg-[#FF006B] hover:text-white transition-all border border-[#F0E6EC] group shadow-xs">
                        <div class="relative">
                            <i data-lucide="shopping-bag" class="w-5 h-5 text-[#FF006B] group-hover:text-white transition-colors"></i>
                            <span class="absolute -top-1.5 -right-2 bg-[#FF006B] group-hover:bg-white group-hover:text-[#FF006B] text-white text-[10px] font-extrabold w-4 h-4 rounded-full flex items-center justify-center shadow-xs"
                                  x-text="cartCount" x-show="cartCount > 0"></span>
                        </div>
                        <span class="hidden sm:inline text-xs font-black" x-text="'₹' + cartSubtotal">₹0.00</span>
                    </button>

                    <!-- User Account / Login Button -->
                    @auth
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen" 
                                    class="flex items-center gap-2 p-1.5 md:px-3 md:py-2 rounded-full bg-white border border-[#EFF0F6] hover:bg-[#FFF0F6] transition-all shadow-xs">
                                <div class="w-7 h-7 rounded-full bg-[#FF006B] text-white flex items-center justify-center text-xs font-bold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span class="hidden md:inline text-xs font-bold text-[#1F1F39]">{{ Str::limit(auth()->user()->name, 12) }}</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="userMenuOpen" @click.outside="userMenuOpen = false" x-cloak style="display: none;"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-[#EFF0F6] py-2 z-50 text-xs font-medium">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="font-bold text-[#1F1F39] truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] text-[#6E7191] truncate">{{ auth()->user()->email }}</p>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                        <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold rounded-full bg-pink-50 text-[#FF006B] uppercase">
                                            {{ auth()->user()->role }}
                                        </span>
                                    @endif
                                </div>

                                @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[#FF006B] hover:bg-[#FFF0F6] font-bold">
                                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Admin Panel
                                    </a>
                                @endif

                                <a href="{{ route('account.orders') }}" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-[#F7F7FC] text-[#1F1F39]">
                                    <i data-lucide="package" class="w-4 h-4 text-gray-400"></i> My Orders
                                </a>
                                <a href="{{ route('account.addresses') }}" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-[#F7F7FC] text-[#1F1F39]">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i> Saved Addresses
                                </a>
                                <a href="{{ route('account.profile') }}" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-[#F7F7FC] text-[#1F1F39]">
                                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i> Profile Settings
                                </a>

                                <form action="{{ route('logout') }}" method="POST" class="border-t border-gray-100 mt-1">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-rose-600 hover:bg-rose-50 text-left font-semibold">
                                        <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold bg-[#FF006B] text-white hover:bg-[#E60060] transition-all shadow-xs">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Sign In</span>
                        </a>
                    @endauth

                </div>
            </div>
        </div>
    </header>

    <!-- Global Floating Toast Notification Stack (Task 1) -->
    <div class="fixed top-5 right-5 z-[99999] flex flex-col gap-2.5 pointer-events-none w-full max-w-sm px-4 sm:px-0"
         role="status" aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="pointer-events-auto bg-white/95 backdrop-blur-md rounded-2xl p-3.5 shadow-2xl border transition-all duration-300 flex items-start gap-3 relative overflow-hidden"
                 :class="{
                     'border-pink-200/80 shadow-pink-500/10': toast.type === 'success',
                     'border-rose-200/80 shadow-rose-500/10': toast.type === 'error',
                     'border-blue-200/80 shadow-blue-500/10': toast.type === 'info'
                 }"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-6 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 sm:translate-x-6 scale-95">
                
                <!-- Left Accent Border Strip -->
                <div class="absolute left-0 top-0 bottom-0 w-1.5"
                     :class="{
                         'bg-[#FF006B]': toast.type === 'success',
                         'bg-rose-500': toast.type === 'error',
                         'bg-blue-500': toast.type === 'info'
                     }"></div>

                <!-- Toast Icon Badge -->
                <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center font-bold"
                     :class="{
                         'bg-[#FFF0F6] text-[#FF006B]': toast.type === 'success',
                         'bg-rose-50 text-rose-600': toast.type === 'error',
                         'bg-blue-50 text-blue-600': toast.type === 'info'
                     }">
                    <template x-if="toast.type === 'success'">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-[#FF006B]"></i>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                    </template>
                </div>

                <!-- Content Area -->
                <div class="flex-1 min-w-0 pr-1">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-xs font-black text-[#1F1F39] truncate" x-text="toast.title"></h4>
                        <button type="button" @click="removeToast(toast.id)"
                                class="text-gray-400 hover:text-gray-700 p-0.5 rounded transition-colors"
                                aria-label="Dismiss">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    <p class="text-xs text-[#6E7191] mt-0.5 leading-snug break-words" x-text="toast.message"></p>

                    <template x-if="toast.type === 'success'">
                        <button type="button" @click="cartDrawerOpen = true; removeToast(toast.id)"
                                class="mt-2 text-[11px] font-extrabold text-[#FF006B] hover:text-[#E60060] inline-flex items-center gap-1 transition-colors">
                            <span>View Cart</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    @if(session('success'))
        <div class="sr-only" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="sr-only" role="alert">{{ session('error') }}</div>
    @endif

    <!-- Main Page Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Slide-Over Cart Drawer (FoodKing FrontendCartComponent) -->
    <div x-show="cartDrawerOpen" x-cloak style="display: none;"
         class="fixed inset-0 z-50 overflow-hidden bg-black/50 backdrop-blur-xs transition-opacity"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="absolute inset-y-0 right-0 max-w-sm w-full bg-white shadow-2xl flex flex-col z-50"
             @click.outside="cartDrawerOpen = false">
            
            <!-- Drawer Header -->
            <div class="p-4 border-b border-[#EFF0F6] flex items-center justify-between bg-white">
                <div class="flex items-center gap-2">
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-[#FF006B]"></i>
                    <h3 class="font-bold text-[#1F1F39] text-base">Your Cart</h3>
                    <span class="bg-[#FFF0F6] text-[#FF006B] text-xs font-black px-2 py-0.5 rounded-full" x-text="cartCount"></span>
                </div>
                <button type="button" @click="cartDrawerOpen = false" class="text-gray-400 hover:text-[#1F1F39] p-1.5 rounded-full hover:bg-gray-100 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Delivery vs. Takeaway Toggle (FoodKing Style) -->
            <div class="p-4 bg-[#F7F7FC] border-b border-[#EFF0F6]">
                <div class="flex items-center justify-center p-1 bg-white rounded-2xl border border-[#EFF0F6] max-w-xs mx-auto">
                    <button type="button" @click="orderType = 'delivery'" 
                            :class="orderType === 'delivery' ? 'bg-[#FF006B] text-white font-bold shadow-xs' : 'text-[#6E7191] font-medium'"
                            class="flex-1 py-1.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5">
                        <i data-lucide="bike" class="w-3.5 h-3.5"></i>
                        <span>Delivery</span>
                    </button>
                    <button type="button" @click="orderType = 'pickup'" 
                            :class="orderType === 'pickup' ? 'bg-[#FF006B] text-white font-bold shadow-xs' : 'text-[#6E7191] font-medium'"
                            class="flex-1 py-1.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5">
                        <i data-lucide="store" class="w-3.5 h-3.5"></i>
                        <span>Takeaway</span>
                    </button>
                </div>
            </div>

            <!-- Items List -->
            @php
                $cartService = app(\App\Services\CartService::class);
                $cartDetailed = $cartService->getDetailedItems();
                $cartTotals = $cartService->calculateTotals();
            @endphp

            <div class="flex-1 overflow-y-auto p-4 space-y-3.5 divide-y divide-[#EFF0F6]">
                @forelse($cartDetailed as $item)
                    <div class="pt-3.5 first:pt-0 flex items-start justify-between gap-3">
                        <img src="{{ $item['product_image'] }}" alt="{{ $item['product_name'] }}" class="w-14 h-14 rounded-xl object-cover shrink-0 border border-[#EFF0F6]">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-[#1F1F39] truncate">{{ $item['product_name'] }}</h4>
                            @if(!empty($item['variation_name']))
                                <p class="text-[10px] text-[#FF006B] font-semibold">{{ $item['variation_name'] }}</p>
                            @endif
                            @if(!empty($item['addons']))
                                <p class="text-[10px] text-gray-400">
                                    +{{ implode(', ', array_column($item['addons'], 'name')) }}
                                </p>
                            @endif

                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs font-black text-[#1F1F39]">₹{{ number_format($item['line_subtotal'], 2) }}</span>
                                
                                <!-- Quantity Controls -->
                                <div class="flex items-center border border-[#EFF0F6] rounded-full bg-[#F7F7FC] px-2 py-0.5 text-xs">
                                    <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="item_key" value="{{ $item['key'] }}">
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                        <button type="submit" class="text-[#FF006B] font-black px-1.5 hover:text-black">-</button>
                                    </form>
                                    <span class="mx-2 font-bold text-[#1F1F39] text-xs">{{ $item['quantity'] }}</span>
                                    <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="item_key" value="{{ $item['key'] }}">
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                        <button type="submit" class="text-[#FF006B] font-black px-1.5 hover:text-black">+</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Remove Button -->
                        <form action="{{ route('cart.remove') }}" method="POST">
                            @csrf
                            <input type="hidden" name="item_key" value="{{ $item['key'] }}">
                            <button type="submit" class="text-gray-300 hover:text-rose-500 transition-colors p-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="text-center py-12 space-y-3">
                        <div class="w-16 h-16 rounded-full bg-[#FFF0F6] text-[#FF006B] flex items-center justify-center mx-auto text-2xl">
                            🛍️
                        </div>
                        <p class="text-sm font-bold text-[#1F1F39]">Your cart is empty</p>
                        <p class="text-xs text-[#6E7191]">Browse our menu and add your favorite dishes!</p>
                        <a href="{{ route('menu.index') }}" @click="cartDrawerOpen = false" 
                           class="inline-block px-5 py-2 rounded-full text-xs font-bold bg-[#FF006B] text-white hover:bg-[#E60060] transition-colors">
                            Explore Menu
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Drawer Footer Summary -->
            @if(!empty($cartDetailed))
                <div class="p-4 border-t border-[#EFF0F6] bg-white space-y-3">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-[#6E7191]">Subtotal:</span>
                        <span class="text-[#1F1F39] font-black text-sm">₹{{ number_format($cartTotals['subtotal'], 2) }}</span>
                    </div>
                    <p class="text-[10px] text-gray-400">Taxes, delivery, and discounts calculated at checkout.</p>
                    
                    <div class="flex gap-2">
                        <a href="{{ route('cart.index') }}" 
                           class="flex-1 py-2.5 rounded-full border border-[#EFF0F6] text-center text-xs font-bold text-[#1F1F39] hover:bg-[#F7F7FC] transition-colors">
                            View Full Cart
                        </a>
                        <a href="{{ route('checkout.index') }}" 
                           class="flex-1 py-2.5 rounded-full bg-[#FF006B] hover:bg-[#E60060] text-center text-xs font-bold text-white transition-all shadow-xs">
                            Checkout
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Interactive Food Customization Bottom Sheet / Modal (FoodKing Screenshot 5) -->
    <div x-show="quickModalOpen" x-cloak style="display: none;"
         class="fixed inset-0 z-50 overflow-hidden flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div @click.outside="quickModalOpen = false" 
             class="bg-white rounded-t-3xl sm:rounded-2xl max-w-lg w-full max-h-[90vh] flex flex-col overflow-hidden shadow-2xl border border-[#EFF0F6] relative"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 sm:scale-100"
             x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95">

            <template x-if="modalProduct">
                <div class="flex flex-col h-full overflow-hidden">
                    
                    <!-- Header Card: Dish Thumbnail Left, Title & Close (x) Right, Description, Price (Screenshot 5) -->
                    <div class="p-4 sm:p-5 border-b border-[#EFF0F6] flex items-start gap-3.5 bg-white shrink-0">
                        <!-- Food Thumbnail -->
                        <img :src="modalProduct.main_image_url" :alt="modalProduct.name" 
                             x-on:error="$event.target.src='{{ asset('images/defaults/food-placeholder.jpg') }}'"
                             class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover shrink-0 border border-[#EFF0F6] shadow-xs">
                        
                        <!-- Food Info & Close Button -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-bold text-[#1F1F39] text-base leading-snug line-clamp-1" x-text="modalProduct.name"></h3>
                                <button type="button" @click="quickModalOpen = false" 
                                        class="w-6 h-6 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center text-xs font-black transition-colors shrink-0">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                            <p class="text-xs text-[#6E7191] line-clamp-2 mt-1 leading-snug" x-text="modalProduct.description"></p>
                            <span class="text-base font-black text-[#1F1F39] block mt-1.5" x-text="'₹' + modalProduct.current_price"></span>
                        </div>
                    </div>

                    <!-- Customization Form -->
                    <form action="{{ route('cart.add') }}" method="POST" @submit.prevent="submitQuickAdd($event)" class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1">
                        @csrf
                        <input type="hidden" name="product_id" :value="modalProduct.id">

                        <!-- Quantity Stepper Row (Screenshot 5: Quantity :   (-) 1 (+)) -->
                        <div class="flex items-center justify-between py-1">
                            <span class="text-xs sm:text-sm font-bold text-[#1F1F39]">Quantity :</span>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="if(modalQuantity > 1) modalQuantity--" 
                                        class="w-7 h-7 rounded-full border-2 border-[#FF006B] text-[#FF006B] hover:bg-[#FFF0F6] flex items-center justify-center font-black text-sm active:scale-95 transition-all">
                                    -
                                </button>
                                <span class="font-bold text-sm text-[#1F1F39] min-w-[20px] text-center" x-text="modalQuantity"></span>
                                <button type="button" @click="if(modalQuantity < 20) modalQuantity++" 
                                        class="w-7 h-7 rounded-full border-2 border-[#FF006B] text-[#FF006B] hover:bg-[#FFF0F6] flex items-center justify-center font-black text-sm active:scale-95 transition-all">
                                    +
                                </button>
                                <input type="hidden" name="quantity" :value="modalQuantity">
                            </div>
                        </div>

                        <!-- Portion Variations (if available) -->
                        <template x-if="modalProduct.variations && modalProduct.variations.length > 0">
                            <div class="pt-2 border-t border-[#EFF0F6]">
                                <label class="block text-xs font-bold text-[#1F1F39] mb-2 uppercase tracking-wider">Choose Portion Size:</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <template x-for="v in modalProduct.variations" :key="v.id">
                                        <label :class="selectedVariation == v.id ? 'border-[#FF006B] bg-[#FFF0F6] text-[#FF006B]' : 'border-[#EFF0F6] hover:bg-gray-50 text-[#1F1F39]'"
                                               class="flex items-center justify-between p-2.5 rounded-xl border cursor-pointer text-xs transition-colors">
                                            <div class="flex items-center gap-2">
                                                <input type="radio" name="variation_id" :value="v.id" x-model="selectedVariation" class="text-[#FF006B] focus:ring-[#FF006B]">
                                                <span class="font-bold" x-text="v.name"></span>
                                            </div>
                                            <span class="font-black" x-text="'₹' + v.price"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Add-ons Section (Screenshot 5: Horizontal Addon Cards) -->
                        <template x-if="modalProduct.addon_groups && modalProduct.addon_groups.length > 0">
                            <div class="pt-2 border-t border-[#EFF0F6] space-y-3">
                                <template x-for="group in modalProduct.addon_groups" :key="group.id">
                                    <div>
                                        <h4 class="text-xs sm:text-sm font-bold text-[#1F1F39] mb-2" x-text="group.name || 'Addons'"></h4>
                                        <div class="flex items-center gap-3 overflow-x-auto pb-2 no-scrollbar">
                                            <template x-for="a in group.addons" :key="a.id">
                                                <div @click="toggleAddon(a.id)"
                                                     :class="selectedAddons.includes(a.id) ? 'border-[#FF006B] bg-[#FFF0F6] shadow-xs' : 'border-[#EFF0F6] bg-white hover:border-gray-300'"
                                                     class="shrink-0 p-2.5 rounded-2xl border cursor-pointer flex items-center gap-2.5 min-w-[160px] max-w-[200px] transition-all select-none">
                                                    <!-- Addon Thumbnail -->
                                                    <div class="w-12 h-12 rounded-xl bg-gray-100 overflow-hidden shrink-0 flex items-center justify-center">
                                                        <img :src="a.image_url || modalProduct.main_image_url" :alt="a.name" class="w-full h-full object-cover">
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs font-bold text-[#1F1F39] truncate" x-text="a.name"></p>
                                                        <p class="text-xs font-black text-[#FF006B] mt-0.5" x-text="'+₹' + a.price"></p>
                                                    </div>
                                                    <input type="checkbox" name="addons[]" :value="a.id" x-model="selectedAddons" class="hidden">
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Special Instructions Section (Screenshot 5) -->
                        <div class="pt-2 border-t border-[#EFF0F6]">
                            <label class="block text-xs sm:text-sm font-bold text-[#1F1F39] mb-2">Special instructions</label>
                            <input type="text" name="instructions" x-model="modalInstructions" placeholder="Add note (extra mayo, cheese, etc.)"
                                   class="w-full text-xs p-3 rounded-2xl border border-[#EFF0F6] bg-[#F7F7FC] focus:bg-white focus:border-[#FF006B] focus:outline-none transition-all placeholder:text-gray-400">
                        </div>

                        <!-- Sticky Bottom Full-Width Pink Button (Screenshot 5) -->
                        <div class="pt-2">
                            <button type="submit" :disabled="addingToCart"
                                    class="w-full py-3.5 px-6 rounded-full bg-[#FF006B] hover:bg-[#E60060] text-white font-extrabold text-sm flex items-center justify-center gap-2 shadow-lg shadow-pink-500/25 active:scale-[0.99] transition-transform disabled:opacity-60 cursor-pointer">
                                <span x-show="addingToCart" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                                <i x-show="!addingToCart" data-lucide="shopping-bag" class="w-4 h-4 text-white"></i>
                                <span x-text="addingToCart ? 'Adding to Cart...' : 'Add to Cart'"></span>
                                <span x-show="!addingToCart">₹<span x-text="calculateModalTotal()"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>

    <!-- Mobile Sticky Bottom Navigation (FoodKing Reference Screenshots 1-5) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-[#EFF0F6] shadow-xl px-2 py-1.5">
        <div class="grid grid-cols-5 items-center text-center">
            <!-- 1. Home -->
            <a href="{{ route('home') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('home') ? 'text-[#FF006B] font-bold' : 'text-[#6E7191]' }}">
                <i data-lucide="home" class="w-5 h-5 mb-0.5"></i>
                <span class="text-[10px]">Home</span>
            </a>

            <!-- 2. Menu -->
            <a href="{{ route('menu.index') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('menu.*') ? 'text-[#FF006B] font-bold' : 'text-[#6E7191]' }}">
                <i data-lucide="file-text" class="w-5 h-5 mb-0.5"></i>
                <span class="text-[10px]">Menu</span>
            </a>

            <!-- 3. Elevated Circular Pink Cart Button -->
            <div class="flex justify-center -mt-6">
                <button type="button" @click="cartDrawerOpen = true" 
                        class="w-13 h-13 rounded-full bg-[#FF006B] hover:bg-[#E60060] text-white flex items-center justify-center shadow-lg shadow-pink-500/40 border-4 border-white active:scale-95 transition-transform relative">
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-white"></i>
                    <span class="absolute -top-1 -right-1 bg-white text-[#FF006B] text-[10px] font-extrabold w-4 h-4 rounded-full flex items-center justify-center border-2 border-[#FF006B] shadow-xs"
                          x-text="cartCount" x-show="cartCount > 0"></span>
                </button>
            </div>

            <!-- 4. Offers -->
            <a href="{{ route('offers.index') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('offers.*') ? 'text-[#FF006B] font-bold' : 'text-[#6E7191]' }}">
                <i data-lucide="badge-percent" class="w-5 h-5 mb-0.5"></i>
                <span class="text-[10px]">Offers</span>
            </a>

            <!-- 5. Profile -->
            <a href="{{ auth()->check() ? route('account.profile') : route('login') }}" 
               class="flex flex-col items-center py-1 {{ (request()->routeIs('account.*') || request()->routeIs('login')) ? 'text-[#FF006B] font-bold' : 'text-[#6E7191]' }}">
                <i data-lucide="user" class="w-5 h-5 mb-0.5"></i>
                <span class="text-[10px]">Profile</span>
            </a>
        </div>
    </div>

    <!-- Redesigned Dark Navy Footer (Matching Reference Design & Connected to Settings) -->
    @include('layouts.partials.footer')

    @stack('scripts')
</body>
</html>
