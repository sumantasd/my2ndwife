@extends('layouts.admin')

@section('title', 'Payment Records')
@section('header_title', 'Payment Ledger & Transactions')

@section('content')
<div class="space-y-6" x-data="{ verifyModal: false, selectedPaymentId: null, orderNumber: '' }">

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Revenue Collected</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">₹{{ number_format($metrics['total_collected'], 2) }}</p>
        </div>
        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Pending COD Cash</span>
            <p class="text-2xl font-black text-amber-600 mt-1">₹{{ number_format($metrics['pending_cod'], 2) }}</p>
        </div>
        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Successful Online Payments</span>
            <p class="text-2xl font-black text-[#F50070] mt-1">{{ $metrics['online_success'] }}</p>
        </div>
        <div class="p-6 rounded-3xl bg-white border border-[#F0E6EC] shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Failed Transactions</span>
            <p class="text-2xl font-black text-rose-600 mt-1">{{ $metrics['failed'] }}</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-5 rounded-3xl border border-[#F0E6EC] shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.payments.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto flex-1">
            <div class="relative flex-1 sm:w-64">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search txn ID, order #, customer..."
                       class="w-full text-xs pl-9 pr-4 py-2.5 rounded-full border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
            </div>

            <select name="method" class="text-xs p-2.5 rounded-xl border border-[#F0E6EC] bg-white">
                <option value="all">All Payment Methods</option>
                <option value="cash_on_delivery" {{ request('method') === 'cash_on_delivery' ? 'selected' : '' }}>Cash on Delivery</option>
                <option value="razorpay" {{ request('method') === 'razorpay' ? 'selected' : '' }}>Razorpay Online</option>
            </select>

            <select name="status" class="text-xs p-2.5 rounded-xl border border-[#F0E6EC] bg-white">
                <option value="all">All Statuses</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>

            <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#25232A] text-white text-xs font-bold hover:bg-[#F50070] transition-colors">
                Filter
            </button>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-3xl border border-[#F0E6EC] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px] bg-gray-50/50">
                        <th class="py-3.5 px-6">Order #</th>
                        <th class="py-3.5 px-4">Method</th>
                        <th class="py-3.5 px-4">Razorpay Order / Txn ID</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Payment Status</th>
                        <th class="py-3.5 px-4">Verification</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                    @php
                        $resp = $payment->gateway_response ?? [];
                        $isRazorpay = ($payment->payment_method === 'razorpay');
                        $failureReason = $resp['failed_reason'] ?? $resp['error'] ?? null;
                        if (!$failureReason && isset($resp['details']['error_description'])) {
                            $failureReason = $resp['details']['error_description'];
                        }
                    @endphp
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="py-3.5 px-6 font-mono font-bold text-[#25232A]">
                            <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-[#F50070] hover:underline">
                                #{{ $payment->order?->order_number }}
                            </a>
                            <div class="text-[10px] text-gray-400 font-sans font-normal">{{ $payment->order?->customer_name }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-bold capitalize">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] {{ $isRazorpay ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-gray-100 text-gray-700' }}">
                                <span>{{ $isRazorpay ? '💳' : '💵' }}</span>
                                <span>{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-gray-600 text-[11px] space-y-0.5">
                            @if($payment->gateway_order_id)
                                <div><span class="text-[9px] uppercase tracking-wider text-gray-400 font-sans font-bold">Order ID:</span> {{ $payment->gateway_order_id }}</div>
                            @endif
                            @if($payment->transaction_id)
                                <div><span class="text-[9px] uppercase tracking-wider text-emerald-600 font-sans font-bold">Payment ID:</span> <span class="text-emerald-700 font-bold">{{ $payment->transaction_id }}</span></div>
                            @elseif(!$payment->gateway_order_id)
                                <span class="text-gray-400 italic">N/A (Cash/Pending)</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-black text-[#25232A]">
                            ₹{{ number_format($payment->amount, 2) }}
                            <span class="text-[10px] text-gray-400 font-normal">{{ $payment->currency }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase border {{ $payment->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($payment->status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200') }}">
                                {{ $payment->status }}
                            </span>
                            @if($payment->status === 'failed' && $failureReason)
                                <p class="text-[10px] text-rose-600 font-sans mt-1 max-w-xs truncate" title="{{ $failureReason }}">
                                    ⚠️ {{ $failureReason }}
                                </p>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-[11px]">
                            @if($payment->status === 'completed')
                                @if(!empty($resp['signature_verified']))
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-bold">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i> Signature Verified
                                    </span>
                                @elseif(!empty($resp['webhook_event']))
                                    <span class="inline-flex items-center gap-1 text-indigo-700 font-bold">
                                        <i data-lucide="webhook" class="w-3.5 h-3.5 text-indigo-600"></i> Webhook Verified
                                    </span>
                                @elseif(!empty($resp['admin_verification']))
                                    <span class="inline-flex items-center gap-1 text-blue-700 font-bold">
                                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-blue-600"></i> Admin Verified
                                    </span>
                                @else
                                    <span class="text-emerald-700 font-bold">Verified</span>
                                @endif
                            @elseif($payment->status === 'failed')
                                <span class="text-rose-600 font-semibold">Verification Failed</span>
                            @else
                                <span class="text-amber-600 font-semibold">Awaiting Payment</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-gray-500 text-[11px]">
                            {{ $payment->paid_at ? $payment->paid_at->format('M d, h:i A') : $payment->created_at->format('M d, h:i A') }}
                        </td>
                        <td class="py-3.5 px-6 text-right">
                            @if($payment->status !== 'completed')
                                <button type="button" 
                                        @click="selectedPaymentId = {{ $payment->id }}; orderNumber = '{{ $payment->order?->order_number }}'; verifyModal = true"
                                        class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white font-bold text-xs border border-emerald-200 transition-colors">
                                    Mark Paid
                                </button>
                            @else
                                <span class="text-emerald-600 font-bold text-xs flex items-center justify-end gap-1">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Paid
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-gray-400">No payment records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

    <!-- Manual Verify Modal -->
    <div x-show="verifyModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.outside="verifyModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#F0E6EC] space-y-4">
            <h3 class="text-base font-black text-[#25232A]">Verify Cash / Manual Payment</h3>
            <p class="text-xs text-[#66636D]">Confirm receipt of funds for order <strong x-text="'#' + orderNumber"></strong>.</p>

            <form :action="'/admin/payments/' + selectedPaymentId + '/verify'" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Verification Reference / Receipt Note <span class="text-rose-500">*</span></label>
                    <input type="text" name="notes" required placeholder="E.g. Cash collected by Rider Amit Roy or Bank transfer ref"
                           class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" @click="verifyModal = false" class="px-4 py-2 font-bold text-gray-500">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold">Confirm & Mark Completed</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
