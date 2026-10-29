@extends('layouts.admin')

@section('title', 'Order #' . $order->order_number)
@section('header_title', 'Order Management: #' . $order->order_number)

@section('content')
<div class="space-y-6" x-data="{ 
    cancelModal: false,
    submitting: false,
    actionError: null,
    actionSuccess: null,
    currentStatus: '{{ $order->order_status }}',
    statusLabel: '{{ $order->status_label }}',
    hasRider: {{ $order->delivery_person_id ? 'true' : 'false' }},

    async submitStatus(newStatus, comment = null) {
        if (this.submitting) return;

        // Check delivery rider requirement before dispatching
        if ('{{ $order->order_type }}' === 'delivery' && newStatus === 'out_for_delivery' && !this.hasRider) {
            alert('Cannot dispatch order: Please assign a delivery rider in the Delivery Partner section first.');
            return;
        }

        this.submitting = true;
        this.actionError = null;

        try {
            const res = await fetch('{{ route('admin.orders.status', $order) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({
                    status: newStatus,
                    comment: comment
                })
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                this.actionError = data.message || 'Failed to update order status.';
                this.submitting = false;
                return;
            }

            // Status updated successfully - reload page to refresh audit log & UI cleanly
            window.location.reload();
        } catch (e) {
            // Fallback: submit standard form if fetch fails
            document.getElementById('fallback-status-' + newStatus)?.submit();
        }
    }
}">

    <!-- Top Navigation, Order Type Badge & Print Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-gray-500 hover:text-[#F50070] transition-colors">
                ← Back to Orders Board
            </a>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase border {{ $order->isTakeaway() ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-blue-50 text-blue-800 border-blue-200' }}">
                {{ $order->isTakeaway() ? '🛍️ Takeaway / Pickup' : '🛵 Home Delivery' }}
            </span>
            @if($order->order_schedule === 'scheduled')
                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase border bg-purple-50 text-purple-800 border-purple-200 flex items-center gap-1">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>Scheduled: {{ $order->scheduled_at ? $order->scheduled_at->format('d M, h:i A') : 'Future' }}</span>
                </span>
            @else
                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase border bg-emerald-50 text-emerald-800 border-emerald-200 flex items-center gap-1">
                    <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                    <span>Instant Order</span>
                </span>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.orders.receipt', $order) }}" target="_blank" 
               class="px-4 py-2 rounded-full bg-[#FFF0F6] text-[#F50070] border border-[#F50070]/30 hover:bg-[#F50070] hover:text-white text-xs font-bold flex items-center gap-1.5 transition-all shadow-2xs">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Print Thermal Receipt (58/80mm)</span>
            </a>

            <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" 
               class="px-4 py-2 rounded-full bg-gray-100 hover:bg-gray-200 text-[#25232A] text-xs font-bold flex items-center gap-1.5 transition-colors">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>A4 Invoice</span>
            </a>

            @if($order->order_status !== \App\Models\Order::STATUS_DELIVERED && $order->order_status !== \App\Models\Order::STATUS_CANCELLED)
                <button type="button" @click="cancelModal = true" 
                        class="px-4 py-2 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition-colors border border-rose-200">
                    Cancel Order
                </button>
            @endif
        </div>
    </div>

    <!-- Error message alert if action blocked -->
    <template x-if="actionError">
        <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between">
            <span x-text="actionError"></span>
            <button @click="actionError = null" class="text-rose-500 hover:text-rose-800 text-sm">✕</button>
        </div>
    </template>

    <!-- Visual Order Stepper & Compact Status Action Bar -->
    <div class="bg-white p-6 sm:p-7 rounded-3xl border border-[#F0E6EC] shadow-sm space-y-6">
        
        <!-- Header Info & Current Status Pill -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Current Status</span>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-block px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wide border {{ $order->status_badge_color }}">
                        {{ $order->status_label }}
                    </span>
                    <span class="text-xs text-gray-400 font-medium">
                        (Order placed {{ $order->created_at->format('d M, h:i A') }})
                    </span>
                </div>
            </div>

            <!-- Grouped Status Action Bar -->
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                @if($order->order_status === \App\Models\Order::STATUS_PLACED)
                    <button type="button" 
                            @click="submitStatus('{{ \App\Models\Order::STATUS_ACCEPTED }}', 'Order accepted by kitchen')"
                            :disabled="submitting"
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-colors shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                        <span x-show="!submitting">✓</span>
                        <span x-show="submitting" class="animate-spin inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>Accept Order</span>
                    </button>
                @endif

                @if($order->order_status === \App\Models\Order::STATUS_ACCEPTED)
                    <button type="button" 
                            @click="submitStatus('{{ \App\Models\Order::STATUS_PREPARING }}', 'Kitchen started preparing food')"
                            :disabled="submitting"
                            class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition-colors shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                        <span x-show="!submitting">👨‍🍳</span>
                        <span x-show="submitting" class="animate-spin inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>Start Preparing</span>
                    </button>
                @endif

                @if($order->order_status === \App\Models\Order::STATUS_PREPARING)
                    <button type="button" 
                            @click="submitStatus('{{ \App\Models\Order::STATUS_READY }}', 'Food prepared and packed')"
                            :disabled="submitting"
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-colors shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                        <span x-show="!submitting">📦</span>
                        <span x-show="submitting" class="animate-spin inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>{{ $order->isTakeaway() ? 'Mark Ready for Pickup' : 'Mark Ready for Dispatch' }}</span>
                    </button>
                @endif

                @if($order->order_status === \App\Models\Order::STATUS_READY)
                    @if($order->isDelivery())
                        <button type="button" 
                                @click="submitStatus('{{ \App\Models\Order::STATUS_OUT_FOR_DELIVERY }}', 'Order dispatched with delivery rider')"
                                :disabled="submitting"
                                class="px-4 py-2 rounded-xl bg-[#F50070] hover:bg-[#D80063] text-white text-xs font-bold transition-colors shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                            <span x-show="!submitting">🛵</span>
                            <span x-show="submitting" class="animate-spin inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span>
                            <span>Dispatch Out for Delivery</span>
                        </button>
                    @else
                        <button type="button" 
                                @click="submitStatus('{{ \App\Models\Order::STATUS_DELIVERED }}', 'Customer picked up order at counter')"
                                :disabled="submitting"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                            <span x-show="!submitting">✓</span>
                            <span x-show="submitting" class="animate-spin inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span>
                            <span>Mark as Picked Up / Completed</span>
                        </button>
                    @endif
                @endif

                @if($order->order_status === \App\Models\Order::STATUS_OUT_FOR_DELIVERY)
                    <button type="button" 
                            @click="submitStatus('{{ \App\Models\Order::STATUS_DELIVERED }}', 'Order delivered to customer successfully')"
                            :disabled="submitting"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                        <span x-show="!submitting">🎉</span>
                        <span x-show="submitting" class="animate-spin inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>Confirm Delivered</span>
                    </button>
                @endif

                @if($order->order_status === \App\Models\Order::STATUS_DELIVERED)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 font-bold text-xs border border-emerald-200">
                        <span>✓</span> Order Completed
                    </span>
                @endif

                @if($order->order_status === \App\Models\Order::STATUS_CANCELLED)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 font-bold text-xs border border-rose-200">
                        <span>✕</span> Cancelled: {{ $order->cancellation_reason }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Horizontal Status Timeline (All 6 Statuses in a Single Row) -->
        @php
            $isDelivery = $order->isDelivery();
            $isTakeaway = $order->isTakeaway();
            $currentStatus = $order->order_status;
            $isCancelled = $currentStatus === \App\Models\Order::STATUS_CANCELLED;

            // Compute active milestone step (1 to 6)
            if ($isCancelled) {
                $activeStep = 0;
            } elseif ($isTakeaway) {
                $activeStep = match ($currentStatus) {
                    \App\Models\Order::STATUS_PLACED => 1,
                    \App\Models\Order::STATUS_ACCEPTED => 2,
                    \App\Models\Order::STATUS_PREPARING => 3,
                    \App\Models\Order::STATUS_READY => 5, // Food ready, waiting for counter pickup
                    \App\Models\Order::STATUS_DELIVERED => 6, // Picked up / Completed
                    default => 1,
                };
            } else {
                $activeStep = match ($currentStatus) {
                    \App\Models\Order::STATUS_PLACED => 1,
                    \App\Models\Order::STATUS_ACCEPTED => 2,
                    \App\Models\Order::STATUS_PREPARING => 3,
                    \App\Models\Order::STATUS_READY => 4,
                    \App\Models\Order::STATUS_OUT_FOR_DELIVERY => 5,
                    \App\Models\Order::STATUS_DELIVERED => 6,
                    default => 1,
                };
            }

            // 6-step milestone definition
            $timelineSteps = [
                1 => [
                    'key' => 'placed',
                    'label' => 'Placed',
                    'time' => $order->created_at ? $order->created_at->format('h:i A') : null,
                ],
                2 => [
                    'key' => 'accepted',
                    'label' => 'Accepted',
                    'time' => $order->statusHistories->firstWhere('to_status', 'accepted')?->created_at?->format('h:i A'),
                ],
                3 => [
                    'key' => 'preparing',
                    'label' => 'Preparing',
                    'time' => $order->statusHistories->firstWhere('to_status', 'preparing')?->created_at?->format('h:i A'),
                ],
                4 => [
                    'key' => 'ready',
                    'label' => 'Ready',
                    'time' => $order->statusHistories->firstWhere('to_status', 'ready')?->created_at?->format('h:i A'),
                ],
                5 => [
                    'key' => 'dispatched',
                    'label' => $isTakeaway ? 'Ready for Pickup' : 'Dispatched',
                    'time' => $isTakeaway 
                        ? ($activeStep >= 5 ? ($order->statusHistories->firstWhere('to_status', 'ready')?->created_at?->format('h:i A')) : null)
                        : $order->statusHistories->firstWhere('to_status', 'out_for_delivery')?->created_at?->format('h:i A'),
                ],
                6 => [
                    'key' => 'delivered',
                    'label' => $isTakeaway ? 'Picked Up' : 'Delivered',
                    'time' => $order->statusHistories->firstWhere('to_status', 'delivered')?->created_at?->format('h:i A'),
                ],
            ];
        @endphp

        <div class="pt-2">
            <!-- Timeline Container with Horizontal Scroll on Narrow Viewports -->
            <div class="w-full overflow-x-auto pb-2 scrollbar-none">
                <div class="min-w-[580px] sm:min-w-0 relative py-3 px-3">
                    
                    <!-- Background Connecting Progress Line -->
                    <div class="absolute top-[28px] left-[8.33%] right-[8.33%] h-1 bg-gray-200 -translate-y-1/2 z-0 rounded-full" aria-hidden="true">
                        <!-- Completed Progress Fill -->
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                             style="width: {{ $activeStep > 1 ? min(100, (($activeStep - 1) / 5) * 100) : 0 }}%;">
                        </div>
                    </div>

                    <!-- 6 Horizontal Milestones in a Single Row -->
                    <div class="relative z-10 grid grid-cols-6 w-full text-center">
                        @foreach($timelineSteps as $stepNumber => $stepInfo)
                            @php
                                $isCompleted = !$isCancelled && ($activeStep > $stepNumber || ($activeStep === 6 && $stepNumber === 6));
                                $isCurrent = !$isCancelled && $activeStep === $stepNumber && $activeStep !== 6;
                                $isPending = !$isCompleted && !$isCurrent;
                            @endphp
                            <div class="flex flex-col items-center group px-1">
                                <!-- Status Icon / Number Node -->
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-200 relative
                                    {{ $isCurrent 
                                        ? 'bg-[#F50070] text-white ring-4 ring-[#F50070]/30 shadow-md shadow-[#F50070]/25 scale-110 z-20' 
                                        : ($isCompleted 
                                            ? 'bg-emerald-500 text-white shadow-sm ring-2 ring-white z-10' 
                                            : 'bg-white text-gray-400 border-2 border-gray-200 z-10') }}">
                                    @if($isCompleted)
                                        <svg class="w-5 h-5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif($isCurrent)
                                        <span class="text-xs font-black">{{ $stepNumber }}</span>
                                    @else
                                        <span class="text-xs font-semibold text-gray-400">{{ $stepNumber }}</span>
                                    @endif
                                </div>

                                <!-- Label & Timestamp -->
                                <div class="mt-2 space-y-0.5">
                                    <span class="text-xs font-bold block leading-tight
                                        {{ $isCurrent ? 'text-[#F50070]' : ($isCompleted ? 'text-[#25232A]' : 'text-gray-400') }}">
                                        {{ $stepInfo['label'] }}
                                    </span>
                                    
                                    @if($stepInfo['time'])
                                        <span class="text-[10px] text-gray-400 font-mono block">
                                            {{ $stepInfo['time'] }}
                                        </span>
                                    @elseif($isCurrent)
                                        <span class="inline-block text-[9px] font-bold uppercase tracking-wider text-[#F50070] bg-[#FFF0F6] px-1.5 py-0.5 rounded-full">
                                            Active
                                        </span>
                                    @else
                                        <span class="text-[10px] text-gray-300 font-medium block">
                                            Pending
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- Main Grid: Items (8 cols) & Sidebar Info (4 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Column: Ordered Items Table (8 cols) -->
        <div class="lg:col-span-8 bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-[#25232A]">Ordered Items</h3>
                <span class="text-xs text-gray-400">{{ $order->items->count() }} items total</span>
            </div>

            <div class="divide-y divide-gray-100 text-xs">
                @foreach($order->items as $item)
                <div class="py-4 flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <h4 class="font-bold text-sm text-[#25232A]">{{ $item->quantity }}x {{ $item->product_name }}</h4>
                        @if($item->variation_name)
                            <p class="text-gray-500 font-semibold">Portion: {{ $item->variation_name }}</p>
                        @endif
                        @if($item->addons && $item->addons->count() > 0)
                            <p class="text-[11px] text-[#66636D]">
                                Extras: {{ $item->addons->pluck('addon_name')->implode(', ') }}
                            </p>
                        @endif
                        @if($item->special_instructions)
                            <p class="text-[11px] text-amber-700 italic bg-amber-50 p-1.5 rounded-lg inline-block">
                                Note: "{{ $item->special_instructions }}"
                            </p>
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-black text-[#25232A]">₹{{ number_format($item->line_total, 2) }}</span>
                        <span class="text-[10px] text-gray-400 block">₹{{ number_format($item->unit_price, 2) }} unit</span>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Totals Breakdown -->
            <div class="pt-4 border-t border-[#F0E6EC] space-y-2 text-xs text-[#66636D]">
                <div class="flex justify-between">
                    <span>Items Subtotal</span>
                    <span class="font-bold text-[#25232A]">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 font-bold">
                    <span>Discount ({{ $order->coupon_code }})</span>
                    <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($order->isDelivery())
                <div class="flex justify-between">
                    <span>Delivery Charge</span>
                    <span class="font-bold text-[#25232A]">₹{{ number_format($order->delivery_charge, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span>Packaging Charge</span>
                    <span class="font-bold text-[#25232A]">₹{{ number_format($order->packaging_charge, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Taxes</span>
                    <span class="font-bold text-[#25232A]">₹{{ number_format($order->tax_amount, 2) }}</span>
                </div>
                <div class="pt-3 border-t border-[#F0E6EC] flex justify-between items-baseline">
                    <span class="text-sm font-black text-[#25232A]">Total Amount</span>
                    <span class="text-2xl font-black text-[#F50070]">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>

            <!-- Internal Kitchen Notes -->
            <form action="{{ route('admin.orders.notes', $order) }}" method="POST" class="pt-4 border-t border-[#F0E6EC] space-y-2">
                @csrf
                <label class="block text-xs font-bold text-[#25232A]">Internal Administrative & Kitchen Notes</label>
                <div class="flex gap-2">
                    <input type="text" name="internal_notes" value="{{ $order->internal_notes }}" placeholder="Add note for chef or dispatcher..."
                           class="flex-1 text-xs p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-[#25232A] text-xs font-bold transition-colors">Save</button>
                </div>
            </form>
        </div>

        <!-- Right Column: Customer, Delivery Rider & History (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Customer Card -->
            <div class="bg-white p-6 rounded-3xl border border-[#F0E6EC] shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-[#25232A] uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="user" class="w-4 h-4 text-[#F50070]"></i>
                    <span>Customer Information</span>
                </h3>
                <div class="space-y-1">
                    <p class="font-bold text-sm text-[#25232A]">{{ $order->customer_name }}</p>
                    <p class="text-gray-500 font-medium">{{ $order->customer_phone }}</p>
                    <p class="text-gray-500">{{ $order->customer_email ?? 'No email provided' }}</p>
                </div>
                <div class="pt-2 border-t border-gray-100">
                    @if($order->isTakeaway())
                        <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider block mb-1">Takeaway Counter Pickup</span>
                        <p class="text-[#25232A] font-semibold">Customer will collect the food directly from the restaurant counter.</p>
                    @else
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Delivery Address</span>
                        <p class="text-[#25232A] leading-relaxed">{{ $order->delivery_address_text ?? 'Address not specified' }}</p>
                        @if($order->delivery_instructions)
                            <p class="text-amber-700 italic bg-amber-50 p-2 rounded-xl mt-1.5">"{{ $order->delivery_instructions }}"</p>
                        @endif

                        <!-- Customer GPS Delivery Location (Task 4) -->
                        <div class="mt-3 pt-3 border-t border-dashed border-gray-200 space-y-2">
                            <span class="text-[10px] font-bold text-[#F50070] uppercase tracking-wider flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                <span>GPS Delivery Coordinates</span>
                            </span>

                            @if($order->delivery_latitude && $order->delivery_longitude)
                                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-3 space-y-2.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-gray-500 font-medium">Latitude:</span>
                                        <span class="font-mono font-bold text-[#25232A]">{{ number_format($order->delivery_latitude, 6) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-gray-500 font-medium">Longitude:</span>
                                        <span class="font-mono font-bold text-[#25232A]">{{ number_format($order->delivery_longitude, 6) }}</span>
                                    </div>
                                    @if($order->delivery_distance_km)
                                        <div class="flex items-center justify-between text-xs pt-1 border-t border-gray-200/60">
                                            <span class="text-gray-500 font-medium">Delivery Distance:</span>
                                            <span class="font-bold text-[#25232A]">{{ number_format($order->delivery_distance_km, 2) }} km</span>
                                        </div>
                                    @endif
                                    <div class="pt-1">
                                        <a href="https://www.google.com/maps?q={{ (float) $order->delivery_latitude }},{{ (float) $order->delivery_longitude }}" 
                                           target="_blank" rel="noopener noreferrer"
                                           class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold border border-blue-200 transition-colors shadow-2xs">
                                            <i data-lucide="map" class="w-3.5 h-3.5"></i>
                                            <span>View on Google Maps</span>
                                            <i data-lucide="external-link" class="w-3 h-3 text-blue-400"></i>
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-200 text-gray-500 text-[11px] flex items-start gap-2">
                                    <i data-lucide="info" class="w-3.5 h-3.5 text-gray-400 shrink-0 mt-0.5"></i>
                                    <span>No GPS coordinates recorded for this order (Customer entered address manually).</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Delivery Partner Section (Only for Delivery Orders) -->
            @if($order->isDelivery())
            <div class="bg-white p-6 rounded-3xl border border-[#F0E6EC] shadow-sm space-y-4 text-xs">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-[#25232A] uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="bike" class="w-4 h-4 text-[#F50070]"></i>
                        <span>Delivery Partner</span>
                    </h3>
                    @if($order->deliveryPerson)
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                            Assigned
                        </span>
                    @else
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                            Unassigned
                        </span>
                    @endif
                </div>

                @if($order->deliveryPerson)
                    <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                        <div>
                            <span class="font-extrabold text-sm text-[#25232A] block">{{ $order->deliveryPerson->name }}</span>
                            <span class="text-gray-500 text-xs">{{ $order->deliveryPerson->phone }}</span>
                        </div>
                        <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                            ✓
                        </span>
                    </div>
                @endif

                <form action="{{ route('admin.orders.assign-rider', $order) }}" method="POST" class="space-y-3 pt-1">
                    @csrf
                    <label class="block text-[11px] font-bold text-gray-600">
                        {{ $order->deliveryPerson ? 'Reassign Delivery Rider:' : 'Choose Delivery Rider:' }}
                    </label>
                    
                    <!-- Full width dropdown -->
                    <select name="delivery_person_id" required 
                            class="w-full text-xs p-3 rounded-2xl border border-[#F0E6EC] bg-white focus:border-[#F50070] focus:outline-none shadow-2xs font-medium">
                        <option value="">-- Select Active Rider --</option>
                        @foreach($deliveryRiders as $rider)
                            <option value="{{ $rider->id }}" {{ $order->delivery_person_id == $rider->id ? 'selected' : '' }}>
                                {{ $rider->name }} ({{ $rider->phone }})
                            </option>
                        @endforeach
                    </select>

                    <!-- Button directly below dropdown at full width -->
                    <button type="submit" 
                            class="w-full py-3 px-4 rounded-2xl bg-[#25232A] hover:bg-[#F50070] text-white text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="bike" class="w-4 h-4"></i>
                        <span>{{ $order->deliveryPerson ? 'Update Rider Assignment' : 'Assign Delivery Rider' }}</span>
                    </button>
                </form>
            </div>
            @endif

            <!-- Milestone Status Audit Log -->
            <div class="bg-white p-6 rounded-3xl border border-[#F0E6EC] shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-[#25232A] uppercase tracking-wider">Status Milestones</h3>
                <div class="divide-y divide-gray-100 max-h-56 overflow-y-auto pr-1">
                    @forelse($order->statusHistories as $hist)
                    <div class="py-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#25232A] capitalize block">{{ str_replace('_', ' ', $hist->to_status) }}</span>
                            <span class="text-[9px] text-gray-400 font-mono">{{ $hist->created_at->format('d M, h:i A') }}</span>
                        </div>
                        <p class="text-gray-500 text-[11px] mt-0.5">{{ $hist->comment }}</p>
                    </div>
                    @empty
                    <p class="text-gray-400 py-3 text-center">No history recorded</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Cancellation Modal with Confirmation -->
    <div x-show="cancelModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         style="display: none;">
        <div @click.outside="cancelModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#F0E6EC] space-y-4">
            <h3 class="text-base font-black text-[#25232A]">Cancel Order #{{ $order->order_number }}</h3>
            <p class="text-xs text-[#66636D]">This action cannot be undone. Please state a specific reason for cancelling this order.</p>

            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Cancellation Reason <span class="text-rose-500">*</span></label>
                    <textarea name="cancellation_reason" rows="3" required placeholder="E.g. Item out of stock, customer requested cancellation, outside delivery boundary..."
                              class="w-full p-3 rounded-2xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="cancelModal = false" class="px-4 py-2 font-bold text-gray-500 hover:text-gray-700">Back</button>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold transition-colors">Confirm Cancel</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
