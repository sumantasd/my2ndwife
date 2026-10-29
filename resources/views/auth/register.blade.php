@extends('layouts.app')

@section('title', 'Create Account | My 2nd Wife')

@section('content')
<div class="max-w-[420px] mx-auto px-4 py-16">
    <div class="bg-white p-8 sm:p-9 rounded-2xl border border-[#EFF0F6] shadow-sm space-y-6">
        
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-[#FFF0F6] text-primary flex items-center justify-center mx-auto text-2xl border border-pink-200 shadow-sm">
                🎉
            </div>
            <h1 class="text-2xl font-bold text-[#1F1F39] font-rubik">Create an Account</h1>
            <p class="text-xs text-[#6E7191]">Join My 2nd Wife food delivery rewards and enjoy fast checkout.</p>
        </div>

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-[#1F1F39] mb-1 font-rubik">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       placeholder="Your Name"
                       class="w-full p-3 rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-[#EFF0F6] @enderror focus:border-primary focus:outline-none text-[#1F1F39]">
                @error('name')
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-bold text-[#1F1F39] mb-1 font-rubik">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       placeholder="name@example.com"
                       class="w-full p-3 rounded-xl border @error('email') border-rose-400 bg-rose-50/30 @else border-[#EFF0F6] @enderror focus:border-primary focus:outline-none text-[#1F1F39]">
                @error('email')
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-bold text-[#1F1F39] mb-1 font-rubik">Mobile Phone Number</label>
                <div class="flex rounded-xl border @error('phone') border-rose-400 bg-rose-50/30 @else border-[#EFF0F6] bg-white @enderror focus-within:border-primary focus-within:ring-2 focus-within:ring-pink-100 transition-all overflow-hidden shadow-2xs">
                    <!-- Default Country Code +91 -->
                    <span class="inline-flex items-center gap-1 px-3.5 bg-[#F7F7FC] border-r border-[#EFF0F6] text-xs font-bold text-[#1F1F39] select-none shrink-0">
                        <span>🇮🇳</span>
                        <span class="font-rubik">+91</span>
                    </span>
                    <!-- Editable Mobile Number -->
                    <input type="tel" name="phone" value="{{ old('phone') }}" required
                           placeholder="Enter 10-digit mobile number"
                           class="w-full p-3 text-[#1F1F39] text-xs font-medium focus:outline-none bg-transparent">
                </div>
                @error('phone')
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                @enderror
                <p class="text-[10px] text-gray-400 mt-1">We'll use this phone number for order updates and delivery coordination.</p>
            </div>

            <div>
                <label class="block font-bold text-[#1F1F39] mb-1 font-rubik" for="register_password">Password</label>
                <div x-data="{ show: false }" class="relative">
                    <input :type="show ? 'text' : 'password'" id="register_password" name="password" required
                           placeholder="At least 8 characters"
                           class="w-full p-3 pr-11 rounded-xl border @error('password') border-rose-400 bg-rose-50/30 @else border-[#EFF0F6] @enderror focus:border-primary focus:outline-none text-[#1F1F39] transition-all">
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
                @error('password')
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-bold text-[#1F1F39] mb-1 font-rubik" for="register_password_confirmation">Confirm Password</label>
                <div x-data="{ show: false }" class="relative">
                    <input :type="show ? 'text' : 'password'" id="register_password_confirmation" name="password_confirmation" required
                           placeholder="Repeat password"
                           class="w-full p-3 pr-11 rounded-xl border @error('password_confirmation') border-rose-400 bg-rose-50/30 @else border-[#EFF0F6] @enderror focus:border-primary focus:outline-none text-[#1F1F39] transition-all">
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
                @error('password_confirmation')
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" 
                    class="w-full py-3.5 rounded-full bg-primary hover:bg-[#E0005E] text-white font-bold text-xs shadow-sm transition-all cursor-pointer">
                Create Account
            </button>
        </form>

        <div class="text-center pt-2 text-xs text-[#6E7191]">
            Already have an account? 
            <a href="{{ route('login') }}" class="text-primary font-bold hover:underline">Sign In</a>
        </div>
    </div>
</div>
@endsection
