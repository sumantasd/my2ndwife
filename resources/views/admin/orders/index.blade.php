@extends('layouts.admin')

@section('title', 'Order Management')
@section('header_title', 'Orders Board')

@section('content')
<div class="space-y-6">

    <!-- Status Filter Tabs Strip -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        @php
            $statuses = [
                'all' => ['label' => 'All Orders', 'color' => 'bg-[#25232A] text-white'],
                \App\Models\Order::STATUS_PLACED => ['label' => 'New Placed', 'color' => 'bg-amber-500 text-white'],
                \App\Models\Order::STATUS_ACCEPTED => ['label' => 'Accepted', 'color' => 'bg-blue-600 text-white'],
                \App\Models\Order::STATUS_PREPARING => ['label' => 'Cooking', 'color' => 'bg-purple-600 text-white'],
                \App\Models\Order::STATUS_READY => ['label' => 'Ready / Packed', 'color' => 'bg-indigo-600 text-white'],
                \App\Models\Order::STATUS_OUT_FOR_DELIVERY => ['label' => 'On Way', 'color' => 'bg-[#F50070] text-white'],
                \App\Models\Order::STATUS_DELIVERED => ['label' => 'Delivered', 'color' => 'bg-emerald-600 text-white'],
                \App\Models\Order::STATUS_CANCELLED => ['label' => 'Cancelled', 'color' => 'bg-rose-600 text-white'],
            ];
            $currentStatus = request('status', 'all');
        @endphp

        @foreach($statuses as $key => $st)
        <a href="{{ request()->fullUrlWithQuery(['status' => $key, 'page' => 1]) }}"
           class="px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all border flex items-center gap-2 {{ $currentStatus === $key ? $st['color'] . ' border-transparent shadow-md' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
            <span>{{ $st['label'] }}</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currentStatus === $key ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 font-extrabold' }}">
                {{ $statusCounts[$key] ?? 0 }}
            </span>
        </a>
        @endforeach
    </div>

    <!-- Filters Bar & CSV Export -->
    <div class="bg-white p-5 rounded-3xl border border-[#F0E6EC] shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto flex-1">
            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif

            <div class="relative flex-1 sm:w-64">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search order #, customer name, phone..."
                       class="w-full text-xs pl-9 pr-4 py-2.5 rounded-full border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
            </div>

            <select name="date_range" class="text-xs p-2.5 rounded-xl border border-[#F0E6EC] bg-white">
                <option value="">All Time</option>
                <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ request('date_range') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="this_week" {{ request('date_range') === 'this_week' ? 'selected' : '' }}>This Week</option>
                <option value="this_month" {{ request('date_range') === 'this_month' ? 'selected' : '' }}>This Month</option>
            </select>

            <select name="payment_method" class="text-xs p-2.5 rounded-xl border border-[#F0E6EC] bg-white">
                <option value="">All Payment Types</option>
                <option value="cash_on_delivery" {{ request('payment_method') === 'cash_on_delivery' ? 'selected' : '' }}>Cash on Delivery</option>
                <option value="razorpay" {{ request('payment_method') === 'razorpay' ? 'selected' : '' }}>Online (Razorpay)</option>
            </select>

            <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#25232A] text-white text-xs font-bold hover:bg-[#F50070] transition-colors">
                Apply Filters
            </button>
            @if(request()->hasAny(['search', 'date_range', 'payment_method']))
                <a href="{{ route('admin.orders.index', ['status' => request('status')]) }}" class="text-xs text-gray-500 hover:text-[#F50070] font-bold">Reset</a>
            @endif
        </form>

        <a href="{{ route('admin.orders.export', request()->query()) }}" 
           class="px-4 py-2.5 rounded-full bg-gray-100 hover:bg-gray-200 text-[#25232A] text-xs font-bold flex items-center gap-1.5 transition-colors whitespace-nowrap self-end md:self-auto">
            <i data-lucide="download" class="w-4 h-4"></i>
            <span>Export CSV</span>
        </a>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-3xl border border-[#F0E6EC] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px] bg-gray-50/50">
                        <th class="py-3.5 px-6">Order #</th>
                        <th class="py-3.5 px-4">Customer Details</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Payment</th>
                        <th class="py-3.5 px-4">Rider</th>
                        <th class="py-3.5 px-4">Total</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="py-3.5 px-6 align-middle">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-extrabold text-[#25232A] hover:text-[#F50070]">
                                #{{ $order->order_number }}
                            </a>
                            <span class="block text-[10px] text-gray-400">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                            <div class="flex items-center gap-1 mt-1 flex-wrap">
                                <span class="text-[9px] uppercase font-bold px-2 py-0.5 rounded-full {{ $order->isTakeaway() ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                    {{ $order->isTakeaway() ? 'Takeaway' : 'Delivery' }}
                                </span>
                                @if($order->order_schedule === 'scheduled')
                                    <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded-full bg-purple-100 text-purple-800 border border-purple-200" title="{{ $order->scheduled_at ? $order->scheduled_at->format('d M Y, h:i A') : 'Scheduled' }}">
                                        🕒 {{ $order->scheduled_at ? $order->scheduled_at->format('d M, h:i A') : 'Scheduled' }}
                                    </span>
                                @else
                                    <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        ⚡ Instant
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4 align-middle">
                            <span class="font-extrabold text-[#25232A] block">{{ $order->customer_name }}</span>
                            <span class="text-gray-400 text-[10px]">{{ $order->customer_phone }}</span>
                            <span class="text-gray-400 text-[10px] block truncate max-w-[200px]">{{ $order->delivery_address_text ?? 'Counter Pickup' }}</span>
                        </td>
                        <td class="py-3.5 px-4 align-middle">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase border {{ $order->status_badge_color }}">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 align-middle">
                            <span class="font-bold text-[#25232A] block uppercase text-[10px]">
                                {{ str_replace('_', ' ', $order->payment?->payment_method ?? 'COD') }}
                            </span>
                            <span class="text-[10px] font-bold {{ $order->isPaid() ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $order->isPaid() ? '● Paid' : '○ Pending' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 align-middle text-gray-600">
                            @if($order->isTakeaway())
                                <span class="text-gray-400 italic text-[11px]">N/A (Takeaway)</span>
                            @else
                                {{ $order->deliveryPerson?->name ?? 'Unassigned' }}
                            @endif
                        </td>
                        <td class="py-3.5 px-4 align-middle font-black text-[#25232A] text-sm whitespace-nowrap">
                            ₹{{ number_format($order->total_amount, 2) }}
                        </td>
                        <td class="py-3.5 px-6 text-right whitespace-nowrap align-middle">
                            <div class="inline-flex items-center justify-end gap-2">
                                <a href="{{ route('admin.orders.show', $order) }}" 
                                   class="inline-flex items-center justify-center px-3 py-1.5 h-8 rounded-xl bg-[#FFF3F8] text-[#F50070] font-bold text-xs hover:bg-[#F50070] hover:text-white transition-colors border border-[#F0E6EC] shadow-2xs">
                                    Manage
                                </a>
                                <a href="{{ route('admin.orders.receipt', $order) }}" target="_blank" title="Print Thermal Receipt (58/80mm)"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-gray-50 border border-gray-200 text-gray-600 hover:text-[#F50070] hover:bg-[#FFF3F8] hover:border-[#F50070] transition-colors"
                                   aria-label="Print Thermal Receipt">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-gray-400">No orders found matching this view.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
