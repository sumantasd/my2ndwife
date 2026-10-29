@extends('layouts.app')

@section('title', 'Checkout | FoodKing Dining - My 2nd Wife')

@section('content')
@php
    $deliveryEnabled = (bool) \App\Models\RestaurantSetting::get('delivery_enabled', true);
    $takeawayEnabled = (bool) \App\Models\RestaurantSetting::get('takeaway_enabled', true);
    $codEnabled = (bool) \App\Models\RestaurantSetting::get('cod_enabled', true);
    $razorpayEnabled = (bool) \App\Models\RestaurantSetting::get('razorpay_enabled', true);
    $taxEnabled = (bool) \App\Models\RestaurantSetting::get('tax_enabled', true);
    $taxRate = (float) \App\Models\RestaurantSetting::get('tax_rate_percentage', 5.00);
    $defaultDeliveryType = $deliveryEnabled ? 'delivery' : 'pickup';
    $defaultPayment = $codEnabled ? 'cash_on_delivery' : ($razorpayEnabled ? 'razorpay' : 'cash_on_delivery');
@endphp

<div class="pt-8 pb-16"
     x-data="{
         orderType: '{{ $defaultDeliveryType }}',
         addressMode: '{{ $savedAddresses->count() > 0 ? 'saved' : 'new' }}',
         scheduleMode: 'asap',
         paymentMethod: '{{ $defaultPayment }}',
         latitude: null,
         longitude: null,
         addressLine1: '{{ old('address_line_1', '') }}',
         city: '{{ old('city', 'Kolkata') }}',
         postalCode: '{{ old('postal_code', '') }}',
         deliveryInstructions: '{{ old('delivery_instructions', '') }}',
         serviceable: true,
         serviceCheckMessage: '',
         serviceDistanceKm: null,
         deliveryFee: {{ $totals['delivery_charge'] }},
         totalAmount: {{ $totals['total_amount'] }},
         checkingService: false,
         gettingLocation: false,
         geoError: '',
         resolvedAddressSummary: '',
         isSubmitting: false,
         submitBtnText: 'Confirm & Place Order',
         checkoutNotice: '',
         checkoutNoticeType: '',
         pendingOrderToken: null,
         pendingPaymentUrl: null,

         useCurrentLocation() {
             this.geoError = '';
             if (!navigator.geolocation) {
                 this.geoError = 'Geolocation is not supported by your browser. Please enter your address manually below.';
                 if (window.showToast) {
                     window.showToast({ type: 'error', title: 'Location Unavailable', message: this.geoError });
                 }
                 return;
             }

             this.gettingLocation = true;
             this.checkingService = true;

             navigator.geolocation.getCurrentPosition(
                 (position) => {
                     this.latitude = position.coords.latitude;
                     this.longitude = position.coords.longitude;
                     this.gettingLocation = false;
                     this.addressMode = 'new';
                     this.verifyDeliveryCoordinates(true);
                 },
                 (err) => {
                     this.gettingLocation = false;
                     this.checkingService = false;
                     let msg = 'Could not retrieve your GPS location. Please enter your address manually.';
                     if (err.code === 1) { // PERMISSION_DENIED
                         msg = 'Location permission was denied. You can manually enter your delivery address below.';
                     } else if (err.code === 2) { // POSITION_UNAVAILABLE
                         msg = 'GPS location is currently unavailable. Please enter your address manually below.';
                     } else if (err.code === 3) { // TIMEOUT
                         msg = 'Location request timed out. Please enter your address manually below.';
                     }
                     this.geoError = msg;
                     if (window.showToast) {
                         window.showToast({ type: 'error', title: 'GPS Location', message: msg });
                     }
                 },
                 { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
             );
         },

         verifyDeliveryCoordinates(reverseGeocode = false) {
             if (!this.latitude || !this.longitude) return;
             this.checkingService = true;
             fetch('{{ route('checkout.check-delivery') }}', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                     'Accept': 'application/json'
                 },
                 body: JSON.stringify({
                     latitude: this.latitude,
                     longitude: this.longitude,
                     reverse_geocode: reverseGeocode
                 })
             })
             .then(res => res.json())
             .then(data => {
                 this.checkingService = false;
                 this.serviceable = data.serviceable;
                 this.serviceCheckMessage = data.message;
                 this.serviceDistanceKm = data.distance_km;
                 if (data.serviceable) {
                     this.deliveryFee = data.delivery_fee;
                     this.totalAmount = data.total_amount;
                 }

                 if (data.address && data.address.success) {
                     this.resolvedAddressSummary = data.address.formatted_address || '';
                     if (data.address.address_line_1 && (!this.addressLine1 || reverseGeocode)) {
                         this.addressLine1 = data.address.address_line_1;
                     }
                     if (data.address.city) {
                         this.city = data.address.city;
                     }
                     if (data.address.postal_code) {
                         this.postalCode = data.address.postal_code;
                     }
                     if (window.showToast) {
                         window.showToast({
                             type: 'success',
                             title: 'Location Detected',
                             message: 'Delivery address populated from GPS. You can review or edit below.'
                         });
                     }
                 } else if (reverseGeocode) {
                     this.resolvedAddressSummary = `GPS Coordinates: ${parseFloat(this.latitude).toFixed(5)}, ${parseFloat(this.longitude).toFixed(5)}`;
                     if (window.showToast) {
                         window.showToast({
                             type: 'info',
                             title: 'GPS Coordinates Detected',
                             message: 'Please review and enter your house/flat address details below.'
                         });
                     }
                 }
             })
             .catch(err => {
                 this.checkingService = false;
                 console.error('Error verifying delivery:', err);
             });
         },

         handleCheckoutSubmit(e) {
             if (this.orderType === 'delivery' && !this.serviceable) {
                 e.preventDefault();
                 return;
             }

             if (this.paymentMethod !== 'razorpay') {
                 // COD: allow normal form submission
                 this.isSubmitting = true;
                 this.submitBtnText = 'Placing Order...';
                 return;
             }

             // Razorpay: Prevent standard submission and invoke Razorpay Checkout
             e.preventDefault();
             if (this.isSubmitting) return;

             this.isSubmitting = true;
             this.submitBtnText = 'Initializing Razorpay...';
             this.checkoutNotice = '';

             const form = e.target;
             const formData = new FormData(form);

             fetch(form.action, {
                 method: 'POST',
                 headers: {
                     'Accept': 'application/json',
                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 body: formData
             })
             .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
             .then(({ status, ok, data }) => {
                 if (!ok || !data.success) {
                     this.isSubmitting = false;
                     this.submitBtnText = 'Confirm & Place Order';
                     const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Unable to proceed with checkout.');
                     this.checkoutNotice = msg;
                     this.checkoutNoticeType = 'error';
                     if (window.showToast) {
                         window.showToast({ type: 'error', title: 'Payment Setup Error', message: msg });
                     }
                     return;
                 }

                 if (data.requires_payment && data.razorpay_options) {
                     this.pendingOrderToken = data.order_token;
                     this.pendingPaymentUrl = data.payment_url;
                     this.submitBtnText = 'Opening Payment Window...';

                     const options = Object.assign({}, data.razorpay_options, {
                         handler: (response) => {
                             this.submitBtnText = 'Verifying payment with bank...';
                             fetch(`/order/payment/${data.order_token}/verify`, {
                                 method: 'POST',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'Accept': 'application/json',
                                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                 },
                                 body: JSON.stringify({
                                     razorpay_payment_id: response.razorpay_payment_id,
                                     razorpay_order_id: response.razorpay_order_id,
                                     razorpay_signature: response.razorpay_signature
                                 })
                             })
                             .then(res => res.json())
                             .then(verifyData => {
                                 if (verifyData.success && verifyData.redirect_url) {
                                     window.location.href = verifyData.redirect_url;
                                 } else if (verifyData.success) {
                                     window.location.href = `/order/track/${data.order_token}`;
                                 } else {
                                     this.isSubmitting = false;
                                     this.submitBtnText = 'Retry Payment';
                                     this.checkoutNotice = verifyData.message || 'Payment verification failed.';
                                     this.checkoutNoticeType = 'error';
                                     if (window.showToast) {
                                         window.showToast({ type: 'error', title: 'Verification Failed', message: this.checkoutNotice });
                                     }
                                 }
                             })
                             .catch(err => {
                                 this.isSubmitting = false;
                                 this.submitBtnText = 'Retry Payment';
                                 window.location.href = data.payment_url;
                             });
                         },
                         modal: {
                             ondismiss: () => {
                                 this.isSubmitting = false;
                                 this.submitBtnText = 'Confirm & Place Order';
                                 this.checkoutNotice = 'Payment window was closed before completion. Your order has not been placed.';
                                 this.checkoutNoticeType = 'warning';
                                 if (window.showToast) {
                                     window.showToast({ type: 'info', title: 'Payment Incomplete', message: this.checkoutNotice });
                                 }
                             }
                         }
                     });

                     if (typeof Razorpay === 'undefined') {
                         this.isSubmitting = false;
                         this.submitBtnText = 'Confirm & Place Order';
                         this.checkoutNotice = 'Razorpay checkout library could not be loaded. Please reload page or use Cash on Delivery.';
                         this.checkoutNoticeType = 'error';
                         return;
                     }

                     const rzp = new Razorpay(options);

                     rzp.on('payment.failed', (response) => {
                         this.isSubmitting = false;
                         this.submitBtnText = 'Retry Payment';
                         const errMsg = response.error ? response.error.description : 'Payment transaction failed.';
                         this.checkoutNotice = errMsg;
                         this.checkoutNoticeType = 'error';
                         if (window.showToast) {
                             window.showToast({ type: 'error', title: 'Payment Failed', message: errMsg });
                         }

                         fetch(`/order/payment/${data.order_token}/failed`, {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'Accept': 'application/json',
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                             },
                             body: JSON.stringify({
                                 reason: errMsg,
                                 error_code: response.error ? response.error.code : 'UNKNOWN',
                                 razorpay_order_id: response.error && response.error.metadata ? response.error.metadata.order_id : null,
                                 razorpay_payment_id: response.error && response.error.metadata ? response.error.metadata.payment_id : null
                             })
                         }).catch(console.error);
                     });

                     try {
                         rzp.open();
                     } catch (err) {
                         this.isSubmitting = false;
                         this.submitBtnText = 'Confirm & Place Order';
                         this.checkoutNotice = 'Failed to open Razorpay modal: ' + err.message;
                         this.checkoutNoticeType = 'error';
                     }
                 } else if (data.redirect_url) {
                     window.location.href = data.redirect_url;
                 }
             })
             .catch(err => {
                 this.isSubmitting = false;
                 this.submitBtnText = 'Confirm & Place Order';
                 this.checkoutNotice = 'Connection error during order placement. Please check your internet connection.';
                 this.checkoutNoticeType = 'error';
             });
         }
     }">

    <div class="max-w-[965px] mx-auto px-4 sm:px-6">

        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl sm:text-[26px] font-bold text-[#1F1F39] font-rubik">Checkout</h1>
            <p class="text-xs text-[#6E7191] mt-0.5">Please confirm your delivery address and payment method</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold space-y-1">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    <span>Please correct the errors before placing order:</span>
                </div>
                <ul class="list-disc pl-6 font-normal">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('checkout.process') }}" method="POST" @submit="handleCheckoutSubmit($event)">
            @csrf

            <!-- Dynamic Checkout Feedback Banner -->
            <div x-show="checkoutNotice" x-cloak style="display: none;" class="mb-5 p-4 rounded-2xl text-xs space-y-1.5"
                 :class="checkoutNoticeType === 'error' ? 'bg-rose-50 border border-rose-200 text-rose-900' : (checkoutNoticeType === 'warning' ? 'bg-amber-50 border border-amber-200 text-amber-900' : 'bg-blue-50 border border-blue-200 text-blue-900')">
                <div class="flex items-center justify-between">
                    <span class="font-bold flex items-center gap-1.5">
                        <i :data-lucide="checkoutNoticeType === 'error' ? 'alert-circle' : 'alert-triangle'" class="w-4 h-4"></i>
                        <span x-text="checkoutNoticeType === 'error' ? 'Payment Notice' : 'Notice'"></span>
                    </span>
                    <button type="button" @click="checkoutNotice = ''" class="font-bold px-1 text-sm leading-none opacity-60 hover:opacity-100">✕</button>
                </div>
                <p x-text="checkoutNotice" class="text-[11px]"></p>
                <template x-if="pendingPaymentUrl">
                    <div class="pt-1">
                        <a :href="pendingPaymentUrl" class="font-bold underline text-[#FF006B] hover:text-[#E60060]">Click here to resume payment on the dedicated payment page →</a>
                    </div>
                </template>
            </div>

            <!-- Hidden Delivery Coordinates -->
            <input type="hidden" name="delivery_latitude" :value="latitude">
            <input type="hidden" name="delivery_longitude" :value="longitude">

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                
                <!-- Left Column: Forms -->
                <div class="md:col-span-7 space-y-5">

                    <!-- 1. Fulfillment Type Card -->
                    <div class="p-4 sm:p-5 rounded-2xl shadow-xs bg-white border border-[#EFF0F6]">
                        <h2 class="text-xs font-bold text-[#1F1F39] uppercase tracking-wider mb-3.5 flex items-center gap-2">
                            <i data-lucide="truck" class="w-4 h-4 text-[#FF006B]"></i>
                            <span>1. Delivery Option</span>
                        </h2>

                        <div class="grid grid-cols-2 gap-3">
                            @if($deliveryEnabled)
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition-all text-xs"
                                       :class="orderType === 'delivery' ? 'border-[#FF006B] bg-[#FFF0F6] text-[#FF006B] font-bold shadow-xs' : 'border-[#EFF0F6] text-[#1F1F39] hover:bg-[#F7F7FC]'">
                                    <input type="radio" name="order_type" value="delivery" x-model="orderType" class="text-[#FF006B] focus:ring-[#FF006B]">
                                    <div>
                                        <span class="block">Home Delivery</span>
                                        <span class="text-[10px] text-[#6E7191] font-normal">Piping hot to doorstep</span>
                                    </div>
                                </label>
                            @else
                                <div class="p-3 rounded-xl border border-gray-200 bg-gray-50 opacity-60 text-xs">
                                    <span class="block font-bold text-gray-500">Delivery Unavailable</span>
                                    <span class="text-[10px] text-gray-400">Temporarily paused</span>
                                </div>
                            @endif

                            @if($takeawayEnabled)
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition-all text-xs"
                                       :class="orderType === 'pickup' ? 'border-[#FF006B] bg-[#FFF0F6] text-[#FF006B] font-bold shadow-xs' : 'border-[#EFF0F6] text-[#1F1F39] hover:bg-[#F7F7FC]'">
                                    <input type="radio" name="order_type" value="pickup" x-model="orderType" class="text-[#FF006B] focus:ring-[#FF006B]">
                                    <div>
                                        <span class="block">Takeaway Pickup</span>
                                        <span class="text-[10px] text-[#6E7191] font-normal">Collect at restaurant</span>
                                    </div>
                                </label>
                            @endif
                        </div>
                    </div>

                    <!-- 2. Customer Information Card -->
                    <div class="p-4 sm:p-5 rounded-2xl shadow-xs bg-white border border-[#EFF0F6] space-y-3.5">
                        <h2 class="text-xs font-bold text-[#1F1F39] uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="user" class="w-4 h-4 text-[#FF006B]"></i>
                            <span>2. Contact Information</span>
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-[#1F1F39] mb-1">Full Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="customer_name" required
                                       value="{{ old('customer_name', $user?->name) }}"
                                       placeholder="Your Name"
                                       class="w-full text-xs p-2.5 rounded-xl border border-[#EFF0F6] bg-[#F7F7FC] focus:bg-white focus:border-[#FF006B] focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#1F1F39] mb-1">Mobile Number <span class="text-rose-500">*</span></label>
                                <input type="text" name="customer_phone" required
                                       value="{{ old('customer_phone', $user?->phone) }}"
                                       placeholder="10-digit mobile number"
                                       class="w-full text-xs p-2.5 rounded-xl border border-[#EFF0F6] bg-[#F7F7FC] focus:bg-white focus:border-[#FF006B] focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1F1F39] mb-1">Email Address (Optional)</label>
                            <input type="email" name="customer_email" 
                                   value="{{ old('customer_email', $user?->email) }}"
                                   placeholder="For receipt & order updates"
                                   class="w-full text-xs p-2.5 rounded-xl border border-[#EFF0F6] bg-[#F7F7FC] focus:bg-white focus:border-[#FF006B] focus:outline-none">
                        </div>
                    </div>

                    <!-- 3. Delivery Address (Visible when delivery selected) -->
                    <div x-show="orderType === 'delivery'" class="p-4 sm:p-5 rounded-2xl shadow-xs bg-white border border-[#EFF0F6] space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                            <h2 class="text-xs font-bold text-[#1F1F39] uppercase tracking-wider flex items-center gap-2">
                                <i data-lucide="map-pin" class="w-4 h-4 text-[#FF006B]"></i>
                                <span>3. Delivery Address</span>
                            </h2>

                            <!-- Geolocation GPS Detection Button -->
                            <button type="button" @click="useCurrentLocation()" :disabled="gettingLocation || checkingService"
                                    class="inline-flex items-center justify-center gap-2 text-xs font-bold text-white bg-[#FF006B] hover:bg-[#E60060] disabled:opacity-60 disabled:cursor-not-allowed px-3.5 py-2 rounded-xl shadow-xs transition-all active:scale-95">
                                <template x-if="gettingLocation || checkingService">
                                    <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </template>
                                <template x-if="!gettingLocation && !checkingService">
                                    <i data-lucide="navigation" class="w-3.5 h-3.5"></i>
                                </template>
                                <span x-text="gettingLocation ? 'Fetching GPS...' : (checkingService ? 'Verifying...' : 'Use My Current Location')">Use My Current Location</span>
                            </button>
                        </div>

                        <!-- GPS Permission / Error Banner -->
                        <div x-show="geoError" x-cloak style="display: none;" class="p-3.5 rounded-xl text-xs bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-2.5 transition-all">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                            <div class="flex-1">
                                <p class="font-bold">Location Notice</p>
                                <p class="text-[11px] text-amber-800 mt-0.5" x-text="geoError"></p>
                            </div>
                            <button type="button" @click="geoError = null" class="text-amber-500 hover:text-amber-700 text-sm font-bold leading-none px-1">✕</button>
                        </div>

                        <!-- GPS Detected Address Review Banner -->
                        <div x-show="latitude && longitude && resolvedAddressSummary" x-cloak style="display: none;" class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-950 space-y-1 transition-all">
                            <div class="flex items-center justify-between">
                                <span class="font-bold flex items-center gap-1.5 text-emerald-800">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    GPS Location Detected
                                </span>
                                <span class="text-[10px] font-mono bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full" x-text="`${parseFloat(latitude).toFixed(4)}, ${parseFloat(longitude).toFixed(4)}`"></span>
                            </div>
                            <p class="text-[11px] text-emerald-900 font-medium" x-text="resolvedAddressSummary"></p>
                            <p class="text-[10px] text-emerald-700">Please review or edit your detailed address fields below.</p>
                        </div>

                        <!-- Live Serviceability Alerts -->
                        <div x-show="serviceCheckMessage" x-cloak style="display: none;" class="p-3 rounded-xl text-xs font-bold flex items-center gap-2 transition-all"
                             :class="serviceable ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                            <i :data-lucide="serviceable ? 'check-circle' : 'alert-circle'" class="w-4 h-4 shrink-0"></i>
                            <span x-text="serviceCheckMessage"></span>
                        </div>

                        @if($savedAddresses->count() > 0)
                            <div class="flex items-center gap-3 pb-2 border-b border-[#EFF0F6]">
                                <label class="flex items-center gap-2 text-xs font-bold cursor-pointer text-[#1F1F39]">
                                    <input type="radio" name="delivery_address_selection" value="saved" x-model="addressMode" class="text-[#FF006B] focus:ring-[#FF006B]">
                                    <span>Use Saved Address</span>
                                </label>
                                <label class="flex items-center gap-2 text-xs font-bold cursor-pointer text-[#1F1F39]">
                                    <input type="radio" name="delivery_address_selection" value="new" x-model="addressMode" class="text-[#FF006B] focus:ring-[#FF006B]">
                                    <span>Enter / GPS Address</span>
                                </label>
                            </div>

                            <!-- Saved Addresses List -->
                            <div x-show="addressMode === 'saved'" class="space-y-2">
                                @foreach($savedAddresses as $addr)
                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-[#EFF0F6] hover:bg-[#FFF0F6] cursor-pointer text-xs"
                                           @click="latitude = {{ $addr->latitude ?? 'null' }}; longitude = {{ $addr->longitude ?? 'null' }}; verifyDeliveryCoordinates();">
                                        <input type="radio" name="saved_address_id" value="{{ $addr->id }}" {{ $loop->first ? 'checked' : '' }} class="mt-1 text-[#FF006B] focus:ring-[#FF006B]">
                                        <div>
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-[#FFF0F6] text-[#FF006B] mb-1">{{ $addr->type }}</span>
                                            <p class="font-bold text-[#1F1F39]">{{ $addr->contact_name }} ({{ $addr->contact_phone }})</p>
                                            <p class="text-[#6E7191] text-[11px] mt-0.5">{{ $addr->full_address }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <input type="hidden" name="delivery_address_selection" value="new">
                        @endif

                        <!-- New / GPS Address Fields -->
                        <div x-show="addressMode === 'new'" class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-[#1F1F39] mb-1">House / Flat No., Building & Street <span class="text-rose-500">*</span></label>
                                <input type="text" name="address_line_1" x-model="addressLine1" placeholder="Flat 4B, Silver Towers, Park Street"
                                       class="w-full text-xs p-2.5 rounded-xl border border-[#EFF0F6] bg-[#F7F7FC] focus:bg-white focus:border-[#FF006B] focus:outline-none">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-[#1F1F39] mb-1">City <span class="text-rose-500">*</span></label>
                                    <input type="text" name="city" x-model="city" placeholder="City"
                                           class="w-full text-xs p-2.5 rounded-xl border border-[#EFF0F6] bg-[#F7F7FC] focus:bg-white focus:border-[#FF006B] focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#1F1F39] mb-1">Postal Code <span class="text-rose-500">*</span></label>
                                    <input type="text" name="postal_code" x-model="postalCode" placeholder="e.g. 700016"
                                           class="w-full text-xs p-2.5 rounded-xl border border-[#EFF0F6] bg-[#F7F7FC] focus:bg-white focus:border-[#FF006B] focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#1F1F39] mb-1">Delivery Instructions for Rider</label>
                                <input type="text" name="delivery_instructions" x-model="deliveryInstructions" placeholder="E.g., call upon arrival, leave with security guard"
                                       class="w-full text-xs p-2.5 rounded-xl border border-[#EFF0F6] bg-[#F7F7FC] focus:bg-white focus:border-[#FF006B] focus:outline-none">
                            </div>

                            @auth
                                <label class="flex items-center gap-2 text-xs font-bold text-[#1F1F39] cursor-pointer pt-1">
                                    <input type="checkbox" name="save_address_to_profile" value="1" checked class="rounded text-[#FF006B] focus:ring-[#FF006B]">
                                    <span>Save this address to my profile</span>
                                </label>
                            @endauth
                        </div>
                    </div>

                    <!-- 4. Schedule & Instructions -->
                    <div class="p-4 sm:p-5 rounded-2xl shadow-xs bg-white border border-[#EFF0F6] space-y-3.5">
                        <h2 class="text-xs font-bold text-[#1F1F39] uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-[#FF006B]"></i>
                            <span>4. Order Timing</span>
                        </h2>

                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition-all text-xs"
                                   :class="scheduleMode === 'asap' ? 'border-[#FF006B] bg-[#FFF0F6] text-[#FF006B] font-bold shadow-xs' : 'border-[#EFF0F6] text-[#1F1F39] hover:bg-[#F7F7FC]'">
                                <input type="radio" name="order_schedule" value="asap" x-model="scheduleMode" class="text-[#FF006B] focus:ring-[#FF006B]">
                                <div>
                                    <span class="block font-bold">Instant Delivery</span>
                                    <span class="text-[10px] text-[#6E7191] font-normal">Approx. 30-40 mins</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition-all text-xs"
                                   :class="scheduleMode === 'scheduled' ? 'border-[#FF006B] bg-[#FFF0F6] text-[#FF006B] font-bold shadow-xs' : 'border-[#EFF0F6] text-[#1F1F39] hover:bg-[#F7F7FC]'">
                                <input type="radio" name="order_schedule" value="scheduled" x-model="scheduleMode" class="text-[#FF006B] focus:ring-[#FF006B]">
                                <div>
                                    <span class="block font-bold">Schedule Order</span>
                                    <span class="text-[10px] text-[#6E7191] font-normal">Choose future time</span>
                                </div>
                            </label>
                        </div>

                        <div x-show="scheduleMode === 'scheduled'" x-cloak style="display: none;" class="pt-2">
                            <label class="block text-xs font-bold text-[#1F1F39] mb-1">Select Delivery Date & Time Slot: <span class="text-rose-500">*</span></label>
                            <input type="datetime-local" name="scheduled_at"
                                   min="{{ now()->addMinutes(45)->format('Y-m-d\TH:i') }}"
                                   class="w-full text-xs p-2.5 rounded-xl border border-[#EFF0F6] bg-[#F7F7FC] focus:bg-white focus:border-[#FF006B] focus:outline-none">
                            <p class="text-[10px] text-[#6E7191] mt-1">Order must be scheduled at least 45 minutes in advance.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1F1F39] mb-1">Special Cooking Request (Optional):</label>
                            <input type="text" name="special_instructions" placeholder="E.g., less oil, extra green chilies..."
                                   class="w-full text-xs p-2.5 rounded-xl border border-[#EFF0F6] bg-[#F7F7FC] focus:bg-white focus:border-[#FF006B] focus:outline-none">
                        </div>
                    </div>

                    <!-- 5. Payment Methods (Dynamically Loaded) -->
                    <div class="p-4 sm:p-5 rounded-2xl shadow-xs bg-white border border-[#EFF0F6] space-y-3.5">
                        <h2 class="text-xs font-bold text-[#1F1F39] uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="credit-card" class="w-4 h-4 text-[#FF006B]"></i>
                            <span>5. Payment Method</span>
                        </h2>

                        <div class="space-y-2.5">
                            @if($codEnabled)
                                <label class="flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition-all text-xs"
                                       :class="paymentMethod === 'cash_on_delivery' ? 'border-[#FF006B] bg-[#FFF0F6] text-[#1F1F39] font-bold shadow-xs' : 'border-[#EFF0F6] hover:bg-[#F7F7FC]'">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="payment_method" value="cash_on_delivery" x-model="paymentMethod" class="text-[#FF006B] focus:ring-[#FF006B]">
                                        <div>
                                            <span class="font-bold block">{{ \App\Models\RestaurantSetting::get('cod_display_name', 'Cash on Delivery (COD)') }}</span>
                                            <span class="text-[10px] text-[#6E7191]">Pay in cash or UPI QR upon delivery</span>
                                        </div>
                                    </div>
                                    <span class="text-xl">💵</span>
                                </label>
                            @endif

                            @if($razorpayEnabled)
                                <label class="flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition-all text-xs"
                                       :class="paymentMethod === 'razorpay' ? 'border-[#FF006B] bg-[#FFF0F6] text-[#1F1F39] font-bold shadow-xs' : 'border-[#EFF0F6] hover:bg-[#F7F7FC]'">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="payment_method" value="razorpay" x-model="paymentMethod" class="text-[#FF006B] focus:ring-[#FF006B]">
                                        <div>
                                            <span class="font-bold block">{{ \App\Models\RestaurantSetting::get('razorpay_display_name', 'Online Payment (UPI, Cards, NetBanking)') }}</span>
                                            <span class="text-[10px] text-[#6E7191]">Instant verification via Razorpay</span>
                                        </div>
                                    </div>
                                    <span class="text-xl">💳</span>
                                </label>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- Right Column: FoodKing Order Summary -->
                <div class="md:col-span-5 space-y-5">
                    <div class="rounded-2xl shadow-xs bg-white border border-[#EFF0F6] overflow-hidden">
                        <div class="p-4 border-b border-[#EFF0F6]">
                            <h3 class="font-bold text-[#1F1F39] text-base font-rubik text-center">Order Summary</h3>
                        </div>

                        <!-- Selected Items List with Quantity Badges -->
                        <div class="p-4 space-y-3.5 border-b border-[#EFF0F6] max-h-72 overflow-y-auto divide-y divide-[#EFF0F6]">
                            @foreach($totals['items'] as $item)
                                <div class="pt-3 first:pt-0 flex items-center justify-between gap-3 relative">
                                    <div class="relative w-12 h-12 rounded-xl overflow-hidden shrink-0 border border-[#EFF0F6]">
                                        <img src="{{ $item['product_image'] }}" alt="{{ $item['product_name'] }}" class="w-full h-full object-cover">
                                        <span class="absolute top-0 right-0 bg-[#1F1F39] text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center">
                                            {{ $item['quantity'] }}
                                        </span>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-[#1F1F39] truncate">{{ $item['product_name'] }}</p>
                                        @if(!empty($item['variation_name']))
                                            <p class="text-[10px] text-[#FF006B] font-semibold">{{ $item['variation_name'] }}</p>
                                        @endif
                                    </div>

                                    <span class="text-xs font-black text-[#1F1F39]">₹{{ number_format($item['line_subtotal'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Price Breakdown -->
                        <div class="p-4 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between text-[#6E7191]">
                                <span>Order Timing:</span>
                                <span class="font-bold text-[#FF006B]" x-text="scheduleMode === 'scheduled' ? 'Scheduled Order' : 'Instant Delivery'">Instant Delivery</span>
                            </div>

                            <div class="flex items-center justify-between text-[#6E7191]">
                                <span>Subtotal:</span>
                                <span class="font-bold text-[#1F1F39]">₹{{ number_format($totals['subtotal'], 2) }}</span>
                            </div>

                            @if($totals['discount_amount'] > 0)
                                <div class="flex items-center justify-between text-emerald-600 font-bold">
                                    <span>Discount ({{ $totals['coupon_code'] }}):</span>
                                    <span>-₹{{ number_format($totals['discount_amount'], 2) }}</span>
                                </div>
                            @endif

                            <div class="flex items-center justify-between text-[#6E7191]">
                                <span>Packaging Fee:</span>
                                <span class="font-bold text-[#1F1F39]">₹{{ number_format($totals['packaging_charge'], 2) }}</span>
                            </div>

                            <div class="flex items-center justify-between text-[#6E7191]">
                                <span>Delivery Fee:</span>
                                <template x-if="orderType === 'pickup'">
                                    <span class="text-emerald-600 font-bold">FREE (Takeaway)</span>
                                </template>
                                <template x-if="orderType === 'delivery'">
                                    <span class="font-bold text-[#1F1F39]" x-text="deliveryFee == 0 ? 'FREE' : '₹' + parseFloat(deliveryFee).toFixed(2)"></span>
                                </template>
                            </div>

                            @if($taxEnabled)
                                <div class="flex items-center justify-between text-[#6E7191]">
                                    <span>GST / Taxes ({{ $taxRate }}%):</span>
                                    <span class="font-bold text-[#1F1F39]">₹{{ number_format($totals['tax_amount'], 2) }}</span>
                                </div>
                            @endif

                            <div class="pt-3 border-t border-[#EFF0F6] flex items-center justify-between text-sm">
                                <span class="font-bold text-[#1F1F39]">Total Amount:</span>
                                <span class="font-black text-lg text-[#FF006B]" x-text="'₹' + (orderType === 'pickup' ? (parseFloat(totalAmount) - parseFloat(deliveryFee)).toFixed(2) : parseFloat(totalAmount).toFixed(2))">₹{{ number_format($totals['total_amount'], 2) }}</span>
                            </div>
                        </div>

                        <!-- Place Order CTA Button -->
                        <div class="p-4 bg-[#F7F7FC] border-t border-[#EFF0F6]">
                            <button type="submit" 
                                    :disabled="(orderType === 'delivery' && !serviceable) || isSubmitting"
                                    :class="((orderType === 'delivery' && !serviceable) || isSubmitting) ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-[#FF006B] hover:bg-[#E60060] active:scale-95 shadow-lg shadow-pink-500/25'"
                                    class="w-full h-12 text-center capitalize font-bold rounded-full text-white transition-all flex items-center justify-center gap-2">
                                <template x-if="isSubmitting">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </template>
                                <span x-text="submitBtnText">Confirm & Place Order</span>
                                <template x-if="!isSubmitting">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </template>
                            </button>
                            <p x-show="orderType === 'delivery' && !serviceable" class="text-[10px] text-center text-rose-500 font-bold mt-2">
                                Please select Takeaway pickup or change your delivery address.
                            </p>
                            <p class="text-[10px] text-center text-[#6E7191] mt-2">Safe & Encrypted Checkout</p>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endsection
