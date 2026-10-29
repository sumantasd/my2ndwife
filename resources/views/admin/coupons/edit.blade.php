@extends('layouts.admin')

@section('title', 'Edit Coupon: ' . $coupon->code)
@section('header_title', 'Edit Coupon')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.coupons.index') }}" class="text-xs font-bold text-gray-500 hover:text-[#F50070]">
            ← Back to Coupons
        </a>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Coupon Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] uppercase font-mono font-bold focus:border-[#F50070] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Discount Type <span class="text-rose-500">*</span></label>
                    <select name="type" required class="w-full p-3 rounded-xl border border-[#F0E6EC] bg-white">
                        <option value="percentage" {{ $coupon->type === 'percentage' ? 'selected' : '' }}>Percentage Discount (%)</option>
                        <option value="fixed" {{ $coupon->type === 'fixed' ? 'selected' : '' }}>Fixed Cash Discount (₹)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Discount Value <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="discount_amount" value="{{ old('discount_amount', $coupon->discount_amount) }}" required
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Min Order Amount (₹)</label>
                    <input type="number" step="0.01" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" required
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Max Cap (₹, for %)</label>
                    <input type="number" step="0.01" name="max_discount_amount" value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}"
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Total Usage Limit</label>
                    <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}"
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Per-Customer Limit</label>
                    <input type="number" name="per_customer_limit" value="{{ old('per_customer_limit', $coupon->per_customer_limit) }}" min="1" required
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $coupon->start_date?->format('Y-m-d')) }}"
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Expiry Date</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d')) }}"
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" value="1" id="is_act" {{ $coupon->is_active ? 'checked' : '' }} class="rounded text-[#F50070]">
                <label for="is_act" class="font-bold text-[#25232A]">Active</label>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.coupons.index') }}" class="px-5 py-2.5 rounded-full text-gray-500 font-bold">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] hover:bg-[#D80063] text-white font-bold shadow-md">
                    Update Coupon
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
