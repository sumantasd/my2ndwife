@extends('layouts.app')

@section('title', 'Contact Us | My 2nd Wife Food Delivery')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-[#FF006B] transition-colors">Home</a>
        <span>/</span>
        <span class="text-gray-900 font-semibold">Contact Us</span>
    </nav>

    <!-- Page Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-[#EFF0F6] shadow-xs mb-8">
        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-[#FFF0F6] text-[#FF006B] mb-3">
            Get in Touch
        </span>
        <h1 class="text-2xl sm:text-4xl font-black font-rubik text-[#1F1F39] tracking-tight">
            Contact My 2nd Wife
        </h1>
        <p class="text-xs sm:text-sm text-[#6E7191] mt-2 max-w-2xl leading-relaxed">
            Have questions about an ongoing order, catering inquiries, or feedback? Reach out through any of our channels below. Our kitchen and support team are here to help.
        </p>
    </div>

    <!-- Contact Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Direct Phone & WhatsApp -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFF0F6] shadow-xs space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-[#FFF0F6] text-[#FF006B] flex items-center justify-center">
                <i data-lucide="phone" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#1F1F39]">Customer Hotline & WhatsApp</h3>
            <p class="text-xs text-gray-600">Give us a call or send a message for instant order inquiries or live tracking help.</p>
            <div class="space-y-2 pt-2">
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="flex items-center gap-2 text-sm font-bold text-[#FF006B] hover:underline">
                    <i data-lucide="phone-call" class="w-4 h-4"></i>
                    <span>{{ $phone }}</span>
                </a>
                @if(!empty($altPhone))
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $altPhone) }}" class="flex items-center gap-2 text-xs font-semibold text-gray-700 hover:underline">
                    <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                    <span>Alt: {{ $altPhone }}</span>
                </a>
                @endif
                @if(!empty($whatsapp))
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold hover:bg-emerald-100 transition-colors">
                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                    <span>Chat on WhatsApp</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Email & Inquiries -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFF0F6] shadow-xs space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-[#FFF0F6] text-[#FF006B] flex items-center justify-center">
                <i data-lucide="mail" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#1F1F39]">Email Inquiries</h3>
            <p class="text-xs text-gray-600">Send us your feedback, corporate event catering requests, or partnership questions.</p>
            <div class="pt-2">
                <a href="mailto:{{ $email }}" class="flex items-center gap-2 text-sm font-bold text-[#FF006B] hover:underline">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>{{ $email }}</span>
                </a>
                <span class="text-[11px] text-gray-500 block mt-1">We typically reply within 24 hours.</span>
            </div>
        </div>

        <!-- Restaurant Location -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFF0F6] shadow-xs space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-[#FFF0F6] text-[#FF006B] flex items-center justify-center">
                <i data-lucide="map-pin" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#1F1F39]">Kitchen & Pickup Location</h3>
            <p class="text-xs text-gray-700 font-medium leading-relaxed">
                {{ $address }}
            </p>
            @if(!empty($mapsUrl) && $mapsUrl !== '#')
            <div class="pt-2">
                <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#FF006B] hover:underline">
                    <span>View on Google Maps</span>
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                </a>
            </div>
            @endif
        </div>

        <!-- Operating Hours -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFF0F6] shadow-xs space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-[#FFF0F6] text-[#FF006B] flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#1F1F39]">Kitchen Operating Hours</h3>
            <p class="text-xs text-gray-600">Delivering piping hot Indian & Bengali delicacies throughout Kolkata.</p>
            <div class="p-3 bg-[#F7F7FC] rounded-2xl border border-[#EFF0F6] space-y-1 text-xs">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#1F1F39]">Monday - Sunday:</span>
                    <span class="font-black text-[#FF006B]">{{ $openTime }} - {{ $closeTime }}</span>
                </div>
                <p class="text-[10px] text-gray-500">Orders placed within 30 minutes of closing are subject to kitchen capacity.</p>
            </div>
        </div>
    </div>
</div>
@endsection
