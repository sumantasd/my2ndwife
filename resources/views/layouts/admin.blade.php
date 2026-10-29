<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') | My 2nd Wife Kitchen Manager</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/defaults/favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Prevent Alpine.js FOUC, ghost backdrops, and premature modal overlay rendering -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full text-[#25232A] font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="min-h-full flex">
        <!-- Sidebar Backdrop for Mobile -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak style="display: none;"
             class="fixed inset-0 z-40 bg-black/50 lg:hidden"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Sidebar Navigation -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-[#25232A] text-white flex flex-col transition-transform duration-300 transform lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            
            @php
                $adminLogoPath = \App\Models\RestaurantSetting::get('restaurant_logo_dark') 
                    ?: \App\Models\RestaurantSetting::get('restaurant_logo', 'images/defaults/logo.svg');
                $adminRestaurantName = \App\Models\RestaurantSetting::get('restaurant_name', 'My 2nd Wife');
                $adminLogoUrl = asset($adminLogoPath);
            @endphp
            <!-- Sidebar Header: Complete Official Logo Only -->
            <div class="h-20 flex items-center justify-between px-5 border-b border-gray-800 bg-[#1D1B22]">
                <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-start gap-1 min-w-0 flex-1 group" aria-label="{{ $adminRestaurantName }} Admin Portal">
                    <img src="{{ $adminLogoUrl }}" 
                         alt="{{ $adminRestaurantName }}" 
                         class="h-8 md:h-9 w-auto max-w-[170px] object-contain shrink-0 transition-transform group-hover:scale-[1.02]">
                    <span class="inline-block text-[9px] font-black uppercase tracking-[0.2em] text-[#FF3D96] bg-pink-500/10 px-2 py-0.5 rounded-full border border-pink-500/20 leading-none">
                        Admin Portal
                    </span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white shrink-0 p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Navigation Menu -->
            <div class="flex-1 overflow-y-auto px-4 py-6 space-y-1">
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-2">Core Operations</p>
                
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#F50070] text-white shadow-lg shadow-pink-500/20' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.orders.index') }}" 
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.orders.*') ? 'bg-[#F50070] text-white shadow-lg shadow-pink-500/20' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        <span>Orders</span>
                    </div>
                    @php $pendingCount = \App\Models\Order::where('order_status', 'placed')->count(); @endphp
                    @if($pendingCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#FF3D96] text-white animate-pulse">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>

                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-gray-500 mt-6 mb-2">Menu & Kitchen</p>

                <a href="{{ route('admin.categories.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.categories.*') ? 'bg-[#F50070] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="grid" class="w-4 h-4"></i>
                    <span>Food Categories</span>
                </a>

                <a href="{{ route('admin.products.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.products.*') ? 'bg-[#F50070] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="utensils" class="w-4 h-4"></i>
                    <span>Food Items</span>
                </a>

                <a href="{{ route('admin.addons.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.addons.*') ? 'bg-[#F50070] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    <span>Add-ons & Extras</span>
                </a>

                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-gray-500 mt-6 mb-2">Management</p>

                <a href="{{ route('admin.customers.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.customers.*') ? 'bg-[#F50070] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Customers</span>
                </a>

                <a href="{{ route('admin.coupons.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.coupons.*') ? 'bg-[#F50070] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="tag" class="w-4 h-4"></i>
                    <span>Coupons & Deals</span>
                </a>

                <a href="{{ route('admin.delivery.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.delivery.*') ? 'bg-[#F50070] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="bike" class="w-4 h-4"></i>
                    <span>Delivery Riders</span>
                </a>

                <a href="{{ route('admin.payments.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.payments.*') ? 'bg-[#F50070] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                    <span>Payment Records</span>
                </a>

                <a href="{{ route('admin.reports.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.reports.*') ? 'bg-[#F50070] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    <span>Sales Reports</span>
                </a>

                <a href="{{ route('admin.settings.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-[#F50070] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Restaurant Settings</span>
                </a>

                <a href="{{ route('admin.notifications.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.notifications.*') ? 'bg-[#F50070] text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="bell" class="w-4 h-4"></i>
                    <span>Push Notifications</span>
                </a>

                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-gray-500 mt-6 mb-2">Website Management</p>

                <a href="{{ route('admin.hero-banners.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ (request()->routeIs('admin.hero-banners.*') || request()->routeIs('admin.banners.*')) ? 'bg-[#F50070] text-white shadow-lg shadow-pink-500/20' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="image" class="w-4 h-4"></i>
                    <span>Homepage Banners</span>
                </a>

                <a href="{{ route('admin.pages.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.pages.*') ? 'bg-[#F50070] text-white shadow-lg shadow-pink-500/20' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Website Pages</span>
                </a>

                <a href="{{ route('admin.footer.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.footer.*') ? 'bg-[#F50070] text-white shadow-lg shadow-pink-500/20' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <i data-lucide="layout-template" class="w-4 h-4"></i>
                    <span>Footer Settings</span>
                </a>
            </div>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-gray-800 bg-gray-900/50">
                <a href="{{ route('home') }}" target="_blank" 
                   class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-xs font-bold text-gray-200 transition-colors">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>View Customer Site</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 lg:pl-64 flex flex-col min-h-screen min-w-0">
            
            <!-- Top Navigation Header with Real-Time Notification System -->
            <header class="h-20 bg-white border-b border-[#F0E6EC] flex items-center justify-between px-4 sm:px-8 sticky top-0 z-30"
                    x-data="adminNotificationSystem()">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-gray-100">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <div>
                        <h1 class="text-lg md:text-xl font-extrabold text-[#25232A]">@yield('header_title', 'Dashboard')</h1>
                        <p class="text-xs text-gray-500 hidden sm:block">Real-time management for My 2nd Wife Food Delivery</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- Kitchen Status Indicator -->
                    @php $isOpen = \App\Models\RestaurantSetting::get('is_restaurant_open', true); @endphp
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold border {{ $isOpen ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                        <span class="w-2 h-2 rounded-full {{ $isOpen ? 'bg-emerald-500' : 'bg-rose-500 animate-ping' }}"></span>
                        <span>{{ $isOpen ? 'Kitchen OPEN' : 'Kitchen CLOSED' }}</span>
                    </div>

                    <!-- Audio Chime Mute/Unmute & Volume Control -->
                    <div class="relative" x-data="{ volOpen: false }">
                        <div class="flex items-center gap-1">
                            <button type="button" 
                                    @click="toggleMute()" 
                                    :title="muted ? 'Sound Notifications Muted (Click to Unmute)' : (isPlayingRingtone ? 'Ringtone Playing! Click to Silence' : 'Sound Notifications Active (Click to Mute)')"
                                    class="p-2.5 rounded-xl border transition-all flex items-center justify-center cursor-pointer relative"
                                    :class="muted ? 'bg-gray-100 text-gray-400 border-gray-200 hover:text-gray-700' : (isPlayingRingtone ? 'bg-[#F50070] text-white border-[#F50070] shadow-md shadow-pink-500/30 animate-pulse' : 'bg-[#FFF0F6] text-[#F50070] border-[#F50070]/30 hover:bg-[#F50070] hover:text-white')">
                                <!-- Speaker Icon when active -->
                                <svg x-show="!muted" class="w-4 h-4 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                                <!-- Speaker Muted Icon -->
                                <svg x-show="muted" x-cloak class="w-4 h-4 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>
                                
                                <!-- Active ringtone indicator dot -->
                                <template x-if="isPlayingRingtone">
                                    <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-white rounded-full animate-ping"></span>
                                </template>
                            </button>

                            <!-- Volume Adjuster Popover Trigger -->
                            <button type="button" 
                                    @click="volOpen = !volOpen" 
                                    title="Notification Sound Volume Settings"
                                    class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors hidden sm:block">
                                <svg class="w-3.5 h-3.5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M12 20v-6M6 20V10M18 20V4"></path></svg>
                            </button>
                        </div>

                        <!-- Volume Slider Dropdown -->
                        <div x-show="volOpen" x-cloak @click.outside="volOpen = false"
                             class="absolute right-0 mt-2 p-3 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 w-52 space-y-2 text-xs">
                            <div class="flex items-center justify-between text-gray-700 font-bold">
                                <span>Alert Volume</span>
                                <span class="text-[#F50070] font-mono" x-text="Math.round(volume * 100) + '%'"></span>
                            </div>
                            <input type="range" min="0.1" max="1.0" step="0.05" :value="volume" 
                                   @input="setVolume($event.target.value)"
                                   class="w-full accent-[#F50070] cursor-pointer">
                            <div class="flex justify-between text-[10px] text-gray-400 font-medium">
                                <span>Quiet</span>
                                <span>Loud</span>
                            </div>
                        </div>
                    </div>

                    <!-- Real-Time Notification Bell & Dropdown Panel -->
                    <div class="relative">
                        <button type="button" 
                                @click="panelOpen = !panelOpen; if(panelOpen) stopRingtone();" 
                                title="Recent Order Notifications"
                                class="relative p-2.5 rounded-xl border border-gray-200 hover:border-[#F50070] hover:bg-[#FFF0F6] text-gray-600 hover:text-[#F50070] transition-colors cursor-pointer">
                            <svg class="w-4 h-4 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                            <!-- Badge -->
                            <template x-if="unreadCount > 0">
                                <span class="absolute -top-1.5 -right-1.5 px-1.5 py-0.5 min-w-[18px] text-[10px] font-black leading-none text-white bg-[#F50070] rounded-full shadow-md animate-pulse text-center"
                                      x-text="unreadCount">
                                </span>
                            </template>
                        </button>

                        <!-- Notification Dropdown Panel -->
                        <div x-show="panelOpen" x-cloak @click.outside="panelOpen = false"
                             class="absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-3xl shadow-2xl border border-[#F0E6EC] z-50 overflow-hidden text-xs divide-y divide-gray-100"
                             style="display: none;">
                            <div class="p-4 bg-gray-50 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-[#25232A] text-sm">Order Alerts</span>
                                    <template x-if="unreadCount > 0">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#F50070] text-white" x-text="unreadCount + ' new'"></span>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2">
                                    <template x-if="desktopPermission !== 'granted' && desktopPermission !== 'unsupported'">
                                        <button type="button" @click="requestDesktopPermission()" class="text-[10px] font-bold text-[#F50070] hover:underline">
                                            Enable Desktop Alerts
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                                <template x-if="recentAlerts.length === 0">
                                    <div class="p-6 text-center text-gray-400">
                                        No recent orders received yet.
                                    </div>
                                </template>

                                <template x-for="item in recentAlerts" :key="item.id">
                                    <a :href="item.view_url" class="p-3.5 flex items-start justify-between gap-3 hover:bg-pink-50/50 transition-colors block">
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-2">
                                                <span class="font-extrabold text-[#25232A]" x-text="'#' + item.order_number"></span>
                                                <span class="px-1.5 py-0.5 text-[9px] font-bold rounded uppercase"
                                                      :class="item.order_type === 'pickup' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'"
                                                      x-text="item.order_type_label"></span>
                                            </div>
                                            <p class="text-gray-600 font-medium" x-text="item.customer_name"></p>
                                            <span class="text-[10px] text-gray-400" x-text="item.time"></span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-black text-[#F50070] block" x-text="item.total_formatted"></span>
                                            <span class="text-[10px] font-bold text-gray-500 uppercase" x-text="item.status_label"></span>
                                        </div>
                                    </a>
                                </template>
                            </div>

                            <div class="p-3 bg-gray-50 text-center">
                                <a href="{{ route('admin.orders.index') }}" class="font-bold text-[#F50070] hover:underline text-xs">
                                    View All Orders &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center gap-2.5 sm:gap-3 pl-2 sm:pl-3 border-l border-gray-200">
                        <div class="w-9 h-9 rounded-full bg-[#F50070] text-white flex items-center justify-center text-sm font-extrabold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="hidden md:block text-left">
                            <span class="block text-xs font-bold text-[#25232A]">{{ auth()->user()->name }}</span>
                            <span class="block text-[10px] text-gray-500 uppercase">{{ auth()->user()->role }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" title="Logout" class="p-2 rounded-full hover:bg-rose-50 text-gray-500 hover:text-rose-600 transition-colors cursor-pointer">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Live Floating Popups for New Orders (Every Admin Page) -->
                <div class="fixed top-20 right-4 sm:right-8 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full">
                    
                    <!-- Audio Autoplay Blocked Notice -->
                    <template x-if="audioBlocked">
                        <div @click="enableAudio()" 
                             class="pointer-events-auto bg-amber-500 hover:bg-amber-600 text-white p-3.5 rounded-2xl shadow-xl flex items-center justify-between text-xs font-bold cursor-pointer transition-all animate-pulse">
                            <div class="flex items-center gap-2">
                                <span class="text-sm">🔇</span>
                                <span>Audio paused by browser — Click to enable sound</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-lg bg-amber-700 text-white text-[10px] uppercase font-black shrink-0">Enable</span>
                        </div>
                    </template>

                    <template x-for="popup in activePopups" :key="popup.id">
                        <div class="pointer-events-auto bg-white rounded-3xl p-5 border-2 border-[#F50070] shadow-2xl space-y-3 transition-all animate-bounce-once">
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#F50070] animate-ping"></span>
                                    <span class="font-black text-sm text-[#25232A] flex items-center gap-1.5">
                                        <span>🔔</span>
                                        <span>New Order Received!</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <!-- Stop Sound Button while ringing -->
                                    <template x-if="isPlayingRingtone">
                                        <button type="button" @click="stopRingtone()" 
                                                class="px-2 py-0.5 rounded-lg bg-pink-100 hover:bg-pink-200 text-[#F50070] text-[10px] font-bold flex items-center gap-1 transition-colors cursor-pointer"
                                                title="Silence Alert">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#F50070] animate-ping"></span>
                                            <span>Stop Sound</span>
                                        </button>
                                    </template>
                                    <button type="button" @click="dismissPopup(popup.id)" class="text-gray-400 hover:text-gray-700 p-1 cursor-pointer">
                                        ✕
                                    </button>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 space-y-1.5 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono font-extrabold text-[#25232A]" x-text="'#' + popup.order_number"></span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase"
                                          :class="popup.order_type === 'pickup' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200'"
                                          x-text="popup.order_type_label">
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-gray-600">
                                    <span class="font-bold text-[#25232A]" x-text="popup.customer_name"></span>
                                    <span class="font-black text-[#F50070] text-sm" x-text="popup.total_formatted"></span>
                                </div>
                                <div class="text-[10px] text-gray-400 flex items-center justify-between">
                                    <span x-text="popup.customer_phone"></span>
                                    <span x-text="popup.created_at_human"></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <a :href="popup.view_url" 
                                   @click="stopRingtone()"
                                   class="flex-1 py-2 px-3 text-center rounded-xl bg-gray-100 hover:bg-gray-200 text-[#25232A] text-xs font-bold transition-colors">
                                    View Order
                                </a>

                                <button type="button" 
                                        @click="acceptFromPopup(popup)"
                                        :disabled="popup.accepting || popup.accepted"
                                        class="flex-1 py-2 px-3 text-center rounded-xl bg-[#F50070] hover:bg-[#D80063] text-white text-xs font-bold transition-colors shadow-sm disabled:opacity-60 cursor-pointer">
                                    <span x-show="!popup.accepting && !popup.accepted">✓ Accept Order</span>
                                    <span x-show="popup.accepting" class="inline-flex items-center gap-1">
                                        <span class="animate-spin inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span>
                                        <span>Accepting...</span>
                                    </span>
                                    <span x-show="popup.accepted">✓ Accepted!</span>
                                </button>
                            </div>

                        </div>
                    </template>
                </div>
            </header>

            <!-- Alerts -->
            <div class="px-4 sm:px-8 pt-6">
                @if(session('success'))
                    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center justify-between text-sm shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-2 font-medium">
                            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-600 hover:text-emerald-900"><i data-lucide="x" class="w-4 h-4"></i></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center justify-between text-sm shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-2 font-medium">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-rose-600 hover:text-rose-900"><i data-lucide="x" class="w-4 h-4"></i></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-sm shadow-sm">
                        <p class="font-bold mb-1">Please correct the following errors:</p>
                        <ul class="list-disc list-inside text-xs space-y-0.5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <main class="flex-1 px-4 sm:px-8 pb-12 w-full max-w-full min-w-0">
                @yield('content')
            </main>

            <!-- Admin Panel Footer with Permanent Hardcoded Tech Googly Ownership Credit -->
            <footer class="mt-auto bg-white border-t border-[#F0E6EC] py-4 px-4 sm:px-8 text-xs text-gray-500"
                    x-data="{ 
                        techGooglyModalOpen: false,
                        init() {
                            this.$watch('techGooglyModalOpen', val => {
                                document.body.classList.toggle('overflow-hidden', val);
                            });
                        }
                    }">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2.5 text-center sm:text-left">
                    <p class="text-[11px] text-gray-400">
                        &copy; {{ date('Y') }} <strong class="text-gray-700">My 2nd Wife</strong>. Kitchen & Delivery Management.
                    </p>

                    <!-- Clickable Permanent Tech Googly Ownership Credit Link -->
                    <button type="button" 
                            @click="techGooglyModalOpen = true"
                            class="inline-flex items-center gap-1.5 text-[11px] sm:text-xs font-semibold text-gray-500 hover:text-[#F50070] transition-colors cursor-pointer group focus:outline-none focus:ring-2 focus:ring-[#F50070]/30 rounded-lg px-2 py-1 -my-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#F50070] opacity-75 group-hover:opacity-100 group-hover:scale-125 transition-all shrink-0"></span>
                        <span class="group-hover:underline underline-offset-4 decoration-[#F50070]/50">
                            Crafted & Developed by Tech Googly | Proprietary Code & Rights
                        </span>
                    </button>
                </div>

                <!-- Permanent Tech Googly Proprietary Rights Modal -->
                <div x-show="techGooglyModalOpen" 
                     x-cloak 
                     style="display: none;"
                     @keydown.escape.window="techGooglyModalOpen = false"
                     class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
                     role="dialog" 
                     aria-modal="true" 
                     aria-labelledby="tech-googly-modal-title"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95">

                    <!-- Modal Card Container -->
                    <div @click.outside="techGooglyModalOpen = false" 
                         class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-[#F0E6EC] overflow-hidden max-h-[90vh] flex flex-col my-auto text-left">
                        
                        <!-- Modal Header -->
                        <div class="p-5 sm:p-6 bg-gradient-to-r from-gray-900 via-[#1A1D2A] to-gray-900 text-white flex items-start justify-between gap-4 border-b border-gray-800 shrink-0">
                            <div class="flex items-start gap-3.5 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#F50070] to-[#FF3D96] flex items-center justify-center text-white shadow-md shadow-pink-500/30 shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 2.18l7 3.12v4.7c0 4.67-3.13 9.04-7 10.18-3.87-1.14-7-5.51-7-10.18V6.3l7-3.12zm-1 5v2h2v-2h-2zm0 4v4h2v-4h-2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 id="tech-googly-modal-title" class="font-extrabold text-sm sm:text-base text-white tracking-tight leading-snug">
                                        Software Development & Proprietary Rights Notice
                                    </h3>
                                    <p class="text-[10px] sm:text-[11px] text-pink-400 font-semibold tracking-wider uppercase mt-1">
                                        Tech Googly Legal & Architecture Notice
                                    </p>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="techGooglyModalOpen = false" 
                                    class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-gray-300 hover:text-white flex items-center justify-center transition-colors shrink-0 mt-0.5"
                                    aria-label="Close dialog">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <!-- Modal Body (Scrollable if viewport is small) -->
                        <div class="p-6 sm:p-7 overflow-y-auto space-y-4 text-xs sm:text-sm leading-relaxed text-[#25232A]">
                            <p class="text-gray-700 leading-relaxed font-normal">
                                This software system has been designed and developed by <strong>Tech Googly</strong>. All rights, title, and interest in the original source code, custom-developed modules, system architecture, and proprietary components of this software are reserved exclusively by Tech Googly, subject to applicable law, contractual agreements, and third-party license terms.
                            </p>

                            <div class="p-4 rounded-2xl bg-pink-50/60 border border-pink-200/80 text-[11px] sm:text-xs text-gray-800 space-y-1.5 leading-relaxed">
                                <p class="font-semibold text-[#F50070] flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                    Restrictions & Protection
                                </p>
                                <p>
                                    Unauthorized copying, reproduction, redistribution, resale, or commercial exploitation of Tech Googly's proprietary code and components without prior written authorization is prohibited, subject to applicable law and contractual rights.
                                </p>
                            </div>

                            <div class="pt-2 flex items-center justify-between text-xs text-gray-500 font-semibold border-t border-gray-100">
                                <span>&copy; 2026 Tech Googly. All Rights Reserved.</span>
                                <a href="https://techgoogly.com" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="text-[#F50070] hover:text-[#D40060] font-bold hover:underline inline-flex items-center gap-1">
                                    <span>techgoogly.com</span>
                                    <svg class="w-3.5 h-3.5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                </a>
                            </div>
                        </div>

                        <!-- Modal Footer Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-3 shrink-0">
                            <a href="https://techgoogly.com" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white font-bold text-xs shadow-sm transition-all hover:shadow">
                                <span>Visit Tech Googly</span>
                                <svg class="w-3.5 h-3.5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            </a>

                            <button type="button" 
                                    @click="techGooglyModalOpen = false" 
                                    class="px-5 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold text-xs transition-colors focus:outline-none focus:ring-2 focus:ring-gray-400">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script>
        function adminNotificationSystem() {
            return {
                muted: localStorage.getItem('m2w_admin_sound_muted') === 'true',
                volume: parseFloat(localStorage.getItem('m2w_admin_sound_volume') || '0.75'),
                panelOpen: false,
                unreadCount: 0,
                lastOrderId: 0,
                activePopups: [],
                alertedOrderIds: new Set(),
                recentAlerts: [],
                desktopPermission: ('Notification' in window) ? Notification.permission : 'unsupported',
                audioBlocked: false,
                isPlayingRingtone: false,
                activeAudioCtx: null,
                ringtoneTimer: null,

                init() {
                    // Unlock browser audio context on first user interaction anywhere on page
                    const unlockAudio = () => {
                        if (this.audioBlocked) {
                            this.enableAudio();
                        }
                    };
                    window.addEventListener('click', unlockAudio, { passive: true });
                    window.addEventListener('keydown', unlockAudio, { passive: true });

                    this.fetchAlerts(true);
                    setInterval(() => {
                        this.fetchAlerts(false);
                    }, 5000);
                },

                setVolume(val) {
                    this.volume = parseFloat(val);
                    localStorage.setItem('m2w_admin_sound_volume', this.volume);
                    this.playTestDing();
                },

                playTestDing() {
                    if (this.muted) return;
                    try {
                        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                        if (!AudioContextClass) return;
                        const ctx = new AudioContextClass();
                        const osc = ctx.createOscillator();
                        const noteGain = ctx.createGain();
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(880, ctx.currentTime);
                        noteGain.gain.setValueAtTime(0.001, ctx.currentTime);
                        noteGain.gain.exponentialRampToValueAtTime(this.volume * 0.85, ctx.currentTime + 0.02);
                        noteGain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.35);
                        osc.connect(noteGain);
                        noteGain.connect(ctx.destination);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.35);
                        setTimeout(() => ctx.close().catch(() => {}), 400);
                    } catch (e) {}
                },

                toggleMute() {
                    this.muted = !this.muted;
                    localStorage.setItem('m2w_admin_sound_muted', this.muted);
                    if (this.muted) {
                        this.stopRingtone();
                    } else {
                        this.playTestDing();
                    }
                },

                enableAudio() {
                    this.audioBlocked = false;
                    if (this.activePopups.length > 0) {
                        this.startRingtone();
                    } else {
                        this.playTestDing();
                    }
                },

                async requestDesktopPermission() {
                    if ('Notification' in window) {
                        const perm = await Notification.requestPermission();
                        this.desktopPermission = perm;
                        if (perm === 'granted') {
                            new Notification('My 2nd Wife Admin', {
                                body: 'Desktop notifications enabled for new incoming orders.',
                                icon: '/images/defaults/favicon.svg'
                            });
                        }
                    }
                },

                startRingtone() {
                    if (this.muted) return;

                    // Immediately stop and teardown any existing ringtone to avoid overlaps
                    this.stopRingtone();

                    try {
                        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                        if (!AudioContextClass) return;

                        const ctx = new AudioContextClass();
                        this.activeAudioCtx = ctx;

                        const runSequence = () => {
                            if (!this.activeAudioCtx || this.muted) return;
                            this.audioBlocked = false;
                            this.isPlayingRingtone = true;

                            const masterGain = ctx.createGain();
                            masterGain.gain.setValueAtTime(this.volume, ctx.currentTime);
                            masterGain.connect(ctx.destination);

                            const playTone = (freq, startTime, duration, type = 'triangle', peak = 0.85) => {
                                if (!this.activeAudioCtx) return;
                                const osc = ctx.createOscillator();
                                const noteGain = ctx.createGain();
                                osc.type = type;
                                osc.frequency.setValueAtTime(freq, startTime);

                                // Acoustic chime envelope: crisp attack & natural decay
                                noteGain.gain.setValueAtTime(0.001, startTime);
                                noteGain.gain.exponentialRampToValueAtTime(peak, startTime + 0.02);
                                noteGain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

                                osc.connect(noteGain);
                                noteGain.connect(masterGain);

                                osc.start(startTime);
                                osc.stop(startTime + duration);
                            };

                            // Sequence of 5 repeating two-tone "Ding-Dong" chimes across ~6.2 seconds
                            const cycles = 5;
                            const cycleInterval = 1.35; // seconds between Ding-Dongs
                            const baseTime = ctx.currentTime + 0.05;

                            for (let i = 0; i < cycles; i++) {
                                const cStart = baseTime + (i * cycleInterval);
                                // "Ding" (A5 - 880 Hz) with harmonic overtone (A6 - 1760 Hz)
                                playTone(880, cStart, 0.40, 'triangle', 0.85);
                                playTone(1760, cStart, 0.22, 'sine', 0.25);

                                // "Dong" (D5 - 587.33 Hz) with warm lower body
                                playTone(587.33, cStart + 0.28, 0.65, 'sine', 0.80);
                                playTone(1174.66, cStart + 0.28, 0.35, 'triangle', 0.20);
                            }

                            // Auto cleanup after 6.6 seconds
                            this.ringtoneTimer = setTimeout(() => {
                                this.stopRingtone();
                            }, 6600);
                        };

                        if (ctx.state === 'suspended') {
                            ctx.resume().then(() => {
                                runSequence();
                            }).catch(() => {
                                this.audioBlocked = true;
                                this.stopRingtone();
                            });
                        } else {
                            runSequence();
                        }
                    } catch (e) {
                        this.audioBlocked = true;
                        this.stopRingtone();
                    }
                },

                stopRingtone() {
                    if (this.ringtoneTimer) {
                        clearTimeout(this.ringtoneTimer);
                        this.ringtoneTimer = null;
                    }

                    if (this.activeAudioCtx) {
                        try {
                            this.activeAudioCtx.close().catch(() => {});
                        } catch (e) {}
                        this.activeAudioCtx = null;
                    }

                    this.isPlayingRingtone = false;
                },

                showDesktopNotification(order) {
                    if ('Notification' in window && Notification.permission === 'granted') {
                        try {
                            const notif = new Notification(`New ${order.order_type_label} Order #${order.order_number}`, {
                                body: `${order.customer_name} placed an order for ${order.total_formatted}. Click to view.`,
                                icon: '/images/defaults/favicon.svg',
                                tag: 'order-' + order.id
                            });
                            notif.onclick = () => {
                                window.focus();
                                this.stopRingtone();
                                window.location.href = order.view_url;
                            };
                        } catch (e) {
                            // Desktop notification failure
                        }
                    }
                },

                async fetchAlerts(isInitial = false) {
                    try {
                        const url = new URL('{{ route('admin.orders.live-alerts') }}', window.location.origin);
                        if (isInitial) {
                            url.searchParams.set('initial', '1');
                        } else if (this.lastOrderId >= 0) {
                            url.searchParams.set('since_id', this.lastOrderId);
                        }

                        const res = await fetch(url.toString(), {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!res.ok) return;
                        const data = await res.json();

                        if (isInitial) {
                            this.lastOrderId = data.latest_id || 0;
                            this.unreadCount = data.placed_count || 0;
                            this.recentAlerts = data.recent_alerts || [];
                            return;
                        }

                        if (data.new_orders && data.new_orders.length > 0) {
                            let hasNewAlert = false;

                            data.new_orders.forEach(order => {
                                if (!this.alertedOrderIds.has(order.id)) {
                                    this.alertedOrderIds.add(order.id);
                                    this.activePopups.push({
                                        ...order,
                                        accepted: false,
                                        accepting: false
                                    });
                                    this.showDesktopNotification(order);
                                    hasNewAlert = true;

                                    // Auto dismiss after 25 seconds
                                    setTimeout(() => {
                                        this.dismissPopup(order.id);
                                    }, 25000);
                                }
                            });

                            if (hasNewAlert) {
                                this.startRingtone();
                            }
                        }

                        this.lastOrderId = Math.max(this.lastOrderId, data.latest_id || 0);
                        this.unreadCount = data.placed_count || 0;
                        if (data.recent_alerts) {
                            this.recentAlerts = data.recent_alerts;
                        }
                    } catch (err) {
                        // Silently retry on next tick
                    }
                },

                dismissPopup(orderId) {
                    this.activePopups = this.activePopups.filter(p => p.id !== orderId);
                    this.stopRingtone();
                },

                async acceptFromPopup(popup) {
                    this.stopRingtone();

                    if (popup.accepting || popup.accepted) return;
                    popup.accepting = true;

                    try {
                        const res = await fetch(popup.accept_url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({
                                status: 'accepted',
                                comment: 'Accepted directly from live notification alert'
                            })
                        });

                        const data = await res.json();
                        if (data.success) {
                            popup.accepted = true;
                            popup.accepting = false;
                            setTimeout(() => {
                                this.dismissPopup(popup.id);
                            }, 1500);
                        } else {
                            alert(data.message || 'Failed to accept order.');
                            popup.accepting = false;
                        }
                    } catch (e) {
                        popup.accepting = false;
                        alert('Network error accepting order.');
                    }
                }
            };
        }
    </script>

    @stack('scripts')
</body>
</html>
