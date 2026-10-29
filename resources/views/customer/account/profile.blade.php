@extends('layouts.app')

@section('title', 'My Account Profile | My 2nd Wife')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-24 sm:pb-12 space-y-6"
     x-data="{ 
         activeTab: (window.location.hash && ['overview', 'edit', 'orders', 'addresses'].includes(window.location.hash.replace('#', ''))) 
                    ? window.location.hash.replace('#', '') 
                    : '{{ session('errors') ? 'edit' : 'overview' }}',
         setActiveTab(tab) {
             this.activeTab = tab;
             if (window.history.replaceState) {
                 window.history.replaceState(null, null, '#' + tab);
             }
         }
     }">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">✕</button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                <span>{{ session('error') ?? ($errors->first() ?? 'Please correct the highlighted errors.') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-900">✕</button>
        </div>
    @endif

    <!-- Profile Header Card (Actual Customer Data) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#EFF0F6] shadow-sm relative overflow-hidden">
        <!-- Decorative pink background glow -->
        <div class="absolute -right-16 -top-16 w-52 h-52 bg-pink-50 rounded-full blur-2xl pointer-events-none opacity-60"></div>
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 relative z-10">
            <!-- Avatar & Customer Info -->
            <div class="flex items-center gap-4 sm:gap-5">
                <!-- Avatar circle with initials -->
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-tr from-[#FF006B] to-[#F50070] text-white flex items-center justify-center text-2xl sm:text-3xl font-black font-rubik shadow-lg shadow-pink-500/20 ring-4 ring-[#FFF0F6] shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold text-[#1F1F39] font-rubik leading-tight">{{ $user->name }}</h1>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i data-lucide="shield-check" class="w-3 h-3 text-emerald-600"></i>
                            <span>Verified</span>
                        </span>
                    </div>

                    <!-- Contact details -->
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-[#6E7191]">
                        <span class="flex items-center gap-1.5 font-medium text-[#1F1F39]">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-[#FF006B]"></i>
                            <span>{{ $user->phone }}</span>
                        </span>
                        @if($user->email)
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-[#6E7191]"></i>
                                <span>{{ $user->email }}</span>
                            </span>
                        @endif
                    </div>

                    <p class="text-[11px] text-gray-400">
                        Customer since {{ $user->created_at->format('M Y') }}
                    </p>
                </div>
            </div>

            <!-- Stats & Edit Action -->
            <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end border-t sm:border-t-0 pt-4 sm:pt-0 border-gray-100">
                <div class="flex items-center gap-2 sm:gap-3 text-center">
                    <div class="px-3.5 py-2 rounded-2xl bg-[#F7F7FC] border border-[#EFF0F6]">
                        <span class="text-base sm:text-lg font-black text-[#1F1F39] font-rubik block leading-tight">{{ $totalOrdersCount }}</span>
                        <span class="text-[10px] text-[#6E7191] font-semibold uppercase tracking-wider">Orders</span>
                    </div>
                    <div class="px-3.5 py-2 rounded-2xl bg-[#F7F7FC] border border-[#EFF0F6]">
                        <span class="text-base sm:text-lg font-black text-[#1F1F39] font-rubik block leading-tight">{{ $addresses->count() }}</span>
                        <span class="text-[10px] text-[#6E7191] font-semibold uppercase tracking-wider">Addresses</span>
                    </div>
                </div>

                <button type="button" 
                        @click="setActiveTab('edit')"
                        class="px-5 py-2.5 rounded-full bg-[#FFF0F6] text-[#FF006B] hover:bg-[#FF006B] hover:text-white font-bold text-xs transition-all border border-[#FF006B]/30 shadow-2xs flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    <span>Edit Profile</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Navigation & Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Sidebar Navigation Menu (4 cols) -->
        <div class="lg:col-span-4 bg-white p-4 rounded-3xl border border-[#EFF0F6] shadow-sm space-y-1.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 px-3 py-1 block">Account Menu</span>

            <!-- Tab 1: Overview -->
            <button type="button" @click="setActiveTab('overview')"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs font-bold transition-all text-left"
                    :class="activeTab === 'overview' ? 'bg-[#FFF0F6] text-[#FF006B] shadow-2xs' : 'text-[#6E7191] hover:text-[#1F1F39] hover:bg-[#F7F7FC]'">
                <div class="flex items-center gap-3">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Account Overview</span>
                </div>
                <span class="text-xs">&rarr;</span>
            </button>

            <!-- Tab 2: Edit Profile & Security -->
            <button type="button" @click="setActiveTab('edit')"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs font-bold transition-all text-left"
                    :class="activeTab === 'edit' ? 'bg-[#FFF0F6] text-[#FF006B] shadow-2xs' : 'text-[#6E7191] hover:text-[#1F1F39] hover:bg-[#F7F7FC]'">
                <div class="flex items-center gap-3">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span>Personal Information & Security</span>
                </div>
                <span class="text-xs">&rarr;</span>
            </button>

            <!-- Tab 3: Order History -->
            <button type="button" @click="setActiveTab('orders')"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs font-bold transition-all text-left"
                    :class="activeTab === 'orders' ? 'bg-[#FFF0F6] text-[#FF006B] shadow-2xs' : 'text-[#6E7191] hover:text-[#1F1F39] hover:bg-[#F7F7FC]'">
                <div class="flex items-center gap-3">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span>Recent Order History</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black"
                      :class="activeTab === 'orders' ? 'bg-[#FF006B] text-white' : 'bg-gray-100 text-gray-600'"
                      x-text="'{{ $totalOrdersCount }}'">
                </span>
            </button>

            <!-- Tab 4: Saved Addresses -->
            <button type="button" @click="setActiveTab('addresses')"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs font-bold transition-all text-left"
                    :class="activeTab === 'addresses' ? 'bg-[#FFF0F6] text-[#FF006B] shadow-2xs' : 'text-[#6E7191] hover:text-[#1F1F39] hover:bg-[#F7F7FC]'">
                <div class="flex items-center gap-3">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>Saved Delivery Addresses</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black"
                      :class="activeTab === 'addresses' ? 'bg-[#FF006B] text-white' : 'bg-gray-100 text-gray-600'"
                      x-text="'{{ $addresses->count() }}'">
                </span>
            </button>

            <!-- Dedicated Links & Logout -->
            <div class="pt-3 border-t border-gray-100 space-y-1.5">
                <a href="{{ route('account.orders') }}" 
                   class="flex items-center justify-between px-4 py-2.5 rounded-2xl text-xs font-semibold text-[#6E7191] hover:text-[#1F1F39] hover:bg-[#F7F7FC] transition-colors">
                    <div class="flex items-center gap-3">
                        <i data-lucide="history" class="w-4 h-4"></i>
                        <span>View All Orders Page</span>
                    </div>
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-gray-400"></i>
                </a>

                <a href="{{ route('account.addresses') }}" 
                   class="flex items-center justify-between px-4 py-2.5 rounded-2xl text-xs font-semibold text-[#6E7191] hover:text-[#1F1F39] hover:bg-[#F7F7FC] transition-colors">
                    <div class="flex items-center gap-3">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Manage All Addresses</span>
                    </div>
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-gray-400"></i>
                </a>

                <form action="{{ route('logout') }}" method="POST" class="pt-2">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-2xl text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-100 hover:border-rose-200 transition-colors cursor-pointer">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Logout from Account</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Content Area (8 cols) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- ============================================== -->
            <!-- SECTION 1: ACCOUNT OVERVIEW                    -->
            <!-- ============================================== -->
            <div x-show="activeTab === 'overview'" class="space-y-6">
                <!-- Personal & Contact Snapshot Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Personal Info Card -->
                    <div class="bg-white p-6 rounded-3xl border border-[#EFF0F6] shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#6E7191] font-rubik flex items-center gap-1.5">
                                <i data-lucide="user" class="w-4 h-4 text-primary"></i>
                                <span>Personal Information</span>
                            </span>
                            <button type="button" @click="setActiveTab('edit')" class="text-[11px] font-bold text-[#FF006B] hover:underline">
                                Edit
                            </button>
                        </div>
                        <div class="space-y-1 text-xs">
                            <span class="text-gray-400 block text-[10px] uppercase font-bold">Registered Name</span>
                            <p class="text-[#1F1F39] font-bold text-sm">{{ $user->name }}</p>
                        </div>
                        <div class="space-y-1 text-xs pt-1">
                            <span class="text-gray-400 block text-[10px] uppercase font-bold">Account Role</span>
                            <p class="text-[#1F1F39] font-semibold capitalize">{{ str_replace('_', ' ', $user->role ?? 'Customer') }}</p>
                        </div>
                    </div>

                    <!-- Contact Info Card -->
                    <div class="bg-white p-6 rounded-3xl border border-[#EFF0F6] shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#6E7191] font-rubik flex items-center gap-1.5">
                                <i data-lucide="phone-call" class="w-4 h-4 text-primary"></i>
                                <span>Contact Details</span>
                            </span>
                            <button type="button" @click="setActiveTab('edit')" class="text-[11px] font-bold text-[#FF006B] hover:underline">
                                Update
                            </button>
                        </div>
                        <div class="space-y-1 text-xs">
                            <span class="text-gray-400 block text-[10px] uppercase font-bold">Primary Mobile Number</span>
                            <p class="text-[#1F1F39] font-bold text-sm">{{ $user->phone }}</p>
                        </div>
                        <div class="space-y-1 text-xs pt-1">
                            <span class="text-gray-400 block text-[10px] uppercase font-bold">Email Address</span>
                            <p class="text-[#1F1F39] font-semibold">{{ $user->email ?: 'Not registered' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders Snapshot -->
                <div class="bg-white p-6 sm:p-7 rounded-3xl border border-[#EFF0F6] shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="shopping-bag" class="w-4 h-4 text-primary"></i>
                            <h3 class="font-bold text-[#1F1F39] text-sm font-rubik">Recent Food Orders</h3>
                        </div>
                        <button type="button" @click="setActiveTab('orders')" class="text-xs font-bold text-[#FF006B] hover:underline">
                            View All ({{ $totalOrdersCount }}) &rarr;
                        </button>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse($recentOrders as $order)
                        <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-[#1F1F39] font-mono text-sm">#{{ $order->order_number }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide border {{ $order->status_badge_color }}">
                                        {{ $order->status_label }}
                                    </span>
                                </div>
                                <p class="text-gray-400 text-[11px]">{{ $order->created_at->format('d M, Y \a\t h:i A') }} • {{ $order->items->count() }} item(s)</p>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0">
                                <span class="font-black text-[#1F1F39] text-sm font-rubik">₹{{ number_format($order->total_amount, 2) }}</span>
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('order.track', ['token' => $order->tracking_token]) }}" 
                                       class="px-3 py-1.5 rounded-xl bg-[#FFF0F6] text-[#FF006B] hover:bg-[#FF006B] hover:text-white font-bold text-xs transition-colors border border-pink-200">
                                        Track Live
                                    </a>
                                    <a href="{{ route('account.orders.details', ['orderNumber' => $order->order_number]) }}" 
                                       class="px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-[#1F1F39] font-bold text-xs transition-colors">
                                        Details
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="py-8 text-center text-gray-400 space-y-2">
                            <i data-lucide="package-open" class="w-8 h-8 mx-auto text-gray-300"></i>
                            <p class="text-xs">No food orders placed yet.</p>
                            <a href="{{ route('menu.index') }}" class="inline-block px-4 py-2 rounded-full bg-[#FF006B] text-white text-xs font-bold hover:bg-[#E0005E] transition-colors">
                                Explore Restaurant Menu
                            </a>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Saved Address Preview -->
                <div class="bg-white p-6 sm:p-7 rounded-3xl border border-[#EFF0F6] shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-primary"></i>
                            <h3 class="font-bold text-[#1F1F39] text-sm font-rubik">Primary Delivery Address</h3>
                        </div>
                        <button type="button" @click="setActiveTab('addresses')" class="text-xs font-bold text-[#FF006B] hover:underline">
                            Manage Addresses ({{ $addresses->count() }}) &rarr;
                        </button>
                    </div>

                    @if($addresses->count() > 0)
                        @php $defAddr = $addresses->first(); @endphp
                        <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 flex items-start justify-between gap-4 text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-white text-[#1F1F39] border border-gray-200">
                                        {{ $defAddr->type }}
                                    </span>
                                    @if($defAddr->is_default)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-pink-100 text-[#FF006B]">Default</span>
                                    @endif
                                </div>
                                <p class="font-bold text-[#1F1F39] text-sm">{{ $defAddr->contact_name }} • {{ $defAddr->contact_phone }}</p>
                                <p class="text-[#6E7191] leading-relaxed">{{ $defAddr->full_address }}</p>
                            </div>
                            <button type="button" @click="setActiveTab('addresses')" class="text-xs font-bold text-gray-500 hover:text-black">
                                Change
                            </button>
                        </div>
                    @else
                        <div class="py-6 text-center text-gray-400 space-y-2">
                            <p class="text-xs">No delivery address saved yet.</p>
                            <a href="{{ route('account.addresses') }}" class="inline-block px-4 py-2 rounded-full bg-[#FFF0F6] text-[#FF006B] border border-pink-200 text-xs font-bold hover:bg-[#FF006B] hover:text-white transition-colors">
                                + Add Delivery Address
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ============================================== -->
            <!-- SECTION 2: EDIT PROFILE & SECURITY             -->
            <!-- ============================================== -->
            <div x-show="activeTab === 'edit'" style="display: none;" class="space-y-6">
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#EFF0F6] shadow-sm space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h2 class="text-base font-bold text-[#1F1F39] font-rubik">Edit Profile & Account Security</h2>
                        <p class="text-xs text-[#6E7191] mt-0.5">Keep your contact details up to date for smooth food deliveries.</p>
                    </div>

                    <form action="{{ route('account.profile.update') }}" method="POST" class="space-y-5 text-xs">
                        @csrf

                        <!-- Full Name -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-[#1F1F39] font-rubik">
                                Full Customer Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full p-3 rounded-2xl border @error('name') border-rose-400 bg-rose-50/50 @else border-[#EFF0F6] @enderror focus:border-primary focus:outline-none text-[#1F1F39] text-xs font-medium">
                            @error('name')
                                <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email Address (Editable with Uniqueness Validation) -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block font-bold text-[#1F1F39] font-rubik">
                                    Registered Email Address <span class="text-rose-500">*</span>
                                </label>
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                        <i data-lucide="check" class="w-3 h-3 text-emerald-600"></i> Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                        <i data-lucide="clock" class="w-3 h-3 text-amber-600"></i> Pending Verification
                                    </span>
                                @endif
                            </div>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   placeholder="name@example.com"
                                   class="w-full p-3 rounded-2xl border @error('email') border-rose-400 bg-rose-50/50 @else border-[#EFF0F6] @enderror focus:border-primary focus:outline-none text-[#1F1F39] text-xs font-medium">
                            @error('email')
                                <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-[10px] text-gray-400">Changing your email address will update where order receipts and account notices are delivered.</p>
                        </div>

                        <!-- Mobile Phone with Default Country Code +91 -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-[#1F1F39] font-rubik">
                                Delivery Contact Mobile Phone <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex rounded-2xl border @error('phone') border-rose-400 bg-rose-50/50 @else border-[#EFF0F6] bg-white @enderror focus-within:border-primary focus-within:ring-2 focus-within:ring-pink-100 overflow-hidden shadow-2xs">
                                <span class="inline-flex items-center gap-1 px-3.5 bg-[#F7F7FC] border-r border-[#EFF0F6] text-xs font-bold text-[#1F1F39] select-none shrink-0">
                                    <span>🇮🇳</span>
                                    <span class="font-rubik">+91</span>
                                </span>
                                <input type="tel" name="phone" value="{{ old('phone', preg_replace('/^\+91\s*/', '', $user->phone)) }}" required
                                       placeholder="Enter 10-digit mobile number"
                                       class="w-full p-3 text-[#1F1F39] text-xs font-medium focus:outline-none bg-transparent">
                            </div>
                            @error('phone')
                                <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-[10px] text-gray-400">Our delivery executives use this phone number to contact you at delivery.</p>
                        </div>

                        <!-- Change Password Section -->
                        <div class="pt-6 border-t border-gray-100 space-y-4">
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#1F1F39] font-rubik">Change Password</h3>
                                <p class="text-[11px] text-[#6E7191]">Leave fields blank if you do not want to change your current password.</p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block font-bold text-[#1F1F39]" for="profile_current_password">Current Password</label>
                                <div x-data="{ show: false }" class="relative">
                                    <input :type="show ? 'text' : 'password'" id="profile_current_password" name="current_password" placeholder="Enter existing password"
                                           class="w-full p-3 pr-11 rounded-2xl border @error('current_password') border-rose-400 bg-rose-50/50 @else border-[#EFF0F6] @enderror focus:border-primary focus:outline-none text-xs transition-all">
                                    <button type="button" 
                                            @click="show = !show" 
                                            :aria-label="show ? 'Hide password' : 'Show password'"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors p-1 flex items-center justify-center focus:outline-none focus:text-primary cursor-pointer">
                                        <span x-show="!show" class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </span>
                                        <span x-show="show" x-cloak class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/>
                                                <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/>
                                                <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/>
                                                <line x1="2" x2="22" y1="2" y2="22"/>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                                @error('current_password')
                                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block font-bold text-[#1F1F39]" for="profile_new_password">New Password</label>
                                    <div x-data="{ show: false }" class="relative">
                                        <input :type="show ? 'text' : 'password'" id="profile_new_password" name="new_password" placeholder="Minimum 8 characters"
                                               class="w-full p-3 pr-11 rounded-2xl border @error('new_password') border-rose-400 bg-rose-50/50 @else border-[#EFF0F6] @enderror focus:border-primary focus:outline-none text-xs transition-all">
                                        <button type="button" 
                                                @click="show = !show" 
                                                :aria-label="show ? 'Hide password' : 'Show password'"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors p-1 flex items-center justify-center focus:outline-none focus:text-primary cursor-pointer">
                                            <span x-show="!show" class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </span>
                                            <span x-show="show" x-cloak class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/>
                                                    <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/>
                                                    <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/>
                                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                    @error('new_password')
                                        <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block font-bold text-[#1F1F39]" for="profile_new_password_confirmation">Confirm New Password</label>
                                    <div x-data="{ show: false }" class="relative">
                                        <input :type="show ? 'text' : 'password'" id="profile_new_password_confirmation" name="new_password_confirmation" placeholder="Re-enter new password"
                                               class="w-full p-3 pr-11 rounded-2xl border border-[#EFF0F6] focus:border-primary focus:outline-none text-xs transition-all">
                                        <button type="button" 
                                                @click="show = !show" 
                                                :aria-label="show ? 'Hide password' : 'Show password'"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors p-1 flex items-center justify-center focus:outline-none focus:text-primary cursor-pointer">
                                            <span x-show="!show" class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </span>
                                            <span x-show="show" x-cloak class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/>
                                                    <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/>
                                                    <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/>
                                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="pt-4 flex items-center justify-between border-t border-gray-100">
                            <button type="button" @click="setActiveTab('overview')" class="px-5 py-2.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-7 py-3 rounded-full bg-[#FF006B] hover:bg-[#E0005E] text-white font-bold text-xs shadow-md shadow-pink-500/20 transition-all cursor-pointer">
                                Save Profile Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- SECTION 3: ORDER HISTORY                       -->
            <!-- ============================================== -->
            <div x-show="activeTab === 'orders'" style="display: none;" class="space-y-4">
                <div class="bg-white p-6 sm:p-7 rounded-3xl border border-[#EFF0F6] shadow-sm flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#1F1F39] font-rubik">Your Order History</h2>
                        <p class="text-xs text-[#6E7191] mt-0.5">Track live food preparation or view past order receipts.</p>
                    </div>
                    <a href="{{ route('account.orders') }}" class="text-xs font-bold text-[#FF006B] hover:underline">
                        Open Full Orders Page &rarr;
                    </a>
                </div>

                <div class="space-y-4">
                    @forelse($recentOrders as $ord)
                    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-[#EFF0F6] shadow-sm space-y-4 hover:border-pink-200 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#EFF0F6] pb-3">
                            <div class="flex items-center gap-3">
                                <span class="text-base font-extrabold text-[#1F1F39] font-mono">#{{ $ord->order_number }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide border {{ $ord->status_badge_color }}">
                                    {{ $ord->status_label }}
                                </span>
                            </div>
                            <span class="text-xs text-[#6E7191]">{{ $ord->created_at->format('M d, Y • h:i A') }}</span>
                        </div>

                        <!-- Items preview list -->
                        <div class="text-xs text-[#6E7191] space-y-1">
                            @foreach($ord->items->take(3) as $it)
                                <p class="truncate">• <strong class="text-[#1F1F39]">{{ $it->quantity }}x</strong> {{ $it->product_name }} @if($it->variation_name) ({{ $it->variation_name }}) @endif</p>
                            @endforeach
                            @if($ord->items->count() > 3)
                                <p class="text-[11px] text-gray-400 font-semibold">+ {{ $ord->items->count() - 3 }} more delicious item(s)</p>
                            @endif
                        </div>

                        <!-- Footer actions & total -->
                        <div class="flex items-center justify-between pt-3 border-t border-[#EFF0F6]">
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase tracking-wider block font-bold">Total Amount</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg font-black text-primary font-rubik">₹{{ number_format($ord->total_amount, 2) }}</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $ord->isPaid() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                        {{ $ord->isPaid() ? 'Paid' : 'Cash on Delivery' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('order.track', ['token' => $ord->tracking_token]) }}" 
                                   class="px-4 py-2 rounded-full bg-[#FFF0F6] text-[#FF006B] font-bold text-xs hover:bg-[#FF006B] hover:text-white transition-colors border border-pink-200 shadow-2xs">
                                    Track Live
                                </a>
                                <a href="{{ route('account.orders.details', ['orderNumber' => $ord->order_number]) }}" 
                                   class="px-4 py-2 rounded-full bg-[#F7F7FC] text-[#1F1F39] font-bold text-xs hover:bg-gray-200 transition-colors border border-[#EFF0F6]">
                                    Details
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="bg-white p-12 rounded-3xl border border-[#EFF0F6] text-center space-y-3">
                        <div class="w-12 h-12 rounded-full bg-pink-50 text-primary flex items-center justify-center mx-auto text-xl border border-pink-100">
                            📦
                        </div>
                        <h3 class="text-base font-bold text-[#1F1F39] font-rubik">No Food Orders Yet</h3>
                        <p class="text-xs text-[#6E7191]">When you place your order, live preparation and tracking details appear here.</p>
                        <a href="{{ route('menu.index') }}" class="inline-block px-5 py-2.5 rounded-full bg-[#FF006B] text-white text-xs font-bold hover:bg-[#E0005E] transition-colors">
                            Explore Food Menu
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- ============================================== -->
            <!-- SECTION 4: SAVED DELIVERY ADDRESSES            -->
            <!-- ============================================== -->
            <div x-show="activeTab === 'addresses'" style="display: none;" class="space-y-4">
                <div class="bg-white p-6 sm:p-7 rounded-3xl border border-[#EFF0F6] shadow-sm flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#1F1F39] font-rubik">Saved Delivery Addresses</h2>
                        <p class="text-xs text-[#6E7191] mt-0.5">Quickly select a saved address for fast doorstep checkout.</p>
                    </div>
                    <a href="{{ route('account.addresses') }}" 
                       class="px-4 py-2 rounded-full bg-[#FF006B] text-white text-xs font-bold hover:bg-[#E0005E] transition-colors shadow-sm flex items-center gap-1.5">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Add Address</span>
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($addresses as $addr)
                    <div class="bg-white p-5 rounded-3xl border border-[#EFF0F6] shadow-sm flex items-start justify-between gap-4 hover:border-pink-200 transition-colors">
                        <div class="space-y-1.5 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-[#F7F7FC] text-[#1F1F39] border border-[#EFF0F6]">
                                    {{ $addr->type }}
                                </span>
                                @if($addr->is_default)
                                    <span class="text-[10px] font-bold text-primary bg-[#FFF0F6] px-2 py-0.5 rounded-full border border-pink-200">
                                        DEFAULT
                                    </span>
                                @endif
                            </div>
                            <p class="font-bold text-[#1F1F39] text-sm font-rubik">{{ $addr->contact_name }} • {{ $addr->contact_phone }}</p>
                            <p class="text-[#6E7191] leading-relaxed">{{ $addr->full_address }}</p>
                            @if($addr->delivery_instructions)
                                <p class="text-[11px] text-amber-700 italic bg-amber-50 p-2 rounded-xl mt-1">
                                    Instructions: {{ $addr->delivery_instructions }}
                                </p>
                            @endif
                        </div>

                        <form action="{{ route('account.addresses.destroy', $addr) }}" method="POST" onsubmit="return confirm('Remove this saved address?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-xl text-[#6E7191] hover:text-[#FB4E4E] hover:bg-rose-50 transition-colors cursor-pointer" title="Delete Address">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                    @empty
                    <div class="bg-white p-10 rounded-3xl border border-[#EFF0F6] text-center space-y-3">
                        <div class="w-12 h-12 rounded-full bg-[#F7F7FC] text-primary flex items-center justify-center mx-auto text-xl border border-[#EFF0F6]">
                            📍
                        </div>
                        <h3 class="text-base font-bold text-[#1F1F39] font-rubik">No Saved Addresses</h3>
                        <p class="text-xs text-[#6E7191]">Add your home or office address to enable instant one-click checkout.</p>
                        <a href="{{ route('account.addresses') }}" class="inline-block px-5 py-2.5 rounded-full bg-[#FF006B] text-white text-xs font-bold hover:bg-[#E0005E] transition-colors">
                            Add Delivery Address
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
