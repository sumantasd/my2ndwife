<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F7F7FC]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Delivery Partner Login | My 2nd Wife</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset(\App\Models\RestaurantSetting::get('restaurant_favicon', 'images/defaults/favicon.svg')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-[#202033] font-sans antialiased bg-[#F7F7FC] flex flex-col justify-center py-8 sm:py-12 px-4 sm:px-6 lg:px-8">

    @php
        $logoPath = \App\Models\RestaurantSetting::get('restaurant_logo', 'images/defaults/logo.svg');
        $logoUrl = asset($logoPath);
        $restaurantName = \App\Models\RestaurantSetting::get('restaurant_name', 'My 2nd Wife');
    @endphp

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        
        <!-- Official Brand Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center p-3.5 sm:p-4 bg-white rounded-3xl border border-[#E8E5EF] shadow-[0_4px_20px_rgba(32,32,51,0.06)] mb-3">
                <img src="{{ $logoUrl }}" 
                     alt="{{ $restaurantName }}" 
                     class="h-10 sm:h-12 w-auto max-w-[210px] object-contain">
            </div>
            
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-[#202033]">{{ $restaurantName }}</h1>
            
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FFF0F6] border border-[#FEE2EB] text-[#FF006E] text-xs font-black uppercase tracking-wider mt-2">
                <span class="w-1.5 h-1.5 rounded-full bg-[#FF006E]"></span>
                <span>Delivery Partner Portal</span>
            </div>
            
            <p class="text-xs text-[#55597D] font-medium mt-2">
                Sign in with your rider account to start receiving delivery assignments
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white py-8 px-6 sm:px-8 rounded-3xl border border-[#E8E5EF] shadow-[0_6px_25px_rgba(32,32,51,0.06)]"
             x-data="{ 
                 loginType: '{{ old('login_type', (old('email') && !old('phone')) ? 'email' : 'phone') }}', 
                 countryCode: '{{ old('country_code', '+91') }}',
                 phoneVal: '{{ old('phone', old('login', '')) }}',
                 emailVal: '{{ old('email', '') }}',
                 passVal: ''
             }">

            <!-- Flash Errors -->
            @if($errors->any())
                <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-bold space-y-1">
                    @foreach($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-[#E5484D] shrink-0"></i>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if(session('info'))
                <div class="mb-5 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 text-xs font-bold flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-blue-600 shrink-0"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <!-- Auth Mode Switcher -->
            <div class="flex items-center p-1 bg-[#F4F3F8] rounded-2xl mb-5 text-xs font-bold">
                <button type="button" 
                        @click="loginType = 'phone'"
                        :class="loginType === 'phone' ? 'bg-white text-[#FF006E] shadow-sm' : 'text-[#667085] hover:text-[#202033]'"
                        class="flex-1 py-2.5 text-center rounded-xl transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                    <span>Mobile Number</span>
                </button>
                <button type="button" 
                        @click="loginType = 'email'"
                        :class="loginType === 'email' ? 'bg-white text-[#FF006E] shadow-sm' : 'text-[#667085] hover:text-[#202033]'"
                        class="flex-1 py-2.5 text-center rounded-xl transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                    <span>Email Address</span>
                </button>
            </div>

            <form action="{{ route('delivery.login.post') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="login_type" :value="loginType">
                <input type="hidden" name="login" :value="loginType === 'phone' ? phoneVal : emailVal">

                <!-- Mobile Number Input with Country Code -->
                <div x-show="loginType === 'phone'">
                    <label for="phone" class="block text-xs font-black text-[#202033] mb-1.5">
                        Rider Mobile Number <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-stretch gap-2">
                        <!-- Country Code Selector (Default +91 India) -->
                        <div class="relative w-28 shrink-0">
                            <select id="country_code" 
                                    name="country_code" 
                                    x-model="countryCode"
                                    class="w-full h-full py-3 pl-3 pr-7 bg-white rounded-2xl border border-[#E8E5EF] text-xs font-bold text-[#202033] focus:border-[#FF006E] focus:ring-1 focus:ring-[#FF006E] focus:outline-none appearance-none cursor-pointer">
                                <option value="+91" selected>🇮🇳 +91</option>
                                <option value="+1">🇺🇸 +1</option>
                                <option value="+44">🇬🇧 +44</option>
                                <option value="+971">🇦🇪 +971</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-[#667085]">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#667085]">
                                <i data-lucide="smartphone" class="w-4 h-4"></i>
                            </div>
                            <input id="phone" 
                                   name="phone" 
                                   type="tel" 
                                   inputmode="numeric"
                                   maxlength="15"
                                   :required="loginType === 'phone'"
                                   x-model="phoneVal"
                                   placeholder="Enter 10-digit mobile number"
                                   class="w-full pl-10 pr-4 py-3 rounded-2xl border border-[#E8E5EF] text-xs font-bold text-[#202033] placeholder:text-[#98A2B3] focus:border-[#FF006E] focus:ring-1 focus:ring-[#FF006E] focus:outline-none transition-all">
                        </div>
                    </div>
                    <p class="text-[11px] text-[#8C93A8] mt-1.5 font-medium">Standard 10-digit Indian mobile number (e.g. 9830099887)</p>
                </div>

                <!-- Email Address Input -->
                <div x-show="loginType === 'email'" x-cloak>
                    <label for="email" class="block text-xs font-black text-[#202033] mb-1.5">
                        Rider Email Address <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#667085]">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input id="email" 
                               name="email" 
                               type="email" 
                               :required="loginType === 'email'"
                               x-model="emailVal"
                               placeholder="rider@my2ndwife.com"
                               class="w-full pl-10 pr-4 py-3 rounded-2xl border border-[#E8E5EF] text-xs font-bold text-[#202033] placeholder:text-[#98A2B3] focus:border-[#FF006E] focus:ring-1 focus:ring-[#FF006E] focus:outline-none transition-all">
                    </div>
                    <p class="text-[11px] text-[#8C93A8] mt-1.5 font-medium">Your registered delivery partner email account</p>
                </div>

                <!-- Password -->
                <div x-data="{ show: false }">
                    <label for="password" class="block text-xs font-black text-[#202033] mb-1.5">
                        Password <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#667085]">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input id="password" 
                               name="password" 
                               :type="show ? 'text' : 'password'" 
                               required 
                               x-model="passVal"
                               placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-3 rounded-2xl border border-[#E8E5EF] text-xs font-bold text-[#202033] placeholder:text-[#98A2B3] focus:border-[#FF006E] focus:ring-1 focus:ring-[#FF006E] focus:outline-none transition-all">
                        <button type="button" 
                                @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#667085] hover:text-[#202033] transition-colors cursor-pointer">
                            <i data-lucide="eye" x-show="!show" class="w-4 h-4"></i>
                            <i data-lucide="eye-off" x-show="show" x-cloak class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#FF006E] focus:ring-[#FF006E] border-gray-300">
                        <span class="text-[#55597D] font-bold">Keep me logged in</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-[#FF006E] to-[#E60063] hover:from-[#E60063] hover:to-[#C70054] text-white text-xs font-black tracking-wide shadow-md hover:shadow-lg transition-all active:scale-[0.99] cursor-pointer flex items-center justify-center gap-2">
                    <span>Sign In to Rider Portal</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Lucide Icons Initializer -->
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
</body>
</html>
