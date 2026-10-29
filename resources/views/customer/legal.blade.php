@extends('layouts.app')

@section('title', $pageTitle . ' | My 2nd Wife Food Delivery')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-[#FF006B] transition-colors">Home</a>
        <span>/</span>
        <span class="text-gray-900 font-semibold">{{ $pageTitle }}</span>
    </nav>

    <!-- Page Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-[#EFF0F6] shadow-xs mb-8">
        <div class="max-w-2xl">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-[#FFF0F6] text-[#FF006B] mb-3">
                Legal & Customer Protection
            </span>
            <h1 class="text-2xl sm:text-4xl font-black font-rubik text-[#1F1F39] tracking-tight">
                {{ $pageTitle }}
            </h1>
            <p class="text-xs sm:text-sm text-[#6E7191] mt-2 leading-relaxed">
                Last updated: {{ date('F Y') }}. Please read our official guidelines regarding service usage, delivery fulfillment, hygiene standards, and customer rights.
            </p>
        </div>

        <!-- Quick Switcher Tabs -->
        <div class="flex items-center gap-2 mt-6 pt-6 border-t border-gray-100 overflow-x-auto no-scrollbar">
            <a href="{{ route('page.terms') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ $activeSection === 'terms' ? 'bg-[#FF006B] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Terms & Conditions
            </a>
            <a href="{{ route('page.privacy') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ $activeSection === 'privacy' ? 'bg-[#FF006B] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Privacy Policy
            </a>
            <a href="{{ route('page.refund') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ $activeSection === 'refund' ? 'bg-[#FF006B] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Refund & Cancellation
            </a>
            <a href="{{ route('page.faq') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 bg-gray-100 text-gray-700 hover:bg-gray-200">
                FAQ & Help Center
            </a>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-[#EFF0F6] shadow-xs space-y-6 text-[#1F1F39]">
        <div class="prose prose-sm max-w-none text-xs sm:text-sm leading-relaxed text-gray-700 space-y-4">
            <div class="p-4 rounded-2xl bg-[#F7F7FC] border border-[#EFF0F6]">
                <p class="font-medium text-gray-800 leading-relaxed whitespace-pre-line">{{ $content }}</p>
            </div>

            @if(isset($secondaryContent) && !empty($secondaryContent))
                <h3 class="text-base sm:text-lg font-bold text-[#1F1F39] pt-4">Delivery & Service Guidelines</h3>
                <div class="p-4 rounded-2xl bg-[#F7F7FC] border border-[#EFF0F6]">
                    <p class="font-medium text-gray-800 leading-relaxed whitespace-pre-line">{{ $secondaryContent }}</p>
                </div>
            @endif

            <h3 class="text-base sm:text-lg font-bold text-[#1F1F39] pt-4">Questions or Assistance</h3>
            <p>
                If you have any questions regarding these terms, your orders, or our hygiene and packaging standards, please contact our support team directly. We are committed to providing the highest quality dining and delivery experience in Kolkata.
            </p>
            <div class="pt-2">
                <a href="{{ route('page.contact') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#FF006B] hover:bg-[#E60060] text-white font-bold text-xs shadow-xs transition-colors">
                    <i data-lucide="phone-call" class="w-4 h-4"></i>
                    <span>Contact Support Team</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
