@extends('layouts.delivery')

@section('title', 'Earnings & Commission')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E8E5EF] shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-base sm:text-lg font-black text-[#202033]">Rider Earnings & Commission</h1>
            <p class="text-xs text-[#55597D]">Track your daily, weekly, and monthly delivery earnings</p>
        </div>

        <div class="px-4 py-2 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-black flex items-center gap-2">
            <i data-lucide="award" class="w-4 h-4 text-amber-600"></i>
            <span>Fleet Rate: ₹{{ number_format($commissionRate, 2) }} / delivered order</span>
        </div>
    </div>

    <!-- Earnings Overview Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Today -->
        <div class="bg-white rounded-3xl p-5 border border-[#E8E5EF] shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085] block mb-1">Today</span>
            <div class="text-2xl font-black text-amber-600 font-mono">₹{{ number_format($todayEarnings, 2) }}</div>
            <p class="text-[11px] font-bold text-[#55597D] mt-1">{{ $todayDeliveries }} order(s) delivered</p>
        </div>

        <!-- This Week -->
        <div class="bg-white rounded-3xl p-5 border border-[#E8E5EF] shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085] block mb-1">This Week</span>
            <div class="text-2xl font-black text-blue-600 font-mono">₹{{ number_format($weekEarnings, 2) }}</div>
            <p class="text-[11px] font-bold text-[#55597D] mt-1">{{ $weekDeliveries }} order(s) delivered</p>
        </div>

        <!-- This Month -->
        <div class="bg-white rounded-3xl p-5 border border-[#E8E5EF] shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085] block mb-1">This Month</span>
            <div class="text-2xl font-black text-purple-600 font-mono">₹{{ number_format($monthEarnings, 2) }}</div>
            <p class="text-[11px] font-bold text-[#55597D] mt-1">{{ $monthDeliveries }} order(s) delivered</p>
        </div>

        <!-- Lifetime -->
        <div class="bg-white rounded-3xl p-5 border border-[#E8E5EF] shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085] block mb-1">Lifetime Total</span>
            <div class="text-2xl font-black text-[#00B884] font-mono">₹{{ number_format($lifetimeEarnings, 2) }}</div>
            <p class="text-[11px] font-bold text-[#55597D] mt-1">{{ $lifetimeDeliveries }} total deliveries</p>
        </div>
    </div>

    <!-- Payout Information Note -->
    <div class="bg-[#FFF0F6] rounded-3xl p-5 border border-[#FEE2EB] flex items-start gap-3.5 text-xs">
        <div class="w-8 h-8 rounded-xl bg-[#FF006E] text-white flex items-center justify-center shrink-0">
            <i data-lucide="info" class="w-4 h-4"></i>
        </div>
        <div class="space-y-1">
            <h3 class="font-black text-[#202033]">Settlement & Payout Policy</h3>
            <p class="text-[#55597D] leading-relaxed">
                Earnings are credited immediately upon each confirmed order delivery. For Cash on Delivery (COD) orders, cash collected must be submitted to the restaurant accounts desk during your daily shift checkout.
            </p>
        </div>
    </div>

    <!-- Breakdown Table -->
    <div class="bg-white rounded-3xl border border-[#E8E5EF] shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-[#E8E5EF]">
            <h2 class="text-base font-extrabold text-[#202033]">Recent Completed Delivery Earnings</h2>
            <p class="text-xs text-[#55597D]">Itemized ledger of confirmed delivery payouts</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#E8E5EF] text-[#667085] font-bold uppercase tracking-wider text-[10px] bg-gray-50/50">
                        <th class="py-3 px-6">Order Details</th>
                        <th class="py-3 px-4">Completion Date</th>
                        <th class="py-3 px-4">Customer Bill</th>
                        <th class="py-3 px-4">Payment Method</th>
                        <th class="py-3 px-6 text-right">Earning Credited</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E5EF]">
                    @forelse($recentDeliveries as $order)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="py-3.5 px-6">
                                <a href="{{ route('delivery.orders.show', $order) }}" class="font-mono font-black text-sm text-[#FF006E] hover:underline">
                                    #{{ $order->order_number }}
                                </a>
                                <p class="text-[11px] text-[#667085]">{{ $order->customer_name }}</p>
                            </td>
                            <td class="py-3.5 px-4 text-[#55597D] font-mono">
                                {{ $order->delivered_at ? $order->delivered_at->format('d M Y, h:i A') : $order->updated_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-[#202033]">
                                ₹{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $order->payment && $order->payment->payment_method === 'cash_on_delivery' ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-emerald-100 text-emerald-900 border border-emerald-200' }}">
                                    {{ $order->payment ? ucfirst(str_replace('_', ' ', $order->payment->payment_method)) : 'COD' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right font-mono font-black text-[#00B884] text-sm">
                                +₹{{ number_format($commissionRate, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-[#667085]">No completed delivery earnings recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($recentDeliveries->hasPages())
        <div class="pt-2">
            {{ $recentDeliveries->links() }}
        </div>
    @endif

</div>
@endsection
