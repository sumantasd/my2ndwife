<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F7F7FC]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Rider Portal') | My 2nd Wife Delivery</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset(\App\Models\RestaurantSetting::get('restaurant_favicon', 'images/defaults/favicon.svg')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Prevent Alpine FOUC -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Ensure smooth safe area padding for modern iPhones */
        .safe-bottom {
            padding-bottom: max(env(safe-area-inset-bottom, 0px), 8px);
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full text-[#202033] font-sans antialiased bg-[#F7F7FC] flex flex-col selection:bg-[#FFF0F6] selection:text-[#FF006E]">

    @php
        $logoPath = \App\Models\RestaurantSetting::get('restaurant_logo', 'images/defaults/logo.svg');
        $logoUrl = asset($logoPath);
        $restaurantName = \App\Models\RestaurantSetting::get('restaurant_name', 'My 2nd Wife');
        $activeOrdersCount = Auth::check() ? \App\Models\Order::where('delivery_person_id', Auth::id())
            ->whereIn('order_status', [\App\Models\Order::STATUS_READY, \App\Models\Order::STATUS_OUT_FOR_DELIVERY])
            ->count() : 0;
        $headerUser = Auth::user();
        $headerAvatar = ($headerUser && !empty($headerUser->avatar))
            ? (str_starts_with($headerUser->avatar, 'http') ? $headerUser->avatar : asset('storage/' . ltrim($headerUser->avatar, '/')))
            : null;
        $headerInitials = strtoupper(substr($headerUser->name ?? 'R', 0, 1));
    @endphp

    <!-- Top Navigation Bar (Polished, non-cramped header) -->
    <header class="sticky top-0 z-40 bg-white border-b border-[#E8E5EF] shadow-2xs">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-15 sm:h-16 flex items-center justify-between gap-2 sm:gap-4">
            
            <!-- Official Website Logo & Rider Badge -->
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                <a href="{{ route('delivery.dashboard') }}" class="flex items-center gap-1.5 sm:gap-2 transition-opacity hover:opacity-90">
                    <img src="{{ $logoUrl }}" 
                         alt="{{ $restaurantName }}" 
                         class="h-7 sm:h-8 max-h-8 w-auto max-w-[105px] sm:max-w-[150px] object-contain shrink-0">
                    <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-black uppercase tracking-wider bg-[#FFF0F6] text-[#FF006E] border border-[#FEE2EB] shrink-0">
                        Rider
                    </span>
                </a>
            </div>

            <!-- Right Controls: Duty Status Pill & Desktop Links & Avatar -->
            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <!-- Duty Status Toggle Form -->
                <form action="{{ route('delivery.duty.toggle') }}" method="POST" class="inline shrink-0">
                    @csrf
                    <button type="submit" 
                            title="Click to toggle Online/Offline duty status"
                            class="px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full text-[10px] sm:text-xs font-black flex items-center gap-1.5 border transition-all shadow-2xs active:scale-95 cursor-pointer shrink-0 {{ $headerUser && $headerUser->is_online ? 'bg-emerald-50 text-[#00B884] border-emerald-200 hover:bg-emerald-100' : 'bg-amber-50 text-[#D97706] border-amber-200 hover:bg-amber-100' }}">
                        <span class="relative flex h-2 w-2 shrink-0">
                            @if($headerUser && $headerUser->is_online)
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#00B884]"></span>
                            @else
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#F5A623]"></span>
                            @endif
                        </span>
                        <span class="hidden sm:inline font-extrabold">{{ $headerUser && $headerUser->is_online ? 'Duty: Online' : 'Duty: Offline' }}</span>
                        <span class="sm:hidden font-extrabold">{{ $headerUser && $headerUser->is_online ? 'Online' : 'Offline' }}</span>
                    </button>
                </form>

                <!-- Desktop Nav Links -->
                <nav class="hidden lg:flex items-center gap-1 ml-1 border-l border-[#E8E5EF] pl-3 text-xs font-bold">
                    <a href="{{ route('delivery.dashboard') }}" 
                       class="px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('delivery.dashboard') ? 'bg-[#FFF0F6] text-[#FF006E] font-black' : 'text-[#55597D] hover:text-[#202033] hover:bg-gray-50' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('delivery.orders.index') }}" 
                       class="px-3 py-2 rounded-xl transition-colors flex items-center gap-1.5 {{ request()->routeIs('delivery.orders.*') ? 'bg-[#FFF0F6] text-[#FF006E] font-black' : 'text-[#55597D] hover:text-[#202033] hover:bg-gray-50' }}">
                        <span>Assigned Orders</span>
                        @if($activeOrdersCount > 0)
                            <span class="w-4 h-4 rounded-full bg-[#FF006E] text-white text-[9px] font-black flex items-center justify-center">
                                {{ $activeOrdersCount }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('delivery.available.index') }}" 
                       class="px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('delivery.available.*') ? 'bg-[#FFF0F6] text-[#FF006E] font-black' : 'text-[#55597D] hover:text-[#202033] hover:bg-gray-50' }}">
                        Available Orders
                    </a>
                    <a href="{{ route('delivery.earnings') }}" 
                       class="px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('delivery.earnings') ? 'bg-[#FFF0F6] text-[#FF006E] font-black' : 'text-[#55597D] hover:text-[#202033] hover:bg-gray-50' }}">
                        Earnings
                    </a>
                    <a href="{{ route('delivery.history') }}" 
                       class="px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('delivery.history') ? 'bg-[#FFF0F6] text-[#FF006E] font-black' : 'text-[#55597D] hover:text-[#202033] hover:bg-gray-50' }}">
                        History
                    </a>
                </nav>

                <!-- Rider Avatar & Logout -->
                <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                    <a href="{{ route('delivery.profile') }}" 
                       class="flex items-center gap-1.5 p-0.5 rounded-full hover:bg-gray-100 transition-colors shrink-0" 
                       title="View Profile">
                        @if($headerAvatar)
                            <img src="{{ $headerAvatar }}" alt="{{ $headerUser->name }}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover border border-[#E8E5EF] shadow-2xs shrink-0">
                        @else
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-br from-[#FF006E] to-[#E60063] text-white font-black text-[11px] sm:text-xs flex items-center justify-center shadow-2xs shrink-0 border border-white ring-1 ring-[#FF006E]/20">
                                {{ $headerInitials }}
                            </div>
                        @endif
                        <span class="hidden xl:inline text-xs font-bold text-[#202033] max-w-[100px] truncate">{{ $headerUser->name }}</span>
                    </a>

                    <form action="{{ route('delivery.logout') }}" method="POST" class="inline shrink-0">
                        @csrf
                        <button type="submit" 
                                title="Sign Out from Rider Portal"
                                class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center text-[#667085] hover:text-[#E5484D] hover:bg-rose-50 transition-colors cursor-pointer shrink-0">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container (generous bottom padding pb-36 sm:pb-40 lg:pb-12 to ensure content is NEVER obscured by fixed bottom navigation) -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-6 pb-36 sm:pb-40 lg:pb-12">
        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold flex items-center gap-3 shadow-2xs">
                <div class="w-6 h-6 rounded-full bg-[#00B884] text-white flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                </div>
                <div class="flex-1">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-bold flex items-center gap-3 shadow-2xs">
                <div class="w-6 h-6 rounded-full bg-[#E5484D] text-white flex items-center justify-center shrink-0">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                </div>
                <div class="flex-1">{{ session('error') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-5 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 text-xs font-bold flex items-center gap-3 shadow-2xs">
                <div class="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center shrink-0">
                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                </div>
                <div class="flex-1">{{ session('info') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile-First Fixed Bottom Navigation Bar (Equal 5-column grid layout, no label cutoffs, safe area support) -->
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-[#E8E5EF] shadow-[0_-2px_12px_rgba(32,32,51,0.06)] safe-bottom">
        <div class="grid grid-cols-5 h-[60px] sm:h-[64px] max-w-lg mx-auto w-full px-1">
            
            <!-- 1. Home / Dashboard -->
            <a href="{{ route('delivery.dashboard') }}" 
               class="h-full flex flex-col items-center justify-center py-1 relative text-center group transition-colors {{ request()->routeIs('delivery.dashboard') ? 'text-[#FF006E]' : 'text-[#667085] hover:text-[#202033]' }}">
                @if(request()->routeIs('delivery.dashboard'))
                    <span class="absolute top-0 inset-x-2 sm:inset-x-3 h-[2.5px] bg-[#FF006E] rounded-full"></span>
                @endif
                <span class="w-6 h-6 flex items-center justify-center shrink-0">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('delivery.dashboard') ? 'stroke-[#FF006E] stroke-[2.4]' : 'stroke-[#667085] stroke-[2]' }}"></i>
                </span>
                <span class="text-[10px] sm:text-[11px] leading-tight font-bold tracking-tight text-center w-full mt-0.5 {{ request()->routeIs('delivery.dashboard') ? 'text-[#FF006E] font-black' : 'text-[#667085]' }}">
                    Home
                </span>
            </a>

            <!-- 2. Assigned Orders -->
            <a href="{{ route('delivery.orders.index') }}" 
               class="h-full flex flex-col items-center justify-center py-1 relative text-center group transition-colors {{ request()->routeIs('delivery.orders.*') ? 'text-[#FF006E]' : 'text-[#667085] hover:text-[#202033]' }}">
                @if(request()->routeIs('delivery.orders.*'))
                    <span class="absolute top-0 inset-x-2 sm:inset-x-3 h-[2.5px] bg-[#FF006E] rounded-full"></span>
                @endif
                <span class="w-6 h-6 flex items-center justify-center shrink-0 relative">
                    <i data-lucide="package" class="w-5 h-5 {{ request()->routeIs('delivery.orders.*') ? 'stroke-[#FF006E] stroke-[2.4]' : 'stroke-[#667085] stroke-[2]' }}"></i>
                    @if($activeOrdersCount > 0)
                        <span class="absolute -top-1 -right-1.5 w-3.5 h-3.5 rounded-full bg-[#FF006E] text-white text-[8px] font-black flex items-center justify-center shadow-2xs">
                            {{ $activeOrdersCount }}
                        </span>
                    @endif
                </span>
                <span class="text-[10px] sm:text-[11px] leading-tight font-bold tracking-tight text-center w-full mt-0.5 {{ request()->routeIs('delivery.orders.*') ? 'text-[#FF006E] font-black' : 'text-[#667085]' }}">
                    Orders
                </span>
            </a>

            <!-- 3. Available Orders -->
            <a href="{{ route('delivery.available.index') }}" 
               class="h-full flex flex-col items-center justify-center py-1 relative text-center group transition-colors {{ request()->routeIs('delivery.available.*') ? 'text-[#FF006E]' : 'text-[#667085] hover:text-[#202033]' }}">
                @if(request()->routeIs('delivery.available.*'))
                    <span class="absolute top-0 inset-x-2 sm:inset-x-3 h-[2.5px] bg-[#FF006E] rounded-full"></span>
                @endif
                <span class="w-6 h-6 flex items-center justify-center shrink-0">
                    <i data-lucide="inbox" class="w-5 h-5 {{ request()->routeIs('delivery.available.*') ? 'stroke-[#FF006E] stroke-[2.4]' : 'stroke-[#667085] stroke-[2]' }}"></i>
                </span>
                <span class="text-[10px] sm:text-[11px] leading-tight font-bold tracking-tight text-center w-full mt-0.5 {{ request()->routeIs('delivery.available.*') ? 'text-[#FF006E] font-black' : 'text-[#667085]' }}">
                    Available
                </span>
            </a>

            <!-- 4. Earnings -->
            <a href="{{ route('delivery.earnings') }}" 
               class="h-full flex flex-col items-center justify-center py-1 relative text-center group transition-colors {{ request()->routeIs('delivery.earnings') ? 'text-[#FF006E]' : 'text-[#667085] hover:text-[#202033]' }}">
                @if(request()->routeIs('delivery.earnings'))
                    <span class="absolute top-0 inset-x-2 sm:inset-x-3 h-[2.5px] bg-[#FF006E] rounded-full"></span>
                @endif
                <span class="w-6 h-6 flex items-center justify-center shrink-0">
                    <i data-lucide="wallet" class="w-5 h-5 {{ request()->routeIs('delivery.earnings') ? 'stroke-[#FF006E] stroke-[2.4]' : 'stroke-[#667085] stroke-[2]' }}"></i>
                </span>
                <span class="text-[10px] sm:text-[11px] leading-tight font-bold tracking-tight text-center w-full mt-0.5 {{ request()->routeIs('delivery.earnings') ? 'text-[#FF006E] font-black' : 'text-[#667085]' }}">
                    Earnings
                </span>
            </a>

            <!-- 5. Profile -->
            <a href="{{ route('delivery.profile') }}" 
               class="h-full flex flex-col items-center justify-center py-1 relative text-center group transition-colors {{ request()->routeIs('delivery.profile') ? 'text-[#FF006E]' : 'text-[#667085] hover:text-[#202033]' }}">
                @if(request()->routeIs('delivery.profile'))
                    <span class="absolute top-0 inset-x-2 sm:inset-x-3 h-[2.5px] bg-[#FF006E] rounded-full"></span>
                @endif
                <span class="w-6 h-6 flex items-center justify-center shrink-0">
                    <i data-lucide="user" class="w-5 h-5 {{ request()->routeIs('delivery.profile') ? 'stroke-[#FF006E] stroke-[2.4]' : 'stroke-[#667085] stroke-[2]' }}"></i>
                </span>
                <span class="text-[10px] sm:text-[11px] leading-tight font-bold tracking-tight text-center w-full mt-0.5 {{ request()->routeIs('delivery.profile') ? 'text-[#FF006E] font-black' : 'text-[#667085]' }}">
                    Profile
                </span>
            </a>

        </div>
    </nav>

    <!-- Reliable Lucide Icons Initializer -->
    <script>
        function triggerLucide() {
            if (window.renderLucide) {
                window.renderLucide();
            } else if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        }
        document.addEventListener('DOMContentLoaded', triggerLucide);
        window.addEventListener('load', triggerLucide);
    </script>
    @stack('scripts')
</body>
</html>
