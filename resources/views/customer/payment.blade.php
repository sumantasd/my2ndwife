@extends('layouts.app')

@section('title', 'Complete Payment | My 2nd Wife')

@section('content')
<div class="max-w-xl mx-auto px-4 py-12 sm:py-16">
    <div class="bg-white p-6 sm:p-9 rounded-3xl border border-[#EFF0F6] shadow-md text-center space-y-6">
        
        <div class="w-16 h-16 rounded-2xl bg-[#FFF0F6] text-[#FF006B] flex items-center justify-center mx-auto text-2xl border border-pink-200 shadow-sm">
            💳
        </div>

        <div>
            <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200 font-rubik">Payment Pending</span>
            <h1 class="text-2xl font-black text-[#1F1F39] font-rubik mt-2">Complete Your Payment</h1>
            <p class="text-xs text-[#6E7191] mt-1">Order <strong class="text-[#1F1F39]">#{{ $order->order_number }}</strong> • Total Payable: <strong class="text-[#FF006B] text-base font-black">₹{{ number_format($order->total_amount, 2) }}</strong></p>
        </div>

        <!-- Notification Banner Container -->
        <div id="payment-notice" style="display: none;" class="p-3.5 rounded-2xl text-xs text-left"></div>

        @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-left text-xs text-rose-900 flex items-start gap-2.5">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5"></i>
            <div>
                <p class="font-bold">Payment Notice</p>
                <p class="text-[11px] text-rose-800 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        <!-- Order Items Summary Accordion -->
        <div class="text-left bg-[#F7F7FC] p-4 rounded-2xl border border-[#EFF0F6] space-y-2 text-xs">
            <div class="flex items-center justify-between font-bold text-[#1F1F39] pb-2 border-b border-gray-200">
                <span>Items ({{ $order->items->count() }})</span>
                <span>₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
            <div class="space-y-1.5 max-h-36 overflow-y-auto pt-1">
                @foreach($order->items as $item)
                <div class="flex items-center justify-between text-[#6E7191] text-[11px]">
                    <span class="truncate pr-2">{{ $item->quantity }}x {{ $item->product_name }}</span>
                    <span class="font-bold text-[#1F1F39] shrink-0">₹{{ number_format($item->line_total, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Official Razorpay Checkout Button -->
        <button id="rzp-pay-button" 
                class="w-full py-4 rounded-full bg-[#FF006B] hover:bg-[#E60060] active:scale-95 text-white font-bold text-sm flex items-center justify-center gap-2 shadow-lg shadow-pink-500/25 transition-all">
            <i data-lucide="shield-check" class="w-5 h-5"></i>
            <span id="rzp-btn-text">Pay ₹{{ number_format($order->total_amount, 2) }} via Razorpay</span>
        </button>

        <form id="rzp-verify-form" action="{{ route('order.payment.verify', ['token' => $order->tracking_token]) }}" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="razorpay_payment_id" id="rzp_payment_id">
            <input type="hidden" name="razorpay_order_id" id="rzp_order_id">
            <input type="hidden" name="razorpay_signature" id="rzp_signature">
        </form>

        <div class="pt-4 border-t border-[#EFF0F6] flex flex-wrap items-center justify-center gap-3 text-xs text-[#6E7191]">
            <span class="flex items-center gap-1"><i data-lucide="lock" class="w-3.5 h-3.5 text-[#1AB759]"></i> 256-bit SSL Secure Checkout</span>
            <span>•</span>
            <span class="text-[11px]">Supports UPI, GPay, PhonePe, Cards & NetBanking</span>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const payButton = document.getElementById('rzp-pay-button');
        const btnText = document.getElementById('rzp-btn-text');
        const noticeBox = document.getElementById('payment-notice');

        function showNotice(type, title, message) {
            noticeBox.style.display = 'block';
            if (type === 'error') {
                noticeBox.className = 'p-3.5 rounded-2xl text-xs text-left bg-rose-50 border border-rose-200 text-rose-900';
            } else if (type === 'warning') {
                noticeBox.className = 'p-3.5 rounded-2xl text-xs text-left bg-amber-50 border border-amber-200 text-amber-900';
            } else {
                noticeBox.className = 'p-3.5 rounded-2xl text-xs text-left bg-blue-50 border border-blue-200 text-blue-900';
            }
            noticeBox.innerHTML = `<strong>${title}</strong><div class="text-[11px] mt-0.5">${message}</div>`;
        }

        const razorpayOptions = {
            "key": "{{ $razorpayData['key_id'] }}",
            "amount": {{ $razorpayData['amount'] }},
            "currency": "{{ $razorpayData['currency'] }}",
            "name": "{{ addslashes($razorpayData['name']) }}",
            "description": "{{ addslashes($razorpayData['description']) }}",
            "order_id": "{{ $razorpayData['order_id'] }}",
            "prefill": @json($razorpayData['prefill']),
            "theme": {
                "color": "#FF006B"
            },
            "handler": function (response) {
                btnText.textContent = 'Verifying payment with bank...';
                payButton.disabled = true;
                payButton.classList.add('opacity-75', 'cursor-not-allowed');

                document.getElementById('rzp_payment_id').value = response.razorpay_payment_id;
                document.getElementById('rzp_order_id').value = response.razorpay_order_id;
                document.getElementById('rzp_signature').value = response.razorpay_signature;

                // Send via AJAX verification with redirect fallback
                fetch('{{ route('order.payment.verify', ['token' => $order->tracking_token]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else if (data.success) {
                        window.location.href = '{{ route('order.track', ['token' => $order->tracking_token]) }}';
                    } else {
                        payButton.disabled = false;
                        payButton.classList.remove('opacity-75', 'cursor-not-allowed');
                        btnText.textContent = 'Retry Payment (₹{{ number_format($order->total_amount, 2) }})';
                        showNotice('error', 'Payment Verification Failed', data.message || 'Signature mismatch. Please try again.');
                    }
                })
                .catch(() => {
                    // Fallback to standard form submission
                    document.getElementById('rzp-verify-form').submit();
                });
            },
            "modal": {
                "ondismiss": function() {
                    payButton.disabled = false;
                    payButton.classList.remove('opacity-75', 'cursor-not-allowed');
                    btnText.textContent = 'Pay ₹{{ number_format($order->total_amount, 2) }} via Razorpay';
                    showNotice('warning', 'Payment Cancelled', 'Razorpay checkout was closed without completing payment. Your order has not been placed. You can click the button above anytime to complete payment.');
                }
            }
        };

        const rzp = new Razorpay(razorpayOptions);

        rzp.on('payment.failed', function (response) {
            payButton.disabled = false;
            payButton.classList.remove('opacity-75', 'cursor-not-allowed');
            btnText.textContent = 'Retry Payment';

            const errDesc = response.error ? response.error.description : 'Payment transaction failed.';
            showNotice('error', 'Payment Failed', errDesc);

            // Record failure on server
            fetch('{{ route('order.payment.failed', ['token' => $order->tracking_token]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    reason: errDesc,
                    error_code: response.error ? response.error.code : 'UNKNOWN',
                    razorpay_order_id: response.error && response.error.metadata ? response.error.metadata.order_id : null,
                    razorpay_payment_id: response.error && response.error.metadata ? response.error.metadata.payment_id : null
                })
            }).catch(console.error);
        });

        payButton.onclick = function(e) {
            e.preventDefault();
            payButton.disabled = true;
            btnText.textContent = 'Opening Razorpay Checkout...';
            try {
                rzp.open();
            } catch (err) {
                payButton.disabled = false;
                btnText.textContent = 'Pay ₹{{ number_format($order->total_amount, 2) }} via Razorpay';
                showNotice('error', 'Gateway Error', 'Could not open Razorpay checkout: ' + err.message);
            }
        };

        // Automatically open Razorpay checkout if customer arrived from checkout page
        setTimeout(() => {
            try {
                rzp.open();
            } catch(e) {
                console.log('Auto-open deferred to user click');
            }
        }, 300);
    });
</script>
@endsection
