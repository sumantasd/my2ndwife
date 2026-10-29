@extends('layouts.app')

@section('title', 'Help Center & FAQ | My 2nd Wife Food Delivery')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-[#FF006B] transition-colors">Home</a>
        <span>/</span>
        <span class="text-gray-900 font-semibold">Help Center & FAQ</span>
    </nav>

    <!-- Page Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-[#EFF0F6] shadow-xs mb-8">
        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-[#FFF0F6] text-[#FF006B] mb-3">
            Customer Support
        </span>
        <h1 class="text-2xl sm:text-4xl font-black font-rubik text-[#1F1F39] tracking-tight">
            Frequently Asked Questions
        </h1>
        <p class="text-xs sm:text-sm text-[#6E7191] mt-2 max-w-2xl leading-relaxed">
            Find quick answers about placing orders, delivery radii, payment methods, cancel policies, and kitchen preparation standards.
        </p>
    </div>

    <!-- FAQ Accordion List -->
    <div class="space-y-4" x-data="{ openFaq: 0 }">
        @foreach($faqs as $index => $faq)
            <div class="bg-white rounded-2xl border border-[#EFF0F6] shadow-xs overflow-hidden transition-all">
                <button type="button" 
                        @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                        class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 font-bold text-xs sm:text-sm text-[#1F1F39] hover:text-[#FF006B] transition-colors">
                    <span class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-[#FFF0F6] text-[#FF006B] font-extrabold text-xs flex items-center justify-center shrink-0">
                            {{ $index + 1 }}
                        </span>
                        <span>{{ $faq['question'] }}</span>
                    </span>
                    <i data-lucide="chevron-down" 
                       class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200"
                       :class="openFaq === {{ $index }} ? 'rotate-180 text-[#FF006B]' : ''"></i>
                </button>
                <div x-show="openFaq === {{ $index }}" x-collapse x-cloak>
                    <div class="px-5 sm:px-6 pb-5 pt-1 text-xs text-gray-600 leading-relaxed border-t border-gray-100/60 bg-[#FAFAFE]">
                        {{ $faq['answer'] }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Still Need Help Box -->
    <div class="mt-10 rounded-3xl bg-gradient-to-r from-pink-500/10 via-rose-500/5 to-purple-500/10 border border-[#FF006B]/20 p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="space-y-1 text-center sm:text-left">
            <h3 class="font-bold text-[#1F1F39] text-sm sm:text-base">Still need assistance with your order?</h3>
            <p class="text-xs text-gray-600">Our customer care team is available during operating hours to resolve any issues immediately.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('page.contact') }}" class="px-5 py-2.5 rounded-full bg-[#FF006B] hover:bg-[#E60060] text-white font-bold text-xs shadow-xs transition-colors">
                Contact Us
            </a>
            <a href="{{ route('menu.index') }}" class="px-5 py-2.5 rounded-full bg-white border border-[#EFF0F6] text-[#1F1F39] hover:bg-gray-50 font-bold text-xs transition-colors">
                Browse Menu
            </a>
        </div>
    </div>
</div>
@endsection
