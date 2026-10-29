@extends('layouts.app')

@section('title', 'Sign In | My 2nd Wife')

@section('content')
<div class="max-w-[420px] mx-auto px-4 py-16">
    <div class="bg-white p-8 sm:p-9 rounded-2xl border border-[#EFF0F6] shadow-sm space-y-6">
        
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-[#FFF0F6] text-primary flex items-center justify-center mx-auto text-2xl border border-pink-200 shadow-sm">
                🍔
            </div>
            <h1 class="text-2xl font-bold text-[#1F1F39] font-rubik">Welcome Back</h1>
            <p class="text-xs text-[#6E7191]">Sign in to your account to track orders and save addresses.</p>
        </div>

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <!-- Email Address or Mobile Number Field with Default +91 -->
            <div x-data="{
                identifier: '{{ old('login', old('email', '')) }}',
                get isEmail() {
                    return /[a-zA-Z@]/.test(this.identifier);
                }
            }">
                <label class="block font-bold text-[#1F1F39] mb-1 font-rubik">Email Address or Mobile Number</label>
                
                <div class="flex rounded-xl border @if($errors->has('login') || $errors->has('email')) border-rose-400 bg-rose-50/30 @else border-[#EFF0F6] bg-white @endif focus-within:border-primary focus-within:ring-2 focus-within:ring-pink-100 transition-all overflow-hidden shadow-2xs">
                    <!-- Visual Country Code +91 / Email Icon Prefix -->
                    <span class="inline-flex items-center gap-1.5 px-3.5 bg-[#F7F7FC] border-r border-[#EFF0F6] text-xs font-bold text-[#1F1F39] select-none shrink-0 transition-colors">
                        <template x-if="!isEmail">
                            <span class="inline-flex items-center gap-1">
                                <span>🇮🇳</span>
                                <span class="font-rubik">+91</span>
                            </span>
                        </template>
                        <template x-if="isEmail">
                            <span class="inline-flex items-center text-primary">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </span>
                        </template>
                    </span>

                    <!-- Editable Input Field -->
                    <input type="text" id="login_input" name="login" x-model="identifier" required autofocus
                           :placeholder="isEmail ? 'name@example.com' : 'Enter mobile number or email'"
                           class="w-full p-3 text-[#1F1F39] text-xs font-medium focus:outline-none bg-transparent">
                </div>

                @if($errors->has('login'))
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $errors->first('login') }}</p>
                @elseif($errors->has('email'))
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $errors->first('email') }}</p>
                @endif
                <p class="text-[10px] text-gray-400 mt-1">Login using your registered email address or 10-digit Indian mobile number.</p>
            </div>

            <div>
                <label class="block font-bold text-[#1F1F39] mb-1 font-rubik" for="login_password">Password</label>
                <div x-data="{ show: false }" class="relative">
                    <input :type="show ? 'text' : 'password'" id="login_password" name="password" required
                           placeholder="••••••••"
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

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded accent-primary">
                    <span class="text-xs text-[#6E7191] font-medium">Remember me</span>
                </label>
            </div>

            <button type="submit" 
                    class="w-full py-3.5 rounded-full bg-primary hover:bg-[#E0005E] text-white font-bold text-xs shadow-sm transition-all cursor-pointer">
                Sign In
            </button>
        </form>

        <div class="text-center pt-3 border-t border-[#EFF0F6] text-xs text-[#6E7191]">
            Don't have an account? 
            <a href="{{ route('register') }}" class="text-primary font-bold hover:underline">Create an Account</a>
        </div>
    </div>
</div>
@endsection
