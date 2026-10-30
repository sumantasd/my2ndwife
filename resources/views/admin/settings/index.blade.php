@extends('layouts.admin')

@section('title', 'Restaurant Settings')
@section('header_title', 'Centralized Restaurant Settings')

@section('content')
<div class="space-y-6" x-data="restaurantSettings({
    activeTab: '{{ session('active_tab', 'general') }}',
    previewLogo: '{{ asset($settings['restaurant_logo'] ?? 'images/defaults/logo.svg') }}',
    previewFavicon: '{{ asset($settings['restaurant_favicon'] ?? 'images/defaults/favicon.svg') }}',
    previewCover: '{{ asset($settings['restaurant_cover_image'] ?? 'images/defaults/banner-placeholder.jpg') }}',
    footerSettingsUrl: '{{ route('admin.footer.index') }}'
})">

    <!-- Mobile Navigation Selector (Touch-friendly & Dropdown) -->
    <div class="lg:hidden bg-white p-3 rounded-2xl border border-[#F0E6EC] shadow-sm space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Settings Section</span>
            <span class="text-[11px] font-bold text-[#F50070] bg-[#FFF0F6] px-2 py-0.5 rounded-full" x-text="activeTab.replace('_', ' ').toUpperCase()"></span>
        </div>
        
        <!-- Mobile Section Button with Dropdown Trigger -->
        <button type="button" @click="mobileDropdownOpen = !mobileDropdownOpen" 
                class="w-full py-2.5 px-4 rounded-xl border border-[#F0E6EC] bg-gray-50 hover:bg-gray-100 flex items-center justify-between text-xs font-bold text-[#25232A] transition-all">
            <div class="flex items-center gap-2">
                <template x-if="activeTab === 'general'"><span>🍽️ General Information</span></template>
                <template x-if="activeTab === 'branding'"><span>🎨 Branding & Logos</span></template>
                <template x-if="activeTab === 'contact'"><span>📍 Contact & Address</span></template>
                <template x-if="activeTab === 'google_maps'"><span>🗺️ Google Maps</span></template>
                <template x-if="activeTab === 'delivery'"><span>🛵 Delivery Radius & Fees</span></template>
                <template x-if="activeTab === 'hours'"><span>⏰ Opening Hours</span></template>
                <template x-if="activeTab === 'orders'"><span>🧾 Taxes & Charges</span></template>
                <template x-if="activeTab === 'payment'"><span>💳 Payment Methods</span></template>
                <template x-if="activeTab === 'receipt'"><span>🖨️ Thermal Receipts</span></template>
                <template x-if="activeTab === 'notifications'"><span>🔔 Notifications</span></template>
                <template x-if="activeTab === 'seo'"><span>🌐 SEO & Social</span></template>
                <template x-if="activeTab === 'legal'"><span>⚖️ Legal & Policies</span></template>
                <template x-if="activeTab === 'banners'"><span>🖼️ Homepage Banners ({{ $banners->count() }})</span></template>
            </div>
            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform" :class="mobileDropdownOpen ? 'rotate-180' : ''"></i>
        </button>

        <!-- Dropdown Menu for Mobile -->
        <div x-show="mobileDropdownOpen" x-cloak @click.away="mobileDropdownOpen = false" 
             class="grid grid-cols-2 gap-1.5 p-2 bg-gray-50 rounded-xl border border-[#F0E6EC] text-xs">
            <button type="button" @click="switchTab('general')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'general' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>🍽️</span> General
            </button>
            <button type="button" @click="switchTab('branding')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'branding' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>🎨</span> Branding
            </button>
            <button type="button" @click="switchTab('contact')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'contact' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>📍</span> Contact
            </button>
            <button type="button" @click="switchTab('delivery')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'delivery' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>🛵</span> Delivery
            </button>
            <button type="button" @click="switchTab('google_maps')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'google_maps' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>🗺️</span> Maps
            </button>
            <button type="button" @click="switchTab('hours')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'hours' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>⏰</span> Hours
            </button>
            <button type="button" @click="switchTab('orders')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'orders' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>🧾</span> Taxes
            </button>
            <button type="button" @click="switchTab('payment')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'payment' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>💳</span> Payment
            </button>
            <button type="button" @click="switchTab('receipt')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'receipt' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>🖨️</span> Receipts
            </button>
            <button type="button" @click="switchTab('banners')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'banners' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>🖼️</span> Banners ({{ $banners->count() }})
            </button>
            <button type="button" @click="switchTab('notifications')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'notifications' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>🔔</span> Alerts
            </button>
            <button type="button" @click="switchTab('seo')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'seo' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>🌐</span> SEO
            </button>
            <button type="button" @click="switchTab('legal')" class="p-2 rounded-lg text-left font-bold flex items-center gap-1.5 transition-colors" :class="activeTab === 'legal' ? 'bg-[#F50070] text-white' : 'text-gray-700 hover:bg-white'">
                <span>⚖️</span> Policies
            </button>
        </div>
    </div>

    <!-- Main Responsive Layout: 2 Columns on Desktop (Sidebar Left + Content Right) -->
    <div class="lg:flex lg:items-start lg:gap-8">
        
        <!-- Categorized Desktop Settings Sidebar -->
        <aside class="hidden lg:block lg:w-72 shrink-0 bg-white rounded-3xl p-4 border border-[#F0E6EC] shadow-sm sticky top-24 space-y-5">
            <div>
                <p class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-2">Restaurant Identity</p>
                <div class="space-y-1">
                    <button type="button" @click="switchTab('general')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'general' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">🍽️</span> General Info
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" :class="activeTab === 'general' ? 'text-[#F50070]' : 'text-gray-300'"></i>
                    </button>

                    <button type="button" @click="switchTab('branding')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'branding' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">🎨</span> Branding & Logos
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" :class="activeTab === 'branding' ? 'text-[#F50070]' : 'text-gray-300'"></i>
                    </button>

                    <button type="button" @click="switchTab('contact')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'contact' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">📍</span> Contact & Address
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" :class="activeTab === 'contact' ? 'text-[#F50070]' : 'text-gray-300'"></i>
                    </button>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <p class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-2">Operations & Delivery</p>
                <div class="space-y-1">
                    <button type="button" @click="switchTab('delivery')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'delivery' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">🛵</span> Delivery Radius & Fees
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" :class="activeTab === 'delivery' ? 'text-[#F50070]' : 'text-gray-300'"></i>
                    </button>

                    <button type="button" @click="switchTab('google_maps')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'google_maps' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">🗺️</span> Google Maps
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" :class="activeTab === 'google_maps' ? 'text-[#F50070]' : 'text-gray-300'"></i>
                    </button>

                    <button type="button" @click="switchTab('hours')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'hours' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">⏰</span> Opening Hours
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" :class="activeTab === 'hours' ? 'text-[#F50070]' : 'text-gray-300'"></i>
                    </button>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <p class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-2">Finance & Orders</p>
                <div class="space-y-1">
                    <button type="button" @click="switchTab('orders')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'orders' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">🧾</span> Taxes & Charges
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" :class="activeTab === 'orders' ? 'text-[#F50070]' : 'text-gray-300'"></i>
                    </button>

                    <button type="button" @click="switchTab('payment')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'payment' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">💳</span> Payment Methods
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" :class="activeTab === 'payment' ? 'text-[#F50070]' : 'text-gray-300'"></i>
                    </button>

                    <button type="button" @click="switchTab('receipt')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'receipt' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">🖨️</span> Thermal Receipts
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" :class="activeTab === 'receipt' ? 'text-[#F50070]' : 'text-gray-300'"></i>
                    </button>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <p class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-2">Marketing & Content</p>
                <div class="space-y-1">
                    <button type="button" @click="switchTab('banners')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'banners' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">🖼️</span> Homepage Banners
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                              :class="activeTab === 'banners' ? 'bg-[#F50070] text-white' : 'bg-pink-100 text-[#F50070]'">
                            {{ $banners->count() }}
                        </span>
                    </button>

                    <button type="button" @click="switchTab('seo')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'seo' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">🌐</span> SEO & Social
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" :class="activeTab === 'seo' ? 'text-[#F50070]' : 'text-gray-300'"></i>
                    </button>

                    <button type="button" @click="switchTab('notifications')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'notifications' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">🔔</span> Notifications
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" :class="activeTab === 'notifications' ? 'text-[#F50070]' : 'text-gray-300'"></i>
                    </button>

                    <button type="button" @click="switchTab('legal')" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-bold text-left transition-all flex items-center justify-between"
                            :class="activeTab === 'legal' ? 'bg-[#FFF0F6] text-[#F50070] font-extrabold border-l-4 border-[#F50070] shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#25232A]'">
                        <span class="flex items-center gap-2.5">
                            <span class="text-sm">⚖️</span> Legal & Policies
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" :class="activeTab === 'legal' ? 'text-[#F50070]' : 'text-gray-300'"></i>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Right: Settings Section Content Area -->
        <div class="flex-1 min-w-0">

    <!-- TAB 1: GENERAL INFORMATION -->
    <div x-show="activeTab === 'general'" x-cloak class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs max-w-4xl">
            @csrf
            <input type="hidden" name="_section" value="general">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="font-extrabold text-[#25232A] text-base">General Restaurant Information & Identity</h3>
                <p class="text-gray-500 text-[11px]">Primary restaurant profile, logo branding, kitchen status, location coordinates, contact details, and currency configuration.</p>
            </div>

            <!-- Restaurant Open/Closed Status Card -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-pink-50/60 border border-pink-200 flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-[#25232A] text-xs">Accept New Orders (Online Kitchen)</h4>
                        <p class="text-[11px] text-gray-500">Toggle off to pause receiving orders on the frontend immediately.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_restaurant_open" value="1" {{ ($settings['is_restaurant_open'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>

                <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200 flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-[#25232A] text-xs">Temporarily Closed Toggle</h4>
                        <p class="text-[11px] text-gray-500">Displays custom banner for maintenance or private events.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_temporarily_closed" value="1" {{ ($settings['is_temporarily_closed'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                    </label>
                </div>
            </div>

            <!-- Brand Visuals: Logo & Favicon Upload -->
            <div class="p-5 rounded-2xl border border-pink-100 bg-[#FFF3F8]/40 space-y-4">
                <div class="flex items-center justify-between border-b border-pink-100 pb-3">
                    <div>
                        <h4 class="font-extrabold text-[#25232A] text-xs flex items-center gap-2">
                            <span>🎨</span> Restaurant Logo & Browser Favicon
                        </h4>
                        <p class="text-[11px] text-gray-500">Upload high-resolution logos and icons. Current logo is displayed below with real-time preview.</p>
                    </div>
                    <span class="text-[10px] font-bold text-[#F50070] bg-pink-100 px-2.5 py-1 rounded-full">Branding Assets</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Logo Upload Card -->
                    <div class="p-4 rounded-xl bg-white border border-[#F0E6EC] space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#25232A]">Restaurant Logo</span>
                            <span class="text-[10px] text-gray-400">Header & Public Navbar (SVG, PNG, JPG, WebP)</span>
                        </div>
                        <div class="h-20 bg-gray-50 rounded-xl border border-dashed border-gray-300 flex items-center justify-center p-2">
                            <img :src="previewLogo" alt="Restaurant Logo Preview" class="max-h-16 max-w-full object-contain">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-500 mb-1">Replace Logo File</label>
                            <input type="file" name="restaurant_logo" accept="image/jpeg,image/png,image/jpg,image/webp,image/svg+xml"
                                   @change="if ($event.target.files.length) previewLogo = URL.createObjectURL($event.target.files[0])"
                                   class="w-full p-2 rounded-xl border border-[#F0E6EC] file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#FFF3F8] file:text-[#F50070] hover:file:bg-pink-100">
                        </div>
                    </div>

                    <!-- Favicon Upload Card -->
                    <div class="p-4 rounded-xl bg-white border border-[#F0E6EC] space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#25232A]">Browser Favicon</span>
                            <span class="text-[10px] text-gray-400">Browser Tab Icon (ICO, PNG, SVG)</span>
                        </div>
                        <div class="h-20 bg-gray-50 rounded-xl border border-dashed border-gray-300 flex items-center justify-center p-2">
                            <img :src="previewFavicon" alt="Favicon Preview" class="w-10 h-10 object-contain">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-500 mb-1">Replace Favicon File</label>
                            <input type="file" name="restaurant_favicon" accept="image/x-icon,image/png,image/svg+xml,image/jpeg"
                                   @change="if ($event.target.files.length) previewFavicon = URL.createObjectURL($event.target.files[0])"
                                   class="w-full p-2 rounded-xl border border-[#F0E6EC] file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#FFF3F8] file:text-[#F50070] hover:file:bg-pink-100">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block font-bold text-[#25232A] mb-1">Temporary Closure Notice (Shown when closed)</label>
                    <input type="text" name="temporary_closed_notice" value="{{ $settings['temporary_closed_notice'] ?? '' }}" placeholder="We are temporarily closed for a private catering event. Will reopen at 6:00 PM!" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Restaurant Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="restaurant_name" value="{{ $settings['restaurant_name'] ?? 'My 2nd Wife' }}" required class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Short Name / Brand Code</label>
                    <input type="text" name="restaurant_short_name" value="{{ $settings['restaurant_short_name'] ?? 'My 2nd Wife' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-[#25232A] mb-1">Tagline</label>
                    <input type="text" name="restaurant_tagline" value="{{ $settings['restaurant_tagline'] ?? 'Flavors You Can Never Resist' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-[#25232A] mb-1">Short Description (Hero Banner & Footers)</label>
                    <input type="text" name="restaurant_short_description" value="{{ $settings['restaurant_short_description'] ?? '' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-[#25232A] mb-1">Full Restaurant Story & Description</label>
                    <textarea name="restaurant_description" rows="3" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">{{ $settings['restaurant_description'] ?? '' }}</textarea>
                </div>

                <!-- Contact Details -->
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Primary Email</label>
                    <input type="email" name="restaurant_email" value="{{ $settings['restaurant_email'] ?? 'hello@my2ndwife.com' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Primary Phone</label>
                    <input type="text" name="restaurant_phone" value="{{ $settings['restaurant_phone'] ?? '+91 98300 12345' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Alternate Phone Number</label>
                    <input type="text" name="restaurant_phone_alt" value="{{ $settings['restaurant_phone_alt'] ?? '' }}" placeholder="Optional second number" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">WhatsApp Order Support Number</label>
                    <input type="text" name="restaurant_whatsapp" value="{{ $settings['restaurant_whatsapp'] ?? '' }}" placeholder="+91 98300 12345" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <!-- Physical Address Section -->
                <div class="md:col-span-2 pt-2 border-t border-gray-100">
                    <h4 class="font-extrabold text-[#25232A] text-xs mb-3 flex items-center gap-2">
                        <span>📍</span> Restaurant Kitchen Address & Coordinates
                    </h4>
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-[#25232A] mb-1">Street Address</label>
                    <textarea name="restaurant_address" rows="2" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">{{ $settings['restaurant_address'] ?? '12, Park Street, Near Camac Street Crossing' }}</textarea>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">City</label>
                    <input type="text" name="restaurant_city" value="{{ $settings['restaurant_city'] ?? 'Kolkata' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">District / Region</label>
                    <input type="text" name="restaurant_district" value="{{ $settings['restaurant_district'] ?? 'Kolkata' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">State</label>
                    <input type="text" name="restaurant_state" value="{{ $settings['restaurant_state'] ?? 'West Bengal' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">PIN / Postal Code</label>
                    <input type="text" name="restaurant_pincode" value="{{ $settings['restaurant_pincode'] ?? '700016' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Country</label>
                    <input type="text" name="restaurant_country" value="{{ $settings['restaurant_country'] ?? 'India' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Google Maps Directions URL</label>
                    <input type="url" name="restaurant_google_maps_url" value="{{ $settings['restaurant_google_maps_url'] ?? 'https://maps.google.com/?q=Park+Street+Kolkata' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Restaurant Latitude (GPS)</label>
                    <input type="number" step="0.0000001" name="restaurant_latitude" value="{{ $settings['restaurant_latitude'] ?? '22.5535' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] font-mono focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Restaurant Longitude (GPS)</label>
                    <input type="number" step="0.0000001" name="restaurant_longitude" value="{{ $settings['restaurant_longitude'] ?? '88.3533' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] font-mono focus:border-[#F50070]">
                </div>

                <!-- Legal & Order Parameters -->
                <div class="md:col-span-2 pt-2 border-t border-gray-100">
                    <h4 class="font-extrabold text-[#25232A] text-xs mb-3 flex items-center gap-2">
                        <span>📋</span> Licensing & Operational Limits
                    </h4>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">GSTIN Number (If applicable)</label>
                    <input type="text" name="gstin" value="{{ $settings['gstin'] ?? '' }}" placeholder="19AAAAA0000A1Z5" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">FSSAI Food License Number (If applicable)</label>
                    <input type="text" name="fssai_license" value="{{ $settings['fssai_license'] ?? '' }}" placeholder="12821000000000" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Minimum Order Amount (₹)</label>
                    <input type="number" step="0.01" name="minimum_order_amount" value="{{ $settings['minimum_order_amount'] ?? '199.00' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Estimated Preparation Time (Minutes)</label>
                    <input type="number" name="estimated_prep_time_mins" value="{{ $settings['estimated_prep_time_mins'] ?? '25' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div class="md:col-span-2 p-4 rounded-2xl bg-[#FAF8FB] border border-[#F0E6EC] space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-[#F0E6EC]">
                        <div>
                            <label class="block font-bold text-[#25232A]">Application Timezone</label>
                            <p class="text-xs text-[#7A7585]">Required business timezone: Indian Standard Time (IST, UTC+05:30)</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-black border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Active: Asia/Kolkata (IST)</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                        <div>
                            <label class="block text-xs font-bold text-[#7A7585] mb-1">Timezone Identifier</label>
                            <select name="restaurant_timezone" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] text-xs font-bold bg-white">
                                <option value="Asia/Kolkata" {{ ($settings['restaurant_timezone'] ?? 'Asia/Kolkata') === 'Asia/Kolkata' ? 'selected' : '' }}>
                                    India Standard Time (IST) &mdash; Asia/Kolkata (UTC+05:30)
                                </option>
                                <option value="UTC" {{ ($settings['restaurant_timezone'] ?? '') === 'UTC' ? 'selected' : '' }}>
                                    Coordinated Universal Time (UTC)
                                </option>
                            </select>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-[#F0E6EC]">
                            <span class="block text-[11px] font-bold text-[#7A7585] uppercase tracking-wider">Current IST Clock</span>
                            <span class="text-sm font-black text-[#25232A]">{{ now()->timezone('Asia/Kolkata')->format('d M Y, h:i:s A') }} IST</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-medium flex items-start gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                        <span><strong>Fixed IST Business Operations:</strong> Order timestamps, scheduled delivery slots, rider dispatch tracking, admin daily reports, and earnings calculations are all synchronized with Indian Standard Time.</span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Currency</label>
                        <input type="text" name="currency_code" value="{{ $settings['currency_code'] ?? 'INR' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                    </div>
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Symbol</label>
                        <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] ?? '₹' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                    </div>
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Position</label>
                        <select name="currency_symbol_position" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                            <option value="before" {{ ($settings['currency_symbol_position'] ?? 'before') === 'before' ? 'selected' : '' }}>Before (₹80)</option>
                            <option value="after" {{ ($settings['currency_symbol_position'] ?? '') === 'after' ? 'selected' : '' }}>After (80 ₹)</option>
                        </select>
                    </div>
                </div>

                <div class="md:col-span-2 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="allow_guest_checkout" value="1" {{ ($settings['allow_guest_checkout'] ?? true) ? 'checked' : '' }} class="rounded text-[#F50070]">
                        <span class="font-bold text-[#25232A]">Allow Guest Checkout (Customers can order without creating account)</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all">
                    Save General Information
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 2: BRANDING & LOGOS -->
    <div x-show="activeTab === 'branding'" x-cloak class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs max-w-4xl">
            @csrf
            <input type="hidden" name="_section" value="branding">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="font-extrabold text-[#25232A] text-base">Branding & Logo Management</h3>
                <p class="text-gray-500 text-[11px]">Upload logos, favicons, hero banners, and default food placeholders. All images are optimized and served through Laravel storage.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Main Header Logo -->
                <div class="p-4 rounded-2xl border border-[#F0E6EC] bg-gray-50/50 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-[#25232A]">Main Website Logo</span>
                        <span class="text-[10px] text-gray-400">Header & Nav (SVG / PNG)</span>
                    </div>
                    <div class="h-20 bg-white rounded-xl border border-dashed border-gray-300 flex items-center justify-center p-2">
                        <img :src="previewLogo" alt="Logo Preview" class="max-h-16 max-w-full object-contain">
                    </div>
                    <input type="file" name="restaurant_logo" accept="image/*"
                           @change="previewLogo = URL.createObjectURL($event.target.files[0])"
                           class="w-full p-2 rounded-xl border border-[#F0E6EC] file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#FFF3F8] file:text-[#F50070]">
                </div>

                <!-- Dark Logo -->
                <div class="p-4 rounded-2xl border border-[#F0E6EC] bg-gray-900 text-white space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-white">Dark-Background Logo</span>
                        <span class="text-[10px] text-gray-400">Footer & Dark Mode</span>
                    </div>
                    <div class="h-20 bg-gray-800 rounded-xl border border-dashed border-gray-700 flex items-center justify-center p-2">
                        <img src="{{ asset($settings['restaurant_logo_dark'] ?? 'images/defaults/logo.svg') }}" alt="Dark Logo" class="max-h-16 max-w-full object-contain">
                    </div>
                    <input type="file" name="restaurant_logo_dark" accept="image/*"
                           class="w-full p-2 rounded-xl border border-gray-700 bg-gray-800 text-white file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#F50070] file:text-white">
                </div>

                <!-- Favicon -->
                <div class="p-4 rounded-2xl border border-[#F0E6EC] bg-gray-50/50 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-[#25232A]">Browser Favicon</span>
                        <span class="text-[10px] text-gray-400">32x32 / SVG</span>
                    </div>
                    <div class="h-16 bg-white rounded-xl border border-dashed border-gray-300 flex items-center justify-center p-2">
                        <img src="{{ asset($settings['restaurant_favicon'] ?? 'images/defaults/favicon.svg') }}" alt="Favicon" class="w-8 h-8 object-contain">
                    </div>
                    <input type="file" name="restaurant_favicon" accept="image/*,.ico"
                           class="w-full p-2 rounded-xl border border-[#F0E6EC] file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#FFF3F8] file:text-[#F50070]">
                </div>

                <!-- Restaurant Cover Image -->
                <div class="p-4 rounded-2xl border border-[#F0E6EC] bg-gray-50/50 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-[#25232A]">Restaurant Cover Image</span>
                        <span class="text-[10px] text-gray-400">Hero & Social Sharing</span>
                    </div>
                    <div class="h-16 bg-white rounded-xl border border-dashed border-gray-300 flex items-center justify-center p-1 overflow-hidden">
                        <img :src="previewCover" alt="Cover" class="h-full w-full object-cover rounded-lg">
                    </div>
                    <input type="file" name="restaurant_cover_image" accept="image/*"
                           @change="previewCover = URL.createObjectURL($event.target.files[0])"
                           class="w-full p-2 rounded-xl border border-[#F0E6EC] file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#FFF3F8] file:text-[#F50070]">
                </div>
            </div>

            <!-- Announcement Bar -->
            <div class="p-4 rounded-2xl border border-[#F0E6EC] bg-pink-50/40 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-[#25232A]">Top Announcement Bar</h4>
                        <p class="text-[11px] text-gray-500">Highlighted message displayed at the very top of the customer website.</p>
                    </div>
                    <input type="checkbox" name="announcement_bar_enabled" value="1" {{ ($settings['announcement_bar_enabled'] ?? true) ? 'checked' : '' }} class="rounded text-[#F50070]">
                </div>
                <input type="text" name="announcement_bar_text" value="{{ $settings['announcement_bar_text'] ?? '' }}" placeholder="⚡ Free Delivery on orders above ₹499 | Use code TASTE20 for 20% OFF!" class="w-full p-3 rounded-xl border border-[#F0E6EC] bg-white">
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all">
                    Save Branding & Logos
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 3: CONTACT & ADDRESS -->
    <div x-show="activeTab === 'contact'" x-cloak class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 text-xs max-w-4xl">
            @csrf
            <input type="hidden" name="_section" value="contact">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="font-extrabold text-[#25232A] text-base">Contact Information & Kitchen Address</h3>
                <p class="text-gray-500 text-[11px]">Public address, takeaway pickup location, customer helpline and Google Maps directions link.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block font-bold text-[#25232A] mb-1">Street Address / Kitchen Location</label>
                    <textarea name="restaurant_address" rows="2" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">{{ $settings['restaurant_address'] ?? '' }}</textarea>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">City</label>
                    <input type="text" name="restaurant_city" value="{{ $settings['restaurant_city'] ?? 'Kolkata' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">District</label>
                    <input type="text" name="restaurant_district" value="{{ $settings['restaurant_district'] ?? 'Kolkata' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">State</label>
                    <input type="text" name="restaurant_state" value="{{ $settings['restaurant_state'] ?? 'West Bengal' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">PIN / Postal Code</label>
                    <input type="text" name="restaurant_pincode" value="{{ $settings['restaurant_pincode'] ?? '700016' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Country</label>
                    <input type="text" name="restaurant_country" value="{{ $settings['restaurant_country'] ?? 'India' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Google Maps Direct Directions URL</label>
                    <input type="url" name="restaurant_google_maps_url" value="{{ $settings['restaurant_google_maps_url'] ?? '' }}" placeholder="https://maps.google.com/?q=..." class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all">
                    Save Contact Details
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 4: GOOGLE MAPS INTEGRATION -->
    <div x-show="activeTab === 'google_maps'" x-cloak class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 text-xs max-w-4xl">
            @csrf
            <input type="hidden" name="_section" value="google_maps">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="font-extrabold text-[#25232A] text-base">Google Maps & GPS Geolocation Integration</h3>
                <p class="text-gray-500 text-[11px]">Configure Google Maps API keys securely, set restaurant GPS coordinates for distance calculation, and enable address autocomplete.</p>
            </div>

            <!-- API Key Security Notice Banner -->
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200 text-blue-900 space-y-2">
                <div class="flex items-center gap-2 font-bold text-xs text-blue-950">
                    <i data-lucide="shield-check" class="w-4 h-4 text-blue-600"></i>
                    <span>Google Cloud Console Requirements & Security Policy</span>
                </div>
                <p class="text-[11px] leading-relaxed">
                    To use Google Maps features, generate an API key in your Google Cloud Console and enable:
                    <span class="font-bold underline">Maps JavaScript API</span>, 
                    <span class="font-bold underline">Places API (New)</span>, 
                    <span class="font-bold underline">Geocoding API</span>, and 
                    <span class="font-bold underline">Distance Matrix API</span>.
                    For maximum security, restrict the HTTP referrer of browser keys. Your saved API key is masked and will never be overwritten if submitted blank.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-[#25232A]">Google Maps API Key</label>
                        <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-bold border border-emerald-200">Database + .env Sync</span>
                    </div>
                    <div class="relative">
                        <input type="password" name="google_maps_api_key" 
                               value="{{ $maskedSecrets['google_maps_api_key'] ?? '' }}"
                               placeholder="Enter AIzaSy... or leave blank to keep existing key"
                               class="w-full p-3 font-mono rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block">Saved keys are securely masked (••••). Leaving this field blank retains the current key without erasing it. Automatically synchronized with runtime environment.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Restaurant Latitude <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.0000001" name="restaurant_latitude" value="{{ $settings['restaurant_latitude'] ?? '22.5535' }}" required class="w-full p-3 rounded-xl border border-[#F0E6EC] font-mono focus:border-[#F50070]">
                    <span class="text-[10px] text-gray-400">Default: 22.553500 (Park Street, Kolkata)</span>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Restaurant Longitude <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.0000001" name="restaurant_longitude" value="{{ $settings['restaurant_longitude'] ?? '88.3533' }}" required class="w-full p-3 rounded-xl border border-[#F0E6EC] font-mono focus:border-[#F50070]">
                    <span class="text-[10px] text-gray-400">Default: 88.353300 (Park Street, Kolkata)</span>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Default Map Zoom Level (1-21)</label>
                    <input type="number" min="1" max="21" name="default_map_zoom" value="{{ $settings['default_map_zoom'] ?? '14' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div class="space-y-3 pt-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="google_maps_js_enabled" value="1" {{ ($settings['google_maps_js_enabled'] ?? false) ? 'checked' : '' }} class="rounded text-[#F50070]">
                        <span class="font-bold text-[#25232A]">Enable Google Maps JavaScript API</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="customer_autocomplete_enabled" value="1" {{ ($settings['customer_autocomplete_enabled'] ?? false) ? 'checked' : '' }} class="rounded text-[#F50070]">
                        <span class="font-bold text-[#25232A]">Enable Places Autocomplete for Customer Checkout</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="google_maps_routes_enabled" value="1" {{ ($settings['google_maps_routes_enabled'] ?? false) ? 'checked' : '' }} class="rounded text-[#F50070]">
                        <span class="font-bold text-[#25232A]">Enable Google Distance Matrix / Routes API (Road Distance)</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all">
                    Save Google Maps Settings
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 5: DELIVERY RADIUS & FEES -->
    <div x-show="activeTab === 'delivery'" x-cloak class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 text-xs max-w-4xl">
            @csrf
            <input type="hidden" name="_section" value="delivery">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="font-extrabold text-[#25232A] text-base">Delivery Radius, Distance Calculation & Fees</h3>
                <p class="text-gray-500 text-[11px]">Strict server-side validation ensures only customers within your designated radius can order home delivery, while takeaway remains available everywhere.</p>
            </div>

            <!-- Toggles -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-[#25232A]">Home Delivery Enabled</h4>
                        <p class="text-[11px] text-gray-500">Allow customers to order for delivery to their doorstep.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="delivery_enabled" value="1" {{ ($settings['delivery_enabled'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>

                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-[#25232A]">Takeaway / Pickup Enabled</h4>
                        <p class="text-[11px] text-gray-500">Allow customers to pick up orders from the restaurant.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="takeaway_enabled" value="1" {{ ($settings['takeaway_enabled'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Maximum Delivery Radius (Kilometres) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.1" name="delivery_radius_km" value="{{ $settings['delivery_radius_km'] ?? '5.0' }}" required class="w-full p-3 rounded-xl border border-[#F0E6EC] font-bold text-[#F50070]">
                    <span class="text-[10px] text-gray-400">Orders beyond this distance will be blocked with a takeaway option.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Distance Calculation Method</label>
                    <select name="distance_calculation_method" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                        <option value="haversine" {{ ($settings['distance_calculation_method'] ?? 'haversine') === 'haversine' ? 'selected' : '' }}>Straight-Line Distance (Haversine Formula)</option>
                        <option value="google_routes" {{ ($settings['distance_calculation_method'] ?? '') === 'google_routes' ? 'selected' : '' }}>Road Distance (Google Distance Matrix API)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Delivery Fee Calculation Method</label>
                    <select name="delivery_fee_calculation_method" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                        <option value="fixed" {{ ($settings['delivery_fee_calculation_method'] ?? 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed Delivery Fee</option>
                        <option value="per_km" {{ ($settings['delivery_fee_calculation_method'] ?? '') === 'per_km' ? 'selected' : '' }}>Per-Kilometre (Base Fee + Distance × Rate)</option>
                        <option value="distance_tiered" {{ ($settings['delivery_fee_calculation_method'] ?? '') === 'distance_tiered' ? 'selected' : '' }}>Tiered (Base fee up to X km, then extra per km)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Base Delivery Charge (₹)</label>
                    <input type="number" step="0.01" name="default_delivery_charge" value="{{ $settings['default_delivery_charge'] ?? '30.00' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Free Delivery Minimum Order Amount (₹)</label>
                    <input type="number" step="0.01" name="free_delivery_threshold" value="{{ $settings['free_delivery_threshold'] ?? '499.00' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                    <span class="text-[10px] text-gray-400">Cart subtotals equal to or greater than this will get ₹0 delivery fee.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Minimum Order Amount for Delivery (₹)</label>
                    <input type="number" step="0.01" name="min_order_amount_delivery" value="{{ $settings['min_order_amount_delivery'] ?? '149.00' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                    <span class="text-[10px] text-gray-400">Orders must reach this food total to qualify for delivery dispatch.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Per-Kilometre Rate (₹/km, if using per-km method)</label>
                    <input type="number" step="0.01" name="delivery_charge_per_km" value="{{ $settings['delivery_charge_per_km'] ?? '10.00' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Tiered Base Distance Range (km)</label>
                    <input type="number" step="0.1" name="delivery_tiered_base_km" value="{{ $settings['delivery_tiered_base_km'] ?? '3.0' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                    <span class="text-[10px] text-gray-400">Base delivery charge applies up to this distance.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Tiered Extra Rate per km beyond base (₹/km)</label>
                    <input type="number" step="0.01" name="delivery_tiered_extra_per_km" value="{{ $settings['delivery_tiered_extra_per_km'] ?? '12.00' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Estimated Delivery Time Window</label>
                    <input type="text" name="estimated_delivery_time" value="{{ $settings['estimated_delivery_time'] ?? '30-45 mins' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Delivery Operating Hours</label>
                    <input type="text" name="delivery_operating_hours" value="{{ $settings['delivery_operating_hours'] ?? '11:00 AM - 11:30 PM' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-[#25232A] mb-1">Delivery Unavailable Notice</label>
                    <input type="text" name="delivery_unavailable_message" value="{{ $settings['delivery_unavailable_message'] ?? 'We do not deliver to this location yet.' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all">
                    Save Delivery Rules
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 6: OPENING HOURS -->
    <div x-show="activeTab === 'hours'" x-cloak class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 text-xs max-w-4xl">
            @csrf
            <input type="hidden" name="_section" value="hours">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="font-extrabold text-[#25232A] text-base">Daily Opening Hours & Schedule</h3>
                <p class="text-gray-500 text-[11px]">Set individual opening and closing times for each day of the week (Monday through Sunday), including support for late midnight orders.</p>
            </div>

            <div class="p-4 rounded-2xl bg-pink-50/50 border border-pink-100 flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-[#25232A]">Allow Future Scheduled Orders</h4>
                    <p class="text-[11px] text-gray-500">Allow customers to schedule meal deliveries ahead of time even during closed hours.</p>
                </div>
                <input type="checkbox" name="scheduled_orders_available" value="1" {{ ($settings['scheduled_orders_available'] ?? true) ? 'checked' : '' }} class="rounded text-[#F50070]">
            </div>

            <div class="space-y-3">
                @php
                    $days = [
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday'
                    ];
                @endphp

                @foreach($days as $key => $label)
                    @php
                        $dayData = $openingHours[$key] ?? ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:30'];
                    @endphp
                    <div class="p-3.5 rounded-2xl border border-gray-100 bg-gray-50/40 flex flex-col sm:flex-row items-center justify-between gap-4"
                         x-data="{ isOpen: {{ ($dayData['is_open'] ?? true) ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-3 w-48">
                            <input type="hidden" name="opening_hours[{{ $key }}][is_open]" value="0">
                            <input type="checkbox" name="opening_hours[{{ $key }}][is_open]" value="1" 
                                   @change="isOpen = $el.checked"
                                   {{ ($dayData['is_open'] ?? true) ? 'checked' : '' }} 
                                   id="open_{{ $key }}" class="rounded text-[#F50070]">
                            <label for="open_{{ $key }}" class="font-extrabold text-[#25232A] text-xs cursor-pointer">{{ $label }}</label>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full" 
                                  :class="isOpen ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-500'"
                                  x-text="isOpen ? 'Open' : 'Closed'">
                                {{ ($dayData['is_open'] ?? true) ? 'Open' : 'Closed' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 flex-1 justify-end">
                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-400 text-[10px]">Opens:</span>
                                <input type="time" name="opening_hours[{{ $key }}][open_time]" value="{{ $dayData['open_time'] ?? '11:00' }}" class="p-2 rounded-xl border border-[#F0E6EC] bg-white font-mono text-xs">
                            </div>

                            <span class="text-gray-300 font-bold">—</span>

                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-400 text-[10px]">Closes:</span>
                                <input type="time" name="opening_hours[{{ $key }}][close_time]" value="{{ $dayData['close_time'] ?? '23:30' }}" class="p-2 rounded-xl border border-[#F0E6EC] bg-white font-mono text-xs">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all">
                    Save Opening Hours
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 7: TAXES & CHARGES -->
    <div x-show="activeTab === 'orders'" x-cloak class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 text-xs max-w-4xl">
            @csrf
            <input type="hidden" name="_section" value="orders">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="font-extrabold text-[#25232A] text-base">Tax Rates, Packaging Charges & Order Policies</h3>
                <p class="text-gray-500 text-[11px]">Configure GST tax settings, hygienic food packaging fees, customer order notes, and cancellation policies.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-[#25232A]">Enable Goods & Services Tax (GST)</h4>
                        <p class="text-[11px] text-gray-500">Calculate tax on cart items and packaging charges.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="tax_enabled" value="1" {{ ($settings['tax_enabled'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Applicable Tax Rate (%)</label>
                    <input type="number" step="0.01" name="tax_rate_percentage" value="{{ $settings['tax_rate_percentage'] ?? '5.00' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                    <span class="text-[10px] text-gray-400">Indian standard food GST is typically 5.00%.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Tax Pricing Mode</label>
                    <select name="tax_pricing_type" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                        <option value="exclusive" {{ ($settings['tax_pricing_type'] ?? 'exclusive') === 'exclusive' ? 'selected' : '' }}>Exclusive (Tax added on top of food subtotal)</option>
                        <option value="inclusive" {{ ($settings['tax_pricing_type'] ?? '') === 'inclusive' ? 'selected' : '' }}>Inclusive (Menu prices already include tax)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Hygienic Packaging Charge (₹)</label>
                    <input type="number" step="0.01" name="packaging_charge" value="{{ $settings['packaging_charge'] ?? '25.00' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-[#25232A] mb-1">Order Cancellation Policy Notice</label>
                    <input type="text" name="order_cancellation_policy" value="{{ $settings['order_cancellation_policy'] ?? '' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div class="space-y-3 md:col-span-2 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="customer_notes_enabled" value="1" {{ ($settings['customer_notes_enabled'] ?? true) ? 'checked' : '' }} class="rounded text-[#F50070]">
                        <span class="font-bold text-[#25232A]">Allow Customer Cooking Instructions / Food Notes</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="delivery_instructions_enabled" value="1" {{ ($settings['delivery_instructions_enabled'] ?? true) ? 'checked' : '' }} class="rounded text-[#F50070]">
                        <span class="font-bold text-[#25232A]">Allow Delivery Drop-off Instructions</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="order_auto_confirm" value="1" {{ ($settings['order_auto_confirm'] ?? false) ? 'checked' : '' }} class="rounded text-[#F50070]">
                        <span class="font-bold text-[#25232A]">Auto-Accept Orders (Skip manual 'Accept' step in kitchen)</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all">
                    Save Tax & Order Settings
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 8: PAYMENT SETTINGS -->
    <div x-show="activeTab === 'payment'" x-cloak class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 text-xs max-w-4xl">
            @csrf
            <input type="hidden" name="_section" value="payment">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="font-extrabold text-[#25232A] text-base">Payment Methods & Gateway Settings</h3>
                <p class="text-gray-500 text-[11px]">Configure Cash on Delivery and online payment gateways (Razorpay / UPI / NetBanking).</p>
            </div>

            <!-- COD Settings Card -->
            <div class="p-5 rounded-2xl border border-[#F0E6EC] bg-gray-50/50 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-[#25232A] text-sm">💵 Cash on Delivery (COD)</h4>
                        <p class="text-[11px] text-gray-500">Allow customers to pay in cash upon meal arrival.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="cod_enabled" value="1" {{ ($settings['cod_enabled'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Display Label</label>
                    <input type="text" name="cod_display_name" value="{{ $settings['cod_display_name'] ?? 'Cash on Delivery (COD)' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] bg-white">
                </div>
            </div>

            <!-- Razorpay Gateway Card -->
            <div class="p-5 rounded-2xl border border-[#F0E6EC] bg-gray-50/50 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-[#25232A] text-sm">💳 Razorpay / UPI / Card Gateway</h4>
                        <p class="text-[11px] text-gray-500">Accept UPI (GPay, PhonePe, Paytm), Debit/Credit Cards & NetBanking.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="razorpay_enabled" value="1" {{ ($settings['razorpay_enabled'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>

                @if(!empty($settings['razorpay_enabled']) && empty($settings['razorpay_key_id']))
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0"></i>
                    <span><strong>Setup Required:</strong> Razorpay is enabled but Key ID is missing. Add your Razorpay API credentials below before activating for live customer checkouts.</span>
                </div>
                @elseif(!empty($settings['razorpay_enabled']) && !empty($settings['razorpay_key_id']))
                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span><strong>Gateway Operational:</strong> Razorpay is configured and active in <strong>{{ strtoupper($settings['payment_mode'] ?? 'test') }}</strong> mode.</span>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Display Label</label>
                        <input type="text" name="razorpay_display_name" value="{{ $settings['razorpay_display_name'] ?? 'Online Payment (UPI, Cards, NetBanking)' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC] bg-white">
                    </div>

                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Environment Mode</label>
                        <select name="payment_mode" class="w-full p-3 rounded-xl border border-[#F0E6EC] bg-white">
                            <option value="test" {{ ($settings['payment_mode'] ?? 'test') === 'test' ? 'selected' : '' }}>Test / Sandbox</option>
                            <option value="live" {{ ($settings['payment_mode'] ?? '') === 'live' ? 'selected' : '' }}>Live / Production</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Razorpay Key ID</label>
                        <input type="text" name="razorpay_key_id" value="{{ $settings['razorpay_key_id'] ?? '' }}" placeholder="rzp_test_..." class="w-full p-3 rounded-xl border border-[#F0E6EC] bg-white font-mono">
                    </div>

                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Razorpay Key Secret</label>
                        <input type="password" name="razorpay_key_secret" value="{{ $maskedSecrets['razorpay_key_secret'] ?? '' }}" placeholder="Enter secret or leave blank to keep existing" class="w-full p-3 rounded-xl border border-[#F0E6EC] bg-white font-mono">
                        <span class="text-[10px] text-gray-400">Secret is masked. Leaving blank preserves existing saved secret.</span>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block font-bold text-[#25232A] mb-1">Razorpay Webhook Secret</label>
                        <input type="password" name="razorpay_webhook_secret" value="{{ $maskedSecrets['razorpay_webhook_secret'] ?? '' }}" placeholder="Enter webhook secret or leave blank to keep existing" class="w-full p-3 rounded-xl border border-[#F0E6EC] bg-white font-mono">
                        <span class="text-[10px] text-gray-400">Used to verify asynchronous payment.captured and payment.failed events from Razorpay.</span>
                    </div>
                </div>

                <!-- Webhook URL Configuration Card -->
                <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200 text-blue-950 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold flex items-center gap-1.5 text-xs text-blue-900">
                            <i data-lucide="webhook" class="w-4 h-4 text-blue-600"></i>
                            Razorpay Dashboard Webhook URL
                        </span>
                        <span class="text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full">Events: payment.captured, payment.failed</span>
                    </div>
                    <p class="text-[11px] text-blue-900 leading-relaxed">
                        In your <strong>Razorpay Dashboard → Settings → Webhooks</strong>, add this webhook endpoint and paste your secret above:
                    </p>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ url('/payment/webhook') }}" class="w-full p-2.5 rounded-xl border border-blue-200 bg-white font-mono text-xs select-all">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all">
                    Save Payment Settings
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 9: NOTIFICATIONS & FIREBASE PUSH -->
    <div x-show="activeTab === 'notifications'" x-cloak class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm space-y-6">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs max-w-4xl">
            @csrf
            <input type="hidden" name="_section" value="notifications">

            <div class="border-b border-gray-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-extrabold text-[#25232A] text-base flex items-center gap-2">
                        <span>🔔</span> Order Notifications & Push Alerts
                    </h3>
                    <p class="text-gray-500 text-[11px]">Configure Firebase Cloud Messaging (FCM HTTP v1), email dispatch alerts, SMS text gateways, and WhatsApp notifications.</p>
                </div>
                <a href="{{ route('admin.notifications.index') }}" 
                   class="px-4 py-2 rounded-xl bg-pink-50 hover:bg-[#F50070] text-[#F50070] hover:text-white font-bold text-xs flex items-center gap-1.5 transition-colors border border-pink-200 shrink-0">
                    <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                    <span>Delivery Logs & History</span>
                </a>
            </div>

            <!-- Firebase Cloud Messaging (FCM HTTP v1) Section -->
            <div class="p-5 sm:p-6 rounded-2xl border border-[#F50070]/30 bg-gradient-to-br from-[#FFF0F6]/40 via-white to-pink-50/20 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-pink-100/70 pb-4">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="font-extrabold text-[#25232A] text-sm flex items-center gap-2">
                                <span>🔥</span> Firebase Cloud Messaging (FCM HTTP v1)
                            </h4>
                            @php
                                $fbSeverityClass = match($firebaseStatus['status_severity'] ?? 'gray') {
                                    'green' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'red' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-gray-100 text-gray-500 border-gray-200',
                                };
                            @endphp
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full border {{ $fbSeverityClass }}">
                                {{ $firebaseStatus['status_label'] ?? 'Status Unknown' }}
                            </span>
                            @if($firebaseStatus['file_loaded'] ?? false)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                    Service Account JSON Loaded
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-500 text-[11px] mt-0.5">Powers native Android and iOS mobile app push notifications for orders, riders, and admins.</p>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" name="firebase_notifications_enabled" value="1" {{ ($settings['firebase_notifications_enabled'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#F50070]"></div>
                        <span class="ml-2 font-extrabold text-xs text-[#25232A]">Enable Push</span>
                    </label>
                </div>

                <!-- Credentials Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Firebase Project ID</label>
                        <input type="text" name="firebase_project_id" value="{{ $settings['firebase_project_id'] ?? '' }}" 
                               placeholder="e.g. my-2nd-wife-food" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white font-mono text-xs">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Client Email (Service Account)</label>
                        <input type="email" name="firebase_client_email" value="{{ $settings['firebase_client_email'] ?? '' }}" 
                               placeholder="firebase-adminsdk-xxx@project.iam.gserviceaccount.com" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white font-mono text-xs">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-gray-700 mb-1">Android Notification Channel ID</label>
                        <input type="text" name="firebase_channel_id" value="{{ $settings['firebase_channel_id'] ?? 'm2w_orders' }}" 
                               placeholder="e.g. m2w_orders" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white font-mono text-xs">
                        <p class="text-[10px] text-gray-400 mt-0.5">Notification channel registered in Android app's NotificationManager (default: <span class="font-mono">m2w_orders</span>).</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-gray-700 mb-1">Private Key (RSA 256 PEM format)</label>
                        <textarea name="firebase_private_key" rows="3" 
                                  placeholder="-----BEGIN PRIVATE KEY-----&#10;...&#10;-----END PRIVATE KEY-----" 
                                  class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white font-mono text-[11px]">{{ $maskedSecrets['firebase_private_key'] ?? '' }}</textarea>
                        <p class="text-[10px] text-gray-400 mt-0.5">Masked securely. Leave unchanged to keep the existing stored key.</p>
                    </div>

                    <!-- Upload Service Account JSON -->
                    <div class="sm:col-span-2 p-3.5 rounded-xl bg-white border border-pink-200/70 space-y-1.5">
                        <label class="block font-bold text-[#25232A]">Or Upload Firebase Service Account JSON File</label>
                        <input type="file" name="firebase_service_account_file" id="firebaseServiceAccountFileInput" 
                               accept=".json,application/json" 
                               @change="handleFirebaseServiceAccountFile($event)"
                               class="w-full p-1.5 rounded-xl border border-[#F0E6EC] bg-gray-50/50 file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#FFF3F8] file:text-[#F50070]">
                        <p class="text-[10px] text-gray-400">Uploading your Google Cloud Service Account JSON file automatically populates the Project ID, Client Email, and Private Key, and saves it securely to server storage.</p>
                        <div id="firebaseJsonStatusMsg" class="hidden text-[11px] font-bold p-2.5 rounded-xl mt-2"></div>
                    </div>
                </div>

                <!-- Event Trigger Settings (Customer, Rider, Admin) -->
                @php
                    $custEvents = $settings['firebase_customer_events'] ?? ['order_placed', 'order_confirmed', 'payment_successful', 'payment_failed', 'order_preparing', 'food_packed', 'rider_assigned', 'out_for_delivery', 'order_delivered', 'order_cancelled'];
                    if (!is_array($custEvents)) $custEvents = [];

                    $riderEvents = $settings['firebase_rider_events'] ?? ['rider_new_delivery', 'rider_order_assigned', 'rider_assignment_cancelled', 'rider_pickup_reminder', 'rider_delivery_completed'];
                    if (!is_array($riderEvents)) $riderEvents = [];

                    $admEvents = $settings['firebase_admin_events'] ?? ['admin_new_order', 'admin_payment_verified', 'admin_payment_failed', 'admin_rider_registered', 'admin_rider_assignment_updated', 'admin_order_cancelled'];
                    if (!is_array($admEvents)) $admEvents = [];
                @endphp

                <div class="space-y-4 pt-3 border-t border-pink-100">
                    <h5 class="font-extrabold text-xs text-[#25232A] uppercase tracking-wider">Event Trigger Toggles</h5>

                    <!-- Customer Events -->
                    <div class="bg-white p-3.5 rounded-xl border border-gray-200 space-y-2">
                        <span class="font-bold text-[#25232A] text-xs block">👤 Customer Push Events</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-[11px]">
                            @foreach([
                                'order_placed' => 'Order Placed',
                                'order_confirmed' => 'Order Confirmed',
                                'payment_successful' => 'Payment Successful',
                                'payment_failed' => 'Payment Failed',
                                'order_preparing' => 'Food Preparing',
                                'food_packed' => 'Packed & Ready',
                                'rider_assigned' => 'Rider Assigned',
                                'out_for_delivery' => 'Out for Delivery',
                                'order_delivered' => 'Order Delivered',
                                'order_cancelled' => 'Order Cancelled',
                            ] as $evtKey => $evtLabel)
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="firebase_customer_events[]" value="{{ $evtKey }}" 
                                           {{ in_array($evtKey, $custEvents) ? 'checked' : '' }} class="rounded text-[#F50070]">
                                    <span>{{ $evtLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Rider Events -->
                    <div class="bg-white p-3.5 rounded-xl border border-gray-200 space-y-2">
                        <span class="font-bold text-[#25232A] text-xs block">🛵 Delivery Rider Push Events</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-[11px]">
                            @foreach([
                                'rider_new_delivery' => 'New Delivery Available',
                                'rider_order_assigned' => 'Order Assigned',
                                'rider_assignment_cancelled' => 'Assignment Cancelled',
                                'rider_pickup_reminder' => 'Pickup Reminder',
                                'rider_delivery_completed' => 'Delivery Completed',
                            ] as $evtKey => $evtLabel)
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="firebase_rider_events[]" value="{{ $evtKey }}" 
                                           {{ in_array($evtKey, $riderEvents) ? 'checked' : '' }} class="rounded text-[#F50070]">
                                    <span>{{ $evtLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Admin Events -->
                    <div class="bg-white p-3.5 rounded-xl border border-gray-200 space-y-2">
                        <span class="font-bold text-[#25232A] text-xs block">🛡️ Admin Alert Push Events</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-[11px]">
                            @foreach([
                                'admin_new_order' => 'New Order Received',
                                'admin_payment_verified' => 'Verified Payment',
                                'admin_payment_failed' => 'Payment Failed',
                                'admin_rider_registered' => 'New Rider Registered',
                                'admin_rider_assignment_updated' => 'Rider Assignment Update',
                                'admin_order_cancelled' => 'Order Cancelled',
                            ] as $evtKey => $evtLabel)
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="firebase_admin_events[]" value="{{ $evtKey }}" 
                                           {{ in_array($evtKey, $admEvents) ? 'checked' : '' }} class="rounded text-[#F50070]">
                                    <span>{{ $evtLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email, SMS, WhatsApp Notifications -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Admin Alert Notification Email</label>
                    <input type="email" name="order_notification_email" value="{{ $settings['order_notification_email'] ?? 'orders@my2ndwife.com' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div class="space-y-3 pt-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="customer_order_email_enabled" value="1" {{ ($settings['customer_order_email_enabled'] ?? true) ? 'checked' : '' }} class="rounded text-[#F50070]">
                        <span class="font-bold text-[#25232A]">Send Order Confirmation Email to Customers</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="admin_order_notification_enabled" value="1" {{ ($settings['admin_order_notification_enabled'] ?? true) ? 'checked' : '' }} class="rounded text-[#F50070]">
                        <span class="font-bold text-[#25232A]">Send New Order Email to Restaurant Admin</span>
                    </label>
                </div>

                <!-- SMS Provider -->
                <div class="p-4 rounded-2xl border border-[#F0E6EC] bg-gray-50/50 md:col-span-2 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-[#25232A]">📱 SMS Gateway Configuration (MSG91 / Fast2SMS)</h4>
                        <input type="checkbox" name="sms_enabled" value="1" {{ ($settings['sms_enabled'] ?? false) ? 'checked' : '' }} class="rounded text-[#F50070]">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold text-gray-500 mb-1">Provider</label>
                            <input type="text" name="sms_provider" value="{{ $settings['sms_provider'] ?? 'msg91' }}" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-500 mb-1">Sender ID</label>
                            <input type="text" name="sms_sender_id" value="{{ $settings['sms_sender_id'] ?? 'M2WIFE' }}" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-500 mb-1">Auth / API Key</label>
                            <input type="password" name="sms_api_key" value="{{ $maskedSecrets['sms_api_key'] ?? '' }}" placeholder="API Key" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white font-mono">
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Provider -->
                <div class="p-4 rounded-2xl border border-[#F0E6EC] bg-gray-50/50 md:col-span-2 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-[#25232A]">💬 WhatsApp Business Notification API</h4>
                        <input type="checkbox" name="whatsapp_notifications_enabled" value="1" {{ ($settings['whatsapp_notifications_enabled'] ?? false) ? 'checked' : '' }} class="rounded text-[#F50070]">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-gray-500 mb-1">Provider (Interakt / Wati / Twilio)</label>
                            <input type="text" name="whatsapp_provider" value="{{ $settings['whatsapp_provider'] ?? 'interakt' }}" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-500 mb-1">API Key / Token</label>
                            <input type="password" name="whatsapp_api_key" value="{{ $maskedSecrets['whatsapp_api_key'] ?? '' }}" placeholder="API Token" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white font-mono">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all">
                    Save Notification Settings
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 10: SEO & SOCIAL -->
    <div x-show="activeTab === 'seo'" x-cloak class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 text-xs max-w-4xl">
            @csrf
            <input type="hidden" name="_section" value="seo">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="font-extrabold text-[#25232A] text-base">Search Engine Optimization & Social Media</h3>
                <p class="text-gray-500 text-[11px]">Configure meta tags, title prefixes, social media handles and Open Graph preview cards.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block font-bold text-[#25232A] mb-1">Default Meta Title</label>
                    <input type="text" name="meta_title" value="{{ $settings['meta_title'] ?? 'My 2nd Wife | Authentic Indian Food & Delivery Kolkata' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-[#25232A] mb-1">Meta Description</label>
                    <textarea name="meta_description" rows="2" class="w-full p-3 rounded-xl border border-[#F0E6EC]">{{ $settings['meta_description'] ?? '' }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-[#25232A] mb-1">Meta Keywords (Comma-separated)</label>
                    <input type="text" name="meta_keywords" value="{{ $settings['meta_keywords'] ?? '' }}" class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Facebook URL</label>
                    <input type="url" name="facebook_url" value="{{ $settings['facebook_url'] ?? '' }}" placeholder="https://facebook.com/..." class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Instagram URL</label>
                    <input type="url" name="instagram_url" value="{{ $settings['instagram_url'] ?? '' }}" placeholder="https://instagram.com/..." class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">YouTube URL</label>
                    <input type="url" name="youtube_url" value="{{ $settings['youtube_url'] ?? '' }}" placeholder="https://youtube.com/@..." class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Twitter / X URL</label>
                    <input type="url" name="twitter_url" value="{{ $settings['twitter_url'] ?? '' }}" placeholder="https://twitter.com/..." class="w-full p-3 rounded-xl border border-[#F0E6EC]">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all">
                    Save SEO & Social Links
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 11: LEGAL & POLICIES -->
    <div x-show="activeTab === 'legal'" x-cloak class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 text-xs max-w-4xl">
            @csrf
            <input type="hidden" name="_section" value="legal">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="font-extrabold text-[#25232A] text-base">Legal Policies & Content Pages</h3>
                <p class="text-gray-500 text-[11px]">Manage customer-facing legal terms, cancellation guidelines, and food safety policies.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">About Us Content</label>
                    <textarea name="legal_about_us" rows="3" class="w-full p-3 rounded-xl border border-[#F0E6EC]">{{ $settings['legal_about_us'] ?? '' }}</textarea>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Contact Us Information</label>
                    <textarea name="legal_contact_us" rows="2" class="w-full p-3 rounded-xl border border-[#F0E6EC]">{{ $settings['legal_contact_us'] ?? '' }}</textarea>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Privacy Policy</label>
                    <textarea name="legal_privacy_policy" rows="3" class="w-full p-3 rounded-xl border border-[#F0E6EC]">{{ $settings['legal_privacy_policy'] ?? '' }}</textarea>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Terms & Conditions</label>
                    <textarea name="legal_terms" rows="3" class="w-full p-3 rounded-xl border border-[#F0E6EC]">{{ $settings['legal_terms'] ?? '' }}</textarea>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Refund & Cancellation Policy</label>
                    <textarea name="legal_refund_cancellation" rows="3" class="w-full p-3 rounded-xl border border-[#F0E6EC]">{{ $settings['legal_refund_cancellation'] ?? '' }}</textarea>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Delivery Policy & Guidelines</label>
                    <textarea name="legal_delivery_policy" rows="3" class="w-full p-3 rounded-xl border border-[#F0E6EC]">{{ $settings['legal_delivery_policy'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all">
                    Save Legal Information
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 13: FOOTER SETTINGS (CONSOLIDATED TO CANONICAL PAGE) -->
    <div x-show="activeTab === 'footer'" x-cloak class="bg-white p-8 sm:p-12 rounded-3xl border border-[#F0E6EC] shadow-sm text-center max-w-xl mx-auto space-y-5">
        <div class="w-16 h-16 mx-auto rounded-3xl bg-[#FFF0F6] text-[#F50070] flex items-center justify-center text-3xl shadow-sm">
            <i data-lucide="layout-template" class="w-8 h-8"></i>
        </div>
        <div>
            <h3 class="font-extrabold text-[#25232A] text-lg">Footer Settings Has Moved</h3>
            <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                To prevent duplicate menus, all footer branding, navigation links, policy columns, social media, mobile app downloads, and copyright options are now centrally managed under <strong>Website Management &rarr; Footer Settings</strong>.
            </p>
        </div>
        <div class="pt-2">
            <a href="{{ route('admin.footer.index') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#F50070] text-white font-bold text-xs hover:bg-[#D40060] transition-all shadow-md shadow-pink-500/20">
                <span>Go to Canonical Footer Settings</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>

    <!-- TAB: THERMAL RECEIPT SETTINGS (58mm / 80mm) -->
    <div x-show="activeTab === 'receipt'" x-cloak class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 text-xs max-w-4xl">
            @csrf
            <input type="hidden" name="_section" value="receipt">

            <div class="border-b border-gray-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-extrabold text-[#25232A] text-base">Thermal Receipt Printer Settings (58mm / 80mm)</h3>
                    <p class="text-gray-500 text-[11px]">Configure thermal receipt layout, paper width, restaurant header, line-item details, charges, and test print output.</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('admin.settings.receipt.test') }}" target="_blank" 
                       class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-[#25232A] font-bold text-xs flex items-center gap-1.5 transition-colors">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                        <span>Receipt Preview</span>
                    </a>
                    <a href="{{ route('admin.settings.receipt.test') }}" target="_blank" 
                       class="px-4 py-2 rounded-xl bg-[#F50070] hover:bg-[#D80063] text-white font-bold text-xs flex items-center gap-1.5 transition-colors shadow-sm">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Test Receipt</span>
                    </a>
                </div>
            </div>

            <!-- Printer Hardware & Paper Width Configuration -->
            <div class="p-5 rounded-2xl border border-pink-100 bg-[#FFF3F8]/40 space-y-4">
                <h4 class="font-extrabold text-[#25232A] text-xs uppercase tracking-wider flex items-center gap-2">
                    <span>🖨️</span> Printer Hardware & Paper Width
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Thermal Paper Width</label>
                        <select name="receipt_paper_width" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white font-semibold">
                            <option value="80mm" {{ ($settings['receipt_paper_width'] ?? '80mm') === '80mm' ? 'selected' : '' }}>80 mm (Standard POS / 3-inch roll)</option>
                            <option value="58mm" {{ ($settings['receipt_paper_width'] ?? '') === '58mm' ? 'selected' : '' }}>58 mm (Compact Mobile / 2-inch roll)</option>
                        </select>
                        <p class="text-[10px] text-gray-500 mt-1">Select the roll size used by your thermal printer (Epson, TVS, Rongta, etc.).</p>
                    </div>

                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Number of Receipt Copies per Print</label>
                        <input type="number" name="receipt_copies" min="1" max="5" value="{{ $settings['receipt_copies'] ?? 1 }}" 
                               class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white font-semibold">
                        <p class="text-[10px] text-gray-500 mt-1">Print multiple copies at once (e.g., 1 for kitchen, 1 for delivery rider/customer).</p>
                    </div>
                </div>

                <div class="p-3 bg-white/80 rounded-xl border border-pink-200 text-[11px] text-gray-600 leading-relaxed">
                    <strong>Hardware Compatibility Notice:</strong> Browser printing sends output directly to any installed system printer driver. Hardware-level automatic paper cutting and cash drawer ejection require an ESC/POS printer driver or raw printer bridge.
                </div>
            </div>

            <!-- Header & Branding Settings -->
            <div class="p-5 rounded-2xl border border-gray-200 bg-gray-50/60 space-y-4">
                <h4 class="font-extrabold text-[#25232A] text-xs uppercase tracking-wider flex items-center gap-2">
                    <span>🏢</span> Restaurant Branding & Header Details
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Include Restaurant Logo</span>
                            <span class="text-[10px] text-gray-400">Print logo graphic at receipt top</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_logo" value="1" {{ ($settings['receipt_show_logo'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200">
                        <label class="block font-bold text-[#25232A] mb-1">Logo Variant Source</label>
                        <select name="receipt_logo_source" class="w-full p-2 rounded-lg border border-[#F0E6EC] bg-white text-xs">
                            <option value="restaurant_logo" {{ ($settings['receipt_logo_source'] ?? 'restaurant_logo') === 'restaurant_logo' ? 'selected' : '' }}>Primary Logo</option>
                            <option value="restaurant_logo_dark" {{ ($settings['receipt_logo_source'] ?? '') === 'restaurant_logo_dark' ? 'selected' : '' }}>Dark Logo</option>
                            <option value="restaurant_logo_light" {{ ($settings['receipt_logo_source'] ?? '') === 'restaurant_logo_light' ? 'selected' : '' }}>Light / Monochrome Logo</option>
                        </select>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Restaurant Name</span>
                            <span class="text-[10px] text-gray-400">Print restaurant title</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_restaurant_name" value="1" {{ ($settings['receipt_show_restaurant_name'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Restaurant Address</span>
                            <span class="text-[10px] text-gray-400">Print outlet street & city</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_address" value="1" {{ ($settings['receipt_show_address'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Phone / Contact</span>
                            <span class="text-[10px] text-gray-400">Print restaurant hotline</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_phone" value="1" {{ ($settings['receipt_show_phone'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Order Metadata & Customer Section -->
            <div class="p-5 rounded-2xl border border-gray-200 bg-gray-50/60 space-y-4">
                <h4 class="font-extrabold text-[#25232A] text-xs uppercase tracking-wider flex items-center gap-2">
                    <span>👤</span> Customer & Order Metadata
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Order Number</span>
                            <span class="text-[10px] text-gray-400">Print #M2W-XXXXX</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_order_number" value="1" {{ ($settings['receipt_show_order_number'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Order Date & Time</span>
                            <span class="text-[10px] text-gray-400">Print order placement timestamp</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_order_date" value="1" {{ ($settings['receipt_show_order_date'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Customer Name</span>
                            <span class="text-[10px] text-gray-400">Print recipient name</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_customer_name" value="1" {{ ($settings['receipt_show_customer_name'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Customer Phone</span>
                            <span class="text-[10px] text-gray-400">Print customer phone number</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_customer_phone" value="1" {{ ($settings['receipt_show_customer_phone'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between sm:col-span-2">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Delivery Address (Delivery orders only)</span>
                            <span class="text-[10px] text-gray-400">Print street address & delivery instructions</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_delivery_address" value="1" {{ ($settings['receipt_show_delivery_address'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Items & Pricing Display -->
            <div class="p-5 rounded-2xl border border-gray-200 bg-gray-50/60 space-y-4">
                <h4 class="font-extrabold text-[#25232A] text-xs uppercase tracking-wider flex items-center gap-2">
                    <span>🍲</span> Item Line Details & Totals Breakdown
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Unit Prices</span>
                            <span class="text-[10px] text-gray-400">Print item rate column</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_unit_prices" value="1" {{ ($settings['receipt_show_unit_prices'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Line Totals</span>
                            <span class="text-[10px] text-gray-400">Print line total amount</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_line_totals" value="1" {{ ($settings['receipt_show_line_totals'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Items Subtotal</span>
                            <span class="text-[10px] text-gray-400">Print items subtotal</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_subtotal" value="1" {{ ($settings['receipt_show_subtotal'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Discounts</span>
                            <span class="text-[10px] text-gray-400">Print coupon / promotion deduction</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_discounts" value="1" {{ ($settings['receipt_show_discounts'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Delivery Charge</span>
                            <span class="text-[10px] text-gray-400">Print delivery fee row</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_delivery_charge" value="1" {{ ($settings['receipt_show_delivery_charge'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Packaging Charge</span>
                            <span class="text-[10px] text-gray-400">Print container fee row</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_packaging_charge" value="1" {{ ($settings['receipt_show_packaging_charge'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Tax / GST Details</span>
                            <span class="text-[10px] text-gray-400">Print tax breakdown row</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_tax" value="1" {{ ($settings['receipt_show_tax'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Grand Total</span>
                            <span class="text-[10px] text-gray-400">Print final payable total row</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_grand_total" value="1" {{ ($settings['receipt_show_grand_total'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Payment Method & Status</span>
                            <span class="text-[10px] text-gray-400">Print COD / Razorpay & Paid/Pending</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_payment_details" value="1" {{ ($settings['receipt_show_payment_details'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#25232A] block">Show Customer & Kitchen Notes</span>
                            <span class="text-[10px] text-gray-400">Print cooking / delivery instructions</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="receipt_show_notes" value="1" {{ ($settings['receipt_show_notes'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Footer Message -->
            <div class="p-5 rounded-2xl border border-gray-200 bg-gray-50/60 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-[#25232A] text-xs uppercase tracking-wider flex items-center gap-2">
                            <span>💬</span> Custom Receipt Footer Message
                        </h4>
                        <p class="text-[11px] text-gray-500">Add a cordial greeting, Wi-Fi password, or social handle at receipt bottom.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="receipt_show_footer_message" value="1" {{ ($settings['receipt_show_footer_message'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Footer Message Text</label>
                    <textarea name="receipt_footer_message" rows="3" class="w-full p-3 rounded-2xl border border-[#F0E6EC] focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070] bg-white font-mono text-xs">{{ $settings['receipt_footer_message'] ?? "Thank you for ordering with My 2nd Wife!\nPlease visit us again." }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                <a href="{{ route('admin.settings.receipt.test') }}" target="_blank" 
                   class="text-xs font-bold text-gray-600 hover:text-[#F50070] flex items-center gap-1.5">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Preview Current Receipt</span>
                </a>

                <button type="submit" class="px-7 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all">
                    Save Receipt Settings
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 12: HOMEPAGE BANNERS -->
    <div x-show="activeTab === 'banners'" x-cloak class="space-y-6">
        
        <!-- Quick Hub to 3 Independent Sections -->
        <div class="p-4 rounded-2xl bg-gradient-to-r from-pink-500/10 via-rose-500/5 to-purple-500/10 border border-[#F50070]/20 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="p-2.5 rounded-xl bg-[#F50070] text-white">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </span>
                <div>
                    <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Dedicated Homepage Banners Suite</h4>
                    <p class="text-[11px] text-gray-500">Manage all 3 independent areas (Main Hero 20:7 Banner, Offer Banner 1, and Offer Banner 2) with dedicated image uploads and live previews.</p>
                </div>
            </div>
            <a href="{{ route('admin.hero-banners.index') }}" 
               class="px-5 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white font-bold text-xs flex items-center gap-1.5 shrink-0 shadow-md shadow-pink-500/20">
                <span>Open Homepage Banners</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

        <!-- Header Card -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xl">🖼️</span>
                    <h3 class="font-extrabold text-[#25232A] text-lg">Promotional Homepage Banners</h3>
                    <span class="text-xs font-bold text-[#F50070] bg-[#FFF0F6] px-2.5 py-0.5 rounded-full border border-pink-100">
                        {{ $banners->count() }} {{ Str::plural('Banner', $banners->count()) }}
                    </span>
                </div>
                <p class="text-gray-500 text-xs mt-1">Manage promotional food sliders and pastel cards displayed on the customer landing page. Configure headlines, offers, order buttons, and upload custom food imagery.</p>
            </div>
            <button type="button" @click="addBannerModal = true" class="px-5 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95 shrink-0">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add New Banner</span>
            </button>
        </div>

        <!-- Live Customer Homepage Preview Header -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h4 class="font-extrabold text-[#25232A] text-sm flex items-center gap-2">
                        <span>👁️</span> Live Customer Homepage Preview
                    </h4>
                    <p class="text-[11px] text-gray-500">Real-time simulation of how active promotional banners appear to customers on mobile and desktop.</p>
                </div>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                    Active Banners ({{ $banners->where('is_active', true)->count() }})
                </span>
            </div>

            <!-- Customer Preview Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($banners->where('is_active', true) as $banner)
                    @php
                        $bgClass = 'bg-[#E8F8F2] border-[#D5EFE5]';
                        $badgeColor = 'text-[#FF006B]';
                        if ($banner->bg_gradient === 'peach' || $loop->iteration % 2 === 0) {
                            $bgClass = 'bg-[#FFF6E9] border-[#FBEAD2]';
                            $badgeColor = 'text-[#FF8A26]';
                        } elseif ($banner->bg_gradient === 'rose') {
                            $bgClass = 'bg-[#FFF3F8] border-[#FFE4F0]';
                            $badgeColor = 'text-[#F50070]';
                        } elseif ($banner->bg_gradient === 'lavender') {
                            $bgClass = 'bg-[#F3E8FF] border-[#E9D5FF]';
                            $badgeColor = 'text-[#9333EA]';
                        }
                    @endphp
                    <div class="rounded-2xl p-4 sm:p-5 {{ $bgClass }} border flex items-center justify-between gap-4 shadow-xs relative overflow-hidden group">
                        <div class="space-y-1.5 z-10 max-w-[65%]">
                            @if($banner->badge_text)
                                <span class="text-xs font-bold {{ $badgeColor }} block">{{ $banner->badge_text }}</span>
                            @endif
                            <h4 class="text-xs sm:text-sm font-bold text-[#1F1F39] leading-tight">
                                {{ $banner->title }}
                            </h4>
                            @if($banner->subtitle)
                                <p class="text-[11px] text-gray-600 line-clamp-1">{{ $banner->subtitle }}</p>
                            @endif
                            <div class="pt-2">
                                <span class="inline-block px-3.5 py-1 rounded-full border border-[#1F1F39] text-[#1F1F39] font-bold text-[11px]">
                                    {{ $banner->button_text ?: 'Order Now' }} &rarr;
                                </span>
                            </div>
                        </div>
                        <div class="w-24 h-20 sm:w-28 sm:h-24 shrink-0 rounded-xl overflow-hidden bg-white/40 flex items-center justify-center p-1 border border-white/60">
                            <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" 
                                 class="w-full h-full object-contain"
                                 onerror="this.src='{{ asset('images/defaults/banner-placeholder.jpg') }}'">
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 text-center py-6 text-gray-400 text-xs">
                        No active promotional banners enabled. Enable or add banners below.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Banners List & Management Card -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h4 class="font-extrabold text-[#25232A] text-sm flex items-center gap-2">
                    <span>📋</span> All Promotional Banners ({{ $banners->count() }})
                </h4>
                <p class="text-[11px] text-gray-500 hidden sm:block">Click edit to update details or replace image. Click status to toggle active state.</p>
            </div>

            <div class="space-y-3">
                @forelse($banners as $banner)
                    <div class="p-4 rounded-2xl border {{ $banner->is_active ? 'border-[#F0E6EC] bg-white' : 'border-gray-200 bg-gray-50 opacity-75' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all hover:border-[#F50070]/40 hover:shadow-xs">
                        
                        <!-- Left: Thumbnail & Banner Info -->
                        <div class="flex items-center gap-3.5 min-w-0">
                            <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 font-extrabold text-[11px] flex items-center justify-center shrink-0">
                                #{{ $banner->display_order }}
                            </span>
                            <div class="w-20 h-14 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden shrink-0 flex items-center justify-center p-0.5">
                                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" 
                                     class="w-full h-full object-contain"
                                     onerror="this.src='{{ asset('images/defaults/banner-placeholder.jpg') }}'">
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="font-bold text-[#25232A] text-xs sm:text-sm truncate">{{ $banner->title }}</h4>
                                    @if($banner->badge_text)
                                        <span class="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-pink-50 text-[#F50070] border border-pink-200">
                                            {{ $banner->badge_text }}
                                        </span>
                                    @endif
                                    <span class="text-[9px] font-semibold px-2 py-0.5 rounded-full capitalize {{ $banner->bg_gradient === 'mint' ? 'bg-emerald-50 text-emerald-700' : ($banner->bg_gradient === 'peach' ? 'bg-amber-50 text-amber-700' : ($banner->bg_gradient === 'rose' ? 'bg-rose-50 text-rose-700' : 'bg-purple-50 text-purple-700')) }}">
                                        Theme: {{ $banner->bg_gradient ?? 'mint' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-500 truncate mt-0.5">{{ $banner->subtitle ?: 'No subtitle provided' }}</p>
                                <div class="flex items-center gap-3 text-[10px] text-gray-400 mt-1">
                                    <span>Button: <strong class="text-gray-600">{{ $banner->button_text ?: 'Order Now' }}</strong></span>
                                    <span>&bull;</span>
                                    <span>URL: <code class="text-pink-600 bg-pink-50 px-1 py-0.5 rounded">{{ $banner->button_url ?: '/menu' }}</code></span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Actions -->
                        <div class="flex items-center gap-2 self-end sm:self-center shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100 w-full sm:w-auto justify-end">
                            <!-- Toggle Active/Inactive -->
                            <form action="{{ route('admin.banners.toggle', $banner) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        title="{{ $banner->is_active ? 'Click to disable' : 'Click to enable' }}"
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold border transition-colors flex items-center gap-1 {{ $banner->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $banner->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    <span>{{ $banner->is_active ? 'Active' : 'Disabled' }}</span>
                                </button>
                            </form>

                            <!-- Edit Button -->
                            <button type="button" 
                                    @click="editBannerData = {
                                        id: {{ $banner->id }},
                                        title: '{{ addslashes($banner->title) }}',
                                        subtitle: '{{ addslashes($banner->subtitle ?? '') }}',
                                        badge_text: '{{ addslashes($banner->badge_text ?? '') }}',
                                        button_text: '{{ addslashes($banner->button_text ?? 'Order Now') }}',
                                        button_url: '{{ addslashes($banner->button_url ?? '/menu') }}',
                                        bg_gradient: '{{ $banner->bg_gradient ?? 'mint' }}',
                                        display_order: {{ $banner->display_order ?? 0 }},
                                        is_active: {{ $banner->is_active ? 'true' : 'false' }},
                                        image_url: '{{ $banner->image_url }}',
                                        update_url: '{{ route('admin.banners.update', $banner) }}'
                                    }; editBannerImagePreview = ''; editBannerModal = true;"
                                    class="p-2 text-gray-600 hover:text-[#F50070] rounded-xl hover:bg-pink-50 transition-colors border border-gray-200 text-xs font-bold flex items-center gap-1">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>Edit</span>
                            </button>

                            <!-- Delete Form -->
                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this promotional banner?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition-colors border border-gray-200" title="Delete Banner">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-gray-400 text-xs space-y-2">
                        <span class="text-3xl block">🖼️</span>
                        <p class="font-bold text-gray-600">No promotional banners configured yet.</p>
                        <p>Click "Add New Banner" above to create your first promotional banner for the homepage.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

        </div> <!-- Close Right: Settings Section Content Area -->
    </div> <!-- Close Main Responsive Layout: 2 Columns on Desktop -->

    <!-- Add Banner Modal -->
    <div x-show="addBannerModal" x-cloak class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div @click.away="addBannerModal = false" class="bg-white rounded-3xl p-6 sm:p-8 max-w-xl w-full space-y-5 shadow-2xl border border-[#F0E6EC] text-xs my-8 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">✨</span>
                    <h3 class="font-extrabold text-[#25232A] text-base">Add New Promotional Banner</h3>
                </div>
                <button type="button" @click="addBannerModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
            </div>

            <!-- Real-time Live Card Mockup inside Modal -->
            <div class="p-3 bg-gray-50 rounded-2xl border border-gray-200 space-y-1.5">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Real-time Banner Preview</span>
                <div class="rounded-xl p-3.5 border flex items-center justify-between gap-3 shadow-2xs relative overflow-hidden"
                     :class="addBannerForm.bg_gradient === 'peach' ? 'bg-[#FFF6E9] border-[#FBEAD2]' : (addBannerForm.bg_gradient === 'rose' ? 'bg-[#FFF3F8] border-[#FFE4F0]' : (addBannerForm.bg_gradient === 'lavender' ? 'bg-[#F3E8FF] border-[#E9D5FF]' : 'bg-[#E8F8F2] border-[#D5EFE5]'))">
                    <div class="space-y-1 max-w-[65%]">
                        <span class="text-[11px] font-bold block"
                              :class="addBannerForm.bg_gradient === 'peach' ? 'text-[#FF8A26]' : (addBannerForm.bg_gradient === 'lavender' ? 'text-[#9333EA]' : 'text-[#FF006B]')"
                              x-text="addBannerForm.badge_text || 'Offer Badge'"></span>
                        <h4 class="text-xs font-bold text-[#1F1F39] leading-tight" x-text="addBannerForm.title || 'Banner Headline'"></h4>
                        <p class="text-[10px] text-gray-600 line-clamp-1" x-text="addBannerForm.subtitle || 'Short offer description'"></p>
                        <span class="inline-block px-2.5 py-0.5 rounded-full border border-[#1F1F39] text-[#1F1F39] font-bold text-[10px] mt-1" x-text="addBannerForm.button_text || 'Order Now'"></span>
                    </div>
                    <div class="w-16 h-14 rounded-lg overflow-hidden bg-white/50 border border-white/60 flex items-center justify-center p-0.5 shrink-0">
                        <template x-if="addBannerForm.preview_image">
                            <img :src="addBannerForm.preview_image" class="w-full h-full object-contain">
                        </template>
                        <template x-if="!addBannerForm.preview_image">
                            <img src="{{ asset('images/banners/banner-biryani.svg') }}" class="w-full h-full object-contain">
                        </template>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Headline Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" x-model="addBannerForm.title" required placeholder="Flat 20% off on Dum Biryani & Starters" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Subtitle / Description</label>
                    <input type="text" name="subtitle" x-model="addBannerForm.subtitle" placeholder="Slow dum-cooked with pure ghee, royal saffron, and aromatic spices" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Badge Text / Offer Callout</label>
                        <input type="text" name="badge_text" x-model="addBannerForm.badge_text" placeholder="Uplifting anytime" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070]">
                    </div>

                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Order Button Text</label>
                        <input type="text" name="button_text" x-model="addBannerForm.button_text" value="Order Now" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Button Destination URL / Route</label>
                        <input type="text" name="button_url" x-model="addBannerForm.button_url" value="/menu" placeholder="/menu?category=biryani" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070]">
                    </div>

                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Color Theme</label>
                        <select name="bg_gradient" x-model="addBannerForm.bg_gradient" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white">
                            <option value="mint">🌿 Mint Pastel (#E8F8F2)</option>
                            <option value="peach">🍑 Cream Peach (#FFF6E9)</option>
                            <option value="rose">🌸 Rose Pink (#FFF3F8)</option>
                            <option value="lavender">💜 Lavender Pastel (#F3E8FF)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Display Order</label>
                        <input type="number" name="display_order" x-model="addBannerForm.display_order" min="0" value="0" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                    </div>

                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded text-[#F50070] focus:ring-[#F50070]">
                            <span class="font-bold text-[#25232A]">Enable Banner Immediately</span>
                        </label>
                    </div>
                </div>

                <!-- Banner Image Upload with Live Preview -->
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Banner Food Image (PNG, JPG, SVG, WebP up to 4MB)</label>
                    <input type="file" name="image" accept="image/*" 
                           @change="previewFile($event, 'addBannerForm.preview_image')"
                           class="w-full p-2 rounded-xl border border-[#F0E6EC] file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#FFF3F8] file:text-[#F50070]">
                    <p class="text-[10px] text-gray-400 mt-1">Recommended: Clean isolated food dish with transparent background (like Biryani handi, Kathi roll, Thali).</p>
                </div>

                <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" @click="addBannerModal = false" class="px-4 py-2 rounded-full text-gray-500 font-bold hover:bg-gray-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-sm transition-all">Save & Publish Banner</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Banner Modal -->
    <div x-show="editBannerModal" x-cloak class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div @click.away="editBannerModal = false" class="bg-white rounded-3xl p-6 sm:p-8 max-w-xl w-full space-y-5 shadow-2xl border border-[#F0E6EC] text-xs my-8 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">✏️</span>
                    <h3 class="font-extrabold text-[#25232A] text-base">Edit Promotional Banner</h3>
                </div>
                <button type="button" @click="editBannerModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
            </div>

            <!-- Real-time Live Card Mockup for Edit -->
            <div class="p-3 bg-gray-50 rounded-2xl border border-gray-200 space-y-1.5">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Live Banner Preview</span>
                <div class="rounded-xl p-3.5 border flex items-center justify-between gap-3 shadow-2xs relative overflow-hidden"
                     :class="editBannerData.bg_gradient === 'peach' ? 'bg-[#FFF6E9] border-[#FBEAD2]' : (editBannerData.bg_gradient === 'rose' ? 'bg-[#FFF3F8] border-[#FFE4F0]' : (editBannerData.bg_gradient === 'lavender' ? 'bg-[#F3E8FF] border-[#E9D5FF]' : 'bg-[#E8F8F2] border-[#D5EFE5]'))">
                    <div class="space-y-1 max-w-[65%]">
                        <span class="text-[11px] font-bold block"
                              :class="editBannerData.bg_gradient === 'peach' ? 'text-[#FF8A26]' : (editBannerData.bg_gradient === 'lavender' ? 'text-[#9333EA]' : 'text-[#FF006B]')"
                              x-text="editBannerData.badge_text || 'Offer Badge'"></span>
                        <h4 class="text-xs font-bold text-[#1F1F39] leading-tight" x-text="editBannerData.title || 'Banner Headline'"></h4>
                        <p class="text-[10px] text-gray-600 line-clamp-1" x-text="editBannerData.subtitle || 'Short offer description'"></p>
                        <span class="inline-block px-2.5 py-0.5 rounded-full border border-[#1F1F39] text-[#1F1F39] font-bold text-[10px] mt-1" x-text="editBannerData.button_text || 'Order Now'"></span>
                    </div>
                    <div class="w-16 h-14 rounded-lg overflow-hidden bg-white/50 border border-white/60 flex items-center justify-center p-0.5 shrink-0">
                        <template x-if="editBannerImagePreview">
                            <img :src="editBannerImagePreview" class="w-full h-full object-contain">
                        </template>
                        <template x-if="!editBannerImagePreview">
                            <img :src="editBannerData.image_url" class="w-full h-full object-contain">
                        </template>
                    </div>
                </div>
            </div>

            <form :action="editBannerData.update_url" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Headline Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" x-model="editBannerData.title" required class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Subtitle / Description</label>
                    <input type="text" name="subtitle" x-model="editBannerData.subtitle" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Badge Text / Offer Callout</label>
                        <input type="text" name="badge_text" x-model="editBannerData.badge_text" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070]">
                    </div>

                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Order Button Text</label>
                        <input type="text" name="button_text" x-model="editBannerData.button_text" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Button Destination URL / Route</label>
                        <input type="text" name="button_url" x-model="editBannerData.button_url" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:ring-1 focus:ring-[#F50070]">
                    </div>

                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Color Theme</label>
                        <select name="bg_gradient" x-model="editBannerData.bg_gradient" class="w-full p-2.5 rounded-xl border border-[#F0E6EC] bg-white">
                            <option value="mint">🌿 Mint Pastel (#E8F8F2)</option>
                            <option value="peach">🍑 Cream Peach (#FFF6E9)</option>
                            <option value="rose">🌸 Rose Pink (#FFF3F8)</option>
                            <option value="lavender">💜 Lavender Pastel (#F3E8FF)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Display Order</label>
                        <input type="number" name="display_order" x-model="editBannerData.display_order" min="0" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                    </div>

                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" :checked="editBannerData.is_active" class="rounded text-[#F50070] focus:ring-[#F50070]">
                            <span class="font-bold text-[#25232A]">Banner Active (Visible to Customers)</span>
                        </label>
                    </div>
                </div>

                <!-- Replace Banner Image -->
                <div class="p-3 bg-gray-50 rounded-2xl border border-gray-200 space-y-2">
                    <label class="block font-bold text-[#25232A]">Replace Banner Image (Optional)</label>
                    <div class="flex items-center gap-3">
                        <div class="w-16 h-12 rounded-xl bg-white border border-gray-200 overflow-hidden shrink-0 flex items-center justify-center p-0.5">
                            <template x-if="editBannerImagePreview">
                                <img :src="editBannerImagePreview" class="w-full h-full object-contain">
                            </template>
                            <template x-if="!editBannerImagePreview">
                                <img :src="editBannerData.image_url" class="w-full h-full object-contain">
                            </template>
                        </div>
                        <input type="file" name="image" accept="image/*" 
                               @change="previewFile($event, 'editBannerImagePreview')"
                               class="w-full p-1.5 rounded-xl border border-[#F0E6EC] bg-white file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#FFF3F8] file:text-[#F50070]">
                    </div>
                    <p class="text-[10px] text-gray-400">Leave blank to keep existing image.</p>
                </div>

                <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" @click="editBannerModal = false" class="px-4 py-2 rounded-full text-gray-500 font-bold hover:bg-gray-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-sm transition-all">Update Banner</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function restaurantSettings(config) {
    return {
        activeTab: config.activeTab,
        mobileDropdownOpen: false,
        addBannerModal: false,
        editBannerModal: false,
        editBannerData: {
            id: null,
            title: '',
            subtitle: '',
            badge_text: '',
            button_text: 'Order Now',
            button_url: '/menu',
            bg_gradient: 'mint',
            display_order: 0,
            is_active: true,
            image_url: '',
            update_url: ''
        },
        addBannerForm: {
            title: '',
            subtitle: '',
            badge_text: 'Uplifting anytime',
            button_text: 'Order Now',
            button_url: '/menu',
            bg_gradient: 'mint',
            display_order: 0,
            is_active: true,
            preview_image: ''
        },
        previewLogo: config.previewLogo,
        previewFavicon: config.previewFavicon,
        previewCover: config.previewCover,
        editBannerImagePreview: '',

        init() {
            const hash = window.location.hash.replace('#', '');
            if (hash === 'footer') {
                window.location.href = config.footerSettingsUrl;
                return;
            }
            const validTabs = ['general', 'branding', 'contact', 'google_maps', 'delivery', 'hours', 'orders', 'payment', 'receipt', 'notifications', 'seo', 'legal', 'banners'];
            if (hash && validTabs.includes(hash)) {
                this.activeTab = hash;
            }
        },

        switchTab(tab) {
            if (tab === 'footer') {
                window.location.href = config.footerSettingsUrl;
                return;
            }
            this.activeTab = tab;
            window.location.hash = tab;
            this.mobileDropdownOpen = false;
        },

        previewFile(event, targetProp) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this[targetProp] = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        handleFirebaseServiceAccountFile(event) {
            const file = event.target.files[0];
            const statusBox = document.getElementById('firebaseJsonStatusMsg');
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const parsed = JSON.parse(e.target.result);
                    const missing = [];
                    if (!parsed.project_id) missing.push('project_id');
                    if (!parsed.client_email) missing.push('client_email');
                    if (!parsed.private_key) missing.push('private_key');

                    if (missing.length > 0) {
                        if (statusBox) {
                            statusBox.className = 'text-[11px] font-bold p-3 rounded-xl mt-2 bg-rose-50 text-rose-800 border border-rose-200 block';
                            statusBox.innerHTML = '⚠️ Invalid Service Account file: missing required keys (' + missing.join(', ') + '). Existing configuration preserved.';
                        }
                        return;
                    }

                    // Populate fields
                    const projInput = document.querySelector('input[name="firebase_project_id"]');
                    const emailInput = document.querySelector('input[name="firebase_client_email"]');
                    const keyArea = document.querySelector('textarea[name="firebase_private_key"]');

                    if (projInput) projInput.value = parsed.project_id;
                    if (emailInput) emailInput.value = parsed.client_email;
                    if (keyArea) keyArea.value = '•••••••••••••••••••••••• (RSA Private Key Loaded from File)';

                    if (statusBox) {
                        statusBox.className = 'text-[11px] font-bold p-3 rounded-xl mt-2 bg-emerald-50 text-emerald-800 border border-emerald-200 block';
                        statusBox.innerHTML = '✅ Service Account JSON parsed successfully! Project ID: <strong>' + parsed.project_id + '</strong> and Client Email loaded. Click <strong>Save Settings</strong> below to persist securely on the server.';
                    }
                } catch (err) {
                    if (statusBox) {
                        statusBox.className = 'text-[11px] font-bold p-3 rounded-xl mt-2 bg-rose-50 text-rose-800 border border-rose-200 block';
                        statusBox.innerHTML = '⚠️ Failed to parse file as JSON: ' + err.message;
                    }
                }
            };
            reader.readAsText(file);
        }
    };
}
</script>
@endsection
