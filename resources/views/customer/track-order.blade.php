@extends('layouts.app')

@section('title', 'Track Order #' . $order->order_number . ' | My 2nd Wife')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-24 sm:pb-12 space-y-8"
     x-data="{
         currentStatus: '{{ $order->order_status }}',
         statusLabel: '{{ $order->status_label }}',
         progressStep: {{ $order->progress_step }},
         isPaid: {{ $order->isPaid() ? 'true' : 'false' }},
         initPolling() {
             setInterval(() => {
                 fetch('{{ route('order.status.json', ['token' => $order->tracking_token]) }}')
                     .then(res => res.json())
                     .then(data => {
                         this.currentStatus = data.status;
                         this.statusLabel = data.status_label;
                         this.progressStep = data.progress_step;
                         this.isPaid = data.is_paid;
                     });
             }, 8000);
         }
     }" x-init="initPolling()">

    <!-- Order Header Card -->
    <div class="p-6 sm:p-7 rounded-2xl bg-white border border-[#EFF0F6] shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#6E7191] uppercase tracking-wider font-rubik">Tracking Live Order</span>
                <span class="inline-block w-2 h-2 rounded-full bg-[#1AB759] animate-pulse"></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[#1F1F39] font-rubik mt-1">#{{ $order->order_number }}</h1>
            <p class="text-xs text-[#6E7191] mt-1 flex flex-wrap items-center gap-1.5">
                <span>Placed on {{ $order->created_at->format('d M Y, h:i A') }}</span>
                <span>•</span>
                <span>Fulfillment: <strong class="capitalize text-[#1F1F39]">{{ $order->order_type }}</strong></span>
                <span>•</span>
                @if($order->order_schedule === 'scheduled')
                    <span class="inline-flex items-center gap-1 font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">
                        <i data-lucide="calendar" class="w-3 h-3"></i>
                        Scheduled: {{ $order->scheduled_at ? $order->scheduled_at->format('d M Y, h:i A') : 'Future' }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        <i data-lucide="zap" class="w-3 h-3"></i>
                        Instant Delivery
                    </span>
                @endif
            </p>
        </div>

        <div class="flex flex-col items-start sm:items-end gap-2">
            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide border"
                  :class="currentStatus === 'delivered' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : (currentStatus === 'cancelled' ? 'bg-rose-50 text-rose-800 border-rose-200' : 'bg-[#FFF0F6] text-primary border-pink-200')"
                  x-text="statusLabel"></span>

            <span class="text-xs font-bold"
                  :class="isPaid ? 'text-[#1AB759]' : 'text-amber-600'"
                  x-text="isPaid ? 'Payment: Completed' : 'Payment: Pending (' + '{{ strtoupper(str_replace('_', ' ', $order->payment?->payment_method ?? 'COD')) }}' + ')'"></span>
        </div>
    </div>

    <!-- Live Progress Timeline Tracker (6 Steps) -->
    @php
        $isDelivery = $order->isDelivery();
        $isTakeaway = $order->isTakeaway();
        
        $steps = $isDelivery ? [
            1 => [
                'key' => 'placed',
                'label' => 'Placed',
                'desc' => 'Order received',
                'path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
            ],
            2 => [
                'key' => 'accepted',
                'label' => 'Accepted',
                'desc' => 'Kitchen confirmed',
                'path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            ],
            3 => [
                'key' => 'preparing',
                'label' => 'Preparing',
                'desc' => 'Cooking fresh',
                'path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 13.8V4a2 2 0 0 1 2-2h.5a3.5 3.5 0 0 1 7 0H16a2 2 0 0 1 2 2v9.8M6 13.8a4.5 4.5 0 0 0-3 4.2V20h18v-2a4.5 4.5 0 0 0-3-4.2M6 13.8h12"/>',
            ],
            4 => [
                'key' => 'ready',
                'label' => 'Ready',
                'desc' => 'Packed & sealed',
                'path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
            ],
            5 => [
                'key' => 'out_for_delivery',
                'label' => 'Out for Delivery',
                'desc' => 'Rider dispatched',
                'path' => '<circle cx="18.5" cy="17.5" r="3.5" stroke-width="2"/><circle cx="5.5" cy="17.5" r="3.5" stroke-width="2"/><circle cx="15" cy="5" r="1" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 17.5V14l-3-3 4-3 2 3h3"/>',
            ],
            6 => [
                'key' => 'delivered',
                'label' => 'Delivered',
                'desc' => 'Enjoy your meal!',
                'path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
            ],
        ] : [
            1 => [
                'key' => 'placed',
                'label' => 'Placed',
                'desc' => 'Order received',
                'path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
            ],
            2 => [
                'key' => 'accepted',
                'label' => 'Accepted',
                'desc' => 'Kitchen confirmed',
                'path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            ],
            3 => [
                'key' => 'preparing',
                'label' => 'Preparing',
                'desc' => 'Cooking fresh',
                'path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 13.8V4a2 2 0 0 1 2-2h.5a3.5 3.5 0 0 1 7 0H16a2 2 0 0 1 2 2v9.8M6 13.8a4.5 4.5 0 0 0-3 4.2V20h18v-2a4.5 4.5 0 0 0-3-4.2M6 13.8h12"/>',
            ],
            4 => [
                'key' => 'ready',
                'label' => 'Ready',
                'desc' => 'Food prepared',
                'path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
            ],
            5 => [
                'key' => 'ready_for_pickup',
                'label' => 'Ready for Pickup',
                'desc' => 'Waiting at counter',
                'path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 7v1a3 3 0 006 0V7m0 1a3 3 0 006 0V7m0 1a3 3 0 006 0V7H3l2-4h14l2 4M5 21V10.85M19 21V10.85M9 21v-4a2 2 0 012-2h2a2 2 0 012 2v4"/>',
            ],
            6 => [
                'key' => 'delivered',
                'label' => 'Picked Up',
                'desc' => 'Enjoy your meal!',
                'path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
            ],
        ];
    @endphp

    <div class="p-6 sm:p-7 rounded-3xl bg-white border border-[#EFF0F6] shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#6E7191] font-rubik flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Live Preparation & Fulfillment Progress</span>
            </h2>
            <span class="text-[11px] font-bold text-gray-400 font-mono hidden sm:inline-block">Auto-updates in real time</span>
        </div>

        <template x-if="currentStatus !== 'cancelled'">
            <div>
                <!-- Desktop: All 6 steps horizontally in one single row -->
                <div class="hidden md:block relative py-4 px-2">
                    <!-- Connecting Background Track (top: 24px) -->
                    <div class="absolute top-[24px] left-[8.33%] right-[8.33%] h-1 bg-[#EFF0F6] -translate-y-1/2 z-0 rounded-full" aria-hidden="true">
                        <!-- Dynamic Completed Progress Fill -->
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-700"
                             :style="'width: ' + (progressStep > 1 ? Math.min(100, ((progressStep - 1) / 5) * 100) : 0) + '%'">
                        </div>
                    </div>

                    <!-- 6 Horizontal Columns in One Row -->
                    <div class="relative z-10 grid grid-cols-6 w-full text-center">
                        @foreach($steps as $num => $st)
                            <div class="flex flex-col items-center group px-1">
                                <!-- Status Icon Circle -->
                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 relative border-2"
                                     :class="{
                                         'bg-emerald-500 border-emerald-500 text-white shadow-sm ring-2 ring-white z-10': (progressStep > {{ $num }} || (progressStep === 6 && {{ $num }} === 6)),
                                         'bg-[#F50070] border-white text-white ring-4 ring-[#F50070]/25 shadow-lg shadow-[#F50070]/25 scale-110 z-20': (progressStep === {{ $num }} && progressStep !== 6),
                                         'bg-[#F7F7FC] border-[#EFF0F6] text-gray-400 z-10': (progressStep < {{ $num }})
                                     }">
                                    <!-- Completed Checkmark -->
                                    <template x-if="progressStep > {{ $num }} || (progressStep === 6 && {{ $num }} === 6)">
                                        <svg class="w-5 h-5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <!-- Current Active Step Icon -->
                                    <template x-if="progressStep === {{ $num }} && progressStep !== 6">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! $st['path'] !!}
                                        </svg>
                                    </template>
                                    <!-- Upcoming Inactive Step Icon -->
                                    <template x-if="progressStep < {{ $num }}">
                                        <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! $st['path'] !!}
                                        </svg>
                                    </template>
                                </div>

                                <!-- Step Labels & Description -->
                                <div class="mt-3 space-y-0.5">
                                    <span class="text-xs font-bold block leading-tight tracking-tight"
                                          :class="{
                                              'text-[#F50070]': (progressStep === {{ $num }} && progressStep !== 6),
                                              'text-[#1F1F39]': (progressStep > {{ $num }} || (progressStep === 6 && {{ $num }} === 6)),
                                              'text-gray-400': (progressStep < {{ $num }})
                                          }">
                                        {{ $st['label'] }}
                                    </span>
                                    <span class="text-[11px] text-[#6E7191] block leading-snug">
                                        {{ $st['desc'] }}
                                    </span>
                                    <template x-if="progressStep === {{ $num }} && progressStep !== 6">
                                        <span class="inline-block mt-1 text-[9px] font-black uppercase tracking-wider text-[#F50070] bg-[#FFF0F6] px-2 py-0.5 rounded-full border border-pink-200">
                                            Current
                                        </span>
                                    </template>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Mobile: Compact 2-row, 3-column layout without vertical overflow or horizontal page scroll -->
                <div class="block md:hidden space-y-6 py-2">
                    <!-- Phase 1: Steps 1, 2, 3 -->
                    <div class="relative">
                        <div class="flex items-center justify-between mb-2 px-1">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 font-rubik">Phase 1: Preparation</span>
                            <span class="text-[10px] font-bold text-primary font-mono">Steps 1 - 3</span>
                        </div>
                        <div class="relative py-2">
                            <!-- Connecting line for Row 1 -->
                            <div class="absolute top-[20px] left-[16.66%] right-[16.66%] h-1 bg-[#EFF0F6] -translate-y-1/2 z-0 rounded-full">
                                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                                     :style="'width: ' + (progressStep >= 3 ? 100 : (progressStep == 2 ? 50 : 0)) + '%'"></div>
                            </div>
                            
                            <div class="grid grid-cols-3 text-center relative z-10">
                                @foreach(array_slice($steps, 0, 3, true) as $num => $st)
                                <div class="flex flex-col items-center px-1">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold transition-all relative border-2"
                                         :class="{
                                             'bg-emerald-500 border-emerald-500 text-white shadow-sm ring-2 ring-white z-10': (progressStep > {{ $num }} || (progressStep === 6 && {{ $num }} === 6)),
                                             'bg-[#F50070] border-white text-white ring-4 ring-[#F50070]/25 shadow-md scale-110 z-20': (progressStep === {{ $num }} && progressStep !== 6),
                                             'bg-[#F7F7FC] border-[#EFF0F6] text-gray-400 z-10': (progressStep < {{ $num }})
                                         }">
                                        <template x-if="progressStep > {{ $num }} || (progressStep === 6 && {{ $num }} === 6)">
                                            <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </template>
                                        <template x-if="progressStep === {{ $num }} && progressStep !== 6">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                {!! $st['path'] !!}
                                            </svg>
                                        </template>
                                        <template x-if="progressStep < {{ $num }}">
                                            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                {!! $st['path'] !!}
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="mt-2 space-y-0.5">
                                        <span class="text-[11px] font-bold block leading-tight tracking-tight"
                                              :class="{
                                                  'text-[#F50070]': (progressStep === {{ $num }} && progressStep !== 6),
                                                  'text-[#1F1F39]': (progressStep > {{ $num }} || (progressStep === 6 && {{ $num }} === 6)),
                                                  'text-gray-400': (progressStep < {{ $num }})
                                              }">
                                            {{ $st['label'] }}
                                        </span>
                                        <span class="text-[9px] text-[#6E7191] block leading-tight">
                                            {{ $st['desc'] }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Flow transition indicator -->
                    <div class="flex items-center justify-center gap-2 text-[10px] font-bold text-gray-400">
                        <span class="h-px bg-gray-200 flex-1"></span>
                        <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 flex items-center gap-1 font-mono">
                            <span>Next: {{ $isTakeaway ? 'Counter Pickup' : 'Handover & Delivery' }}</span>
                            <svg class="w-3 h-3 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </span>
                        <span class="h-px bg-gray-200 flex-1"></span>
                    </div>

                    <!-- Phase 2: Steps 4, 5, 6 -->
                    <div class="relative">
                        <div class="flex items-center justify-between mb-2 px-1">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 font-rubik">Phase 2: Handover & {{ $isTakeaway ? 'Pickup' : 'Delivery' }}</span>
                            <span class="text-[10px] font-bold text-primary font-mono">Steps 4 - 6</span>
                        </div>
                        <div class="relative py-2">
                            <!-- Connecting line for Row 2 -->
                            <div class="absolute top-[20px] left-[16.66%] right-[16.66%] h-1 bg-[#EFF0F6] -translate-y-1/2 z-0 rounded-full">
                                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                                     :style="'width: ' + (progressStep >= 6 ? 100 : (progressStep == 5 ? 50 : 0)) + '%'"></div>
                            </div>
                            
                            <div class="grid grid-cols-3 text-center relative z-10">
                                @foreach(array_slice($steps, 3, 3, true) as $num => $st)
                                <div class="flex flex-col items-center px-1">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold transition-all relative border-2"
                                         :class="{
                                             'bg-emerald-500 border-emerald-500 text-white shadow-sm ring-2 ring-white z-10': (progressStep > {{ $num }} || (progressStep === 6 && {{ $num }} === 6)),
                                             'bg-[#F50070] border-white text-white ring-4 ring-[#F50070]/25 shadow-md scale-110 z-20': (progressStep === {{ $num }} && progressStep !== 6),
                                             'bg-[#F7F7FC] border-[#EFF0F6] text-gray-400 z-10': (progressStep < {{ $num }})
                                         }">
                                        <template x-if="progressStep > {{ $num }} || (progressStep === 6 && {{ $num }} === 6)">
                                            <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </template>
                                        <template x-if="progressStep === {{ $num }} && progressStep !== 6">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                {!! $st['path'] !!}
                                            </svg>
                                        </template>
                                        <template x-if="progressStep < {{ $num }}">
                                            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                {!! $st['path'] !!}
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="mt-2 space-y-0.5">
                                        <span class="text-[11px] font-bold block leading-tight tracking-tight"
                                              :class="{
                                                  'text-[#F50070]': (progressStep === {{ $num }} && progressStep !== 6),
                                                  'text-[#1F1F39]': (progressStep > {{ $num }} || (progressStep === 6 && {{ $num }} === 6)),
                                                  'text-gray-400': (progressStep < {{ $num }})
                                              }">
                                            {{ $st['label'] }}
                                        </span>
                                        <span class="text-[9px] text-[#6E7191] block leading-tight">
                                            {{ $st['desc'] }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="currentStatus === 'cancelled'">
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                <p class="font-bold flex items-center gap-1.5">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-[#FB4E4E]"></i>
                    <span>This order has been cancelled</span>
                </p>
                @if($order->cancellation_reason)
                    <p class="text-rose-700">Reason: {{ $order->cancellation_reason }}</p>
                @endif
            </div>
        </template>
    </div>

    <!-- Details Grid: Delivery Partner / Counter & Location -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        @if($order->isDelivery())
        <!-- Delivery Partner Information -->
        <div class="p-6 rounded-3xl bg-white border border-[#EFF0F6] shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#6E7191] flex items-center gap-1.5 font-rubik">
                <i data-lucide="bike" class="w-4 h-4 text-primary"></i>
                <span>Assigned Delivery Partner</span>
            </h3>

            @if($order->deliveryPerson)
                <div class="flex items-center gap-4 pt-2">
                    <div class="w-12 h-12 rounded-full bg-[#FFF0F6] text-primary flex items-center justify-center font-bold text-lg border border-pink-200">
                        {{ substr($order->deliveryPerson->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-[#1F1F39] font-rubik">{{ $order->deliveryPerson->name }}</h4>
                        <p class="text-xs text-[#6E7191]">{{ $order->deliveryPerson->phone }}</p>
                        <span class="inline-block mt-1 text-[10px] font-bold text-[#1AB759] bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                            Verified Delivery Executive
                        </span>
                    </div>
                </div>
            @else
                <div class="py-4 text-center text-xs text-[#6E7191]">
                    <i data-lucide="clock" class="w-6 h-6 mx-auto mb-1 text-gray-300"></i>
                    <p>A delivery executive will be assigned once your food is almost ready.</p>
                </div>
            @endif
        </div>

        <!-- Delivery Address Snapshot -->
        <div class="p-6 rounded-3xl bg-white border border-[#EFF0F6] shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#6E7191] flex items-center gap-1.5 font-rubik">
                    <i data-lucide="map-pin" class="w-4 h-4 text-primary"></i>
                    <span>Delivery Location</span>
                </h3>
                @if($order->delivery_latitude && $order->delivery_longitude)
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                        <i data-lucide="crosshair" class="w-3 h-3 text-emerald-600"></i>
                        GPS Verified
                    </span>
                @endif
            </div>

            <div class="space-y-1 text-xs pt-2">
                <p class="font-bold text-[#1F1F39]">{{ $order->customer_name }} • {{ $order->customer_phone }}</p>
                <p class="text-[#6E7191] leading-relaxed">{{ $order->delivery_address_text ?? 'Doorstep delivery' }}</p>
                @if($order->delivery_instructions)
                    <p class="text-amber-700 italic bg-amber-50 p-2 rounded-xl mt-2">
                        Instructions: {{ $order->delivery_instructions }}
                    </p>
                @endif
            </div>
        </div>
        @else
        <!-- Takeaway Counter Collection -->
        <div class="p-6 rounded-3xl bg-white border border-[#EFF0F6] shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#6E7191] flex items-center gap-1.5 font-rubik">
                <i data-lucide="store" class="w-4 h-4 text-primary"></i>
                <span>Takeaway Counter Collection</span>
            </h3>
            <div class="space-y-1.5 text-xs pt-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-50 text-amber-800 border border-amber-200">
                        Self Pickup
                    </span>
                    <span class="text-[11px] text-[#1AB759] font-bold">No delivery fee charged</span>
                </div>
                <p class="text-[#1F1F39] font-semibold">Please present your Order #{{ $order->order_number }} at the counter when ready.</p>
                <p class="text-[#6E7191] text-[11px]">Hot & fresh takeaway will be packed securely for travel.</p>
            </div>
        </div>

        <!-- Restaurant Pickup Location -->
        <div class="p-6 rounded-3xl bg-white border border-[#EFF0F6] shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#6E7191] flex items-center gap-1.5 font-rubik">
                <i data-lucide="map-pin" class="w-4 h-4 text-primary"></i>
                <span>Pickup Restaurant Location</span>
            </h3>
            <div class="space-y-1 text-xs pt-1">
                <p class="font-bold text-[#1F1F39]">{{ \App\Models\RestaurantSetting::get('restaurant_name', 'My 2nd Wife') }}</p>
                <p class="text-[#6E7191] leading-relaxed">{{ \App\Models\RestaurantSetting::get('restaurant_address', 'Restaurant Address') }}</p>
                <p class="text-gray-500 font-mono text-[11px] mt-1">Contact: {{ \App\Models\RestaurantSetting::get('restaurant_phone', '+91 98300 12345') }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Ordered Items Breakdown -->
    <div class="p-6 sm:p-7 rounded-2xl bg-white border border-[#EFF0F6] shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-[#1F1F39] font-rubik">Items in this Order</h3>

        <div class="divide-y divide-[#EFF0F6]">
            @foreach($order->items as $item)
            <div class="py-3 flex items-start justify-between gap-4 text-xs">
                <div>
                    <p class="font-bold text-[#1F1F39] text-sm">{{ $item->quantity }}x {{ $item->product_name }}</p>
                    @if($item->variation_name)
                        <p class="text-[#6E7191]">Size: {{ $item->variation_name }}</p>
                    @endif
                    @if($item->addons->count() > 0)
                        <p class="text-[11px] text-[#6E7191]">
                            Add-ons: {{ $item->addons->pluck('addon_name')->implode(', ') }}
                        </p>
                    @endif
                    @if($item->special_instructions)
                        <p class="text-[11px] text-amber-700 italic">"{{ $item->special_instructions }}"</p>
                    @endif
                </div>
                <span class="font-bold text-[#1F1F39] text-sm font-rubik">₹{{ number_format($item->line_total, 2) }}</span>
            </div>
            @endforeach
        </div>

        <!-- Financial Totals -->
        <div class="pt-4 border-t border-[#EFF0F6] space-y-2 text-xs text-[#6E7191]">
            <div class="flex justify-between">
                <span>Subtotal</span>
                <span class="font-bold text-[#1F1F39]">₹{{ number_format($order->subtotal, 2) }}</span>
            </div>

            @if($order->discount_amount > 0)
            <div class="flex justify-between text-[#1AB759] font-bold">
                <span>Discount Applied</span>
                <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
            </div>
            @endif

            <div class="flex justify-between">
                <span>Delivery Charge</span>
                <span>₹{{ number_format($order->delivery_charge, 2) }}</span>
            </div>

            <div class="flex justify-between">
                <span>Packaging Charge</span>
                <span>₹{{ number_format($order->packaging_charge, 2) }}</span>
            </div>

            <div class="flex justify-between">
                <span>Tax Amount</span>
                <span>₹{{ number_format($order->tax_amount, 2) }}</span>
            </div>

            <div class="pt-3 border-t border-[#EFF0F6] flex justify-between items-baseline">
                <span class="text-sm font-bold text-[#1F1F39] font-rubik">Total Paid / Payable</span>
                <span class="text-2xl font-bold text-[#FF006B] font-rubik">₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Status History Audit Trail -->
    @if($order->statusHistories->count() > 0)
    <div class="p-6 rounded-2xl bg-white border border-[#EFF0F6] shadow-sm space-y-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-[#6E7191] font-rubik">Order Milestones History</h3>
        <div class="divide-y divide-[#EFF0F6] text-xs">
            @foreach($order->statusHistories as $history)
            <div class="py-2.5 flex items-center justify-between">
                <div>
                    <span class="font-bold text-[#1F1F39] capitalize">{{ str_replace('_', ' ', $history->to_status) }}</span>
                    <p class="text-[#6E7191] text-[11px]">{{ $history->comment }}</p>
                </div>
                <span class="text-[10px] text-[#6E7191] font-medium">{{ $history->created_at->format('h:i A') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
