@extends('layouts.admin')

@section('title', 'Create Coupon')
@section('header_title', 'New Promo Coupon')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.coupons.index') }}" class="text-xs font-bold text-gray-500 hover:text-[#F50070]">
            ← Back to Coupons
        </a>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Coupon Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="FEAST25"
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] uppercase font-mono font-bold focus:border-[#F50070] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Discount Type <span class="text-rose-500">*</span></label>
                    <select name="type" required class="w-full p-3 rounded-xl border border-[#F0E6EC] bg-white">
                        <option value="percentage">Percentage Discount (%)</option>
                        <option value="fixed">Fixed Cash Discount (₹)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Discount Value <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="discount_amount" value="{{ old('discount_amount') }}" required placeholder="20"
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Min Order Amount (₹)</label>
                    <input type="number" step="0.01" name="min_order_amount" value="{{ old('min_order_amount', 199.00) }}" required
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Max Cap (₹, for %)</label>
                    <input type="number" step="0.01" name="max_discount_amount" value="{{ old('max_discount_amount') }}" placeholder="100.00"
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Total Usage Limit (Blank = Unlimited)</label>
                    <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" placeholder="500"
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Per-Customer Limit <span class="text-rose-500">*</span></label>
                    <input type="number" name="per_customer_limit" value="{{ old('per_customer_limit', 1) }}" min="1" required
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}"
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Expiry Date</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" value="1" id="is_act" checked class="rounded text-[#F50070]">
                <label for="is_act" class="font-bold text-[#25232A]">Active Immediately</label>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.coupons.index') }}" class="px-5 py-2.5 rounded-full text-gray-500 font-bold">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] hover:bg-[#D80063] text-white font-bold shadow-md">
                    Create Coupon
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
