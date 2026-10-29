@extends('layouts.app')

@section('title', ($page->meta_title ?: $page->title) . ' | My 2nd Wife Food Delivery')

@if($page->meta_description)
    @section('meta_description', $page->meta_description)
@endif

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#FF006B] transition-colors">Home</a>
        <span>/</span>
        <span class="text-gray-900 font-semibold">{{ $page->title }}</span>
    </nav>

    <!-- Page Header Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-[#EFF0F6] shadow-xs mb-8">
        <div class="max-w-2xl">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-[#FFF0F6] text-[#FF006B] mb-3">
                Official Information
            </span>
            <h1 class="text-2xl sm:text-4xl font-black font-rubik text-[#1F1F39] tracking-tight">
                {{ $page->title }}
            </h1>
            @if($page->meta_description)
                <p class="text-xs sm:text-sm text-[#6E7191] mt-2 leading-relaxed">
                    {{ $page->meta_description }}
                </p>
            @endif
            <p class="text-[11px] text-gray-400 mt-2">
                Last updated: {{ $page->updated_at->format('F Y') }}
            </p>
        </div>

        <!-- Quick Switcher Tabs -->
        <div class="flex items-center gap-2 mt-6 pt-6 border-t border-gray-100 overflow-x-auto no-scrollbar">
            <a href="{{ route('page.about') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ in_array($page->slug, ['about', 'about-us']) ? 'bg-[#FF006B] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                About Us
            </a>
            <a href="{{ route('page.terms') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ in_array($page->slug, ['terms', 'terms-and-conditions']) ? 'bg-[#FF006B] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Terms & Conditions
            </a>
            <a href="{{ route('page.privacy') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ in_array($page->slug, ['privacy', 'privacy-policy']) ? 'bg-[#FF006B] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Privacy Policy
            </a>
            <a href="{{ route('page.refund') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ in_array($page->slug, ['refund', 'refund-policy']) ? 'bg-[#FF006B] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Refund Policy
            </a>
            <a href="{{ route('page.faq') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ $page->slug === 'faq' ? 'bg-[#FF006B] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                FAQ & Help
            </a>
            <a href="{{ route('page.contact') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ $page->slug === 'contact' ? 'bg-[#FF006B] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Contact Us
            </a>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-[#EFF0F6] shadow-xs text-[#1F1F39]">
        <div class="prose prose-sm max-w-none text-xs sm:text-sm leading-relaxed text-gray-700 space-y-4">
            {!! $sanitizedContent !!}
        </div>

        <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h4 class="text-xs font-bold text-gray-900">Have questions about this page?</h4>
                <p class="text-[11px] text-gray-500">Our customer support team is available from 11:00 AM to 11:30 PM daily.</p>
            </div>
            <a href="{{ route('page.contact') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#FF006B] hover:bg-[#E60060] text-white font-bold text-xs shadow-xs transition-colors self-start sm:self-auto">
                <i data-lucide="phone-call" class="w-4 h-4"></i>
                <span>Contact Support</span>
            </a>
        </div>
    </div>
</div>
@endsection
