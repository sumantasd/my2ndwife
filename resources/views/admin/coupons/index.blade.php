@extends('layouts.admin')

@section('title', 'Promo Coupons')
@section('header_title', 'Coupons & Discounts')

@section('content')
<div class="space-y-6">

    <!-- Actions Bar -->
    <div class="bg-white p-5 rounded-3xl border border-[#F0E6EC] shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.coupons.index') }}" method="GET" class="w-full sm:w-80 relative">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search promo code..."
                   class="w-full text-xs pl-9 pr-4 py-2.5 rounded-full border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none uppercase font-mono">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
        </form>

        <a href="{{ route('admin.coupons.create') }}" 
           class="px-5 py-2.5 rounded-full bg-[#F50070] hover:bg-[#D80063] text-white text-xs font-bold flex items-center gap-2 shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Create Promo Coupon</span>
        </a>
    </div>

    <!-- Coupons Table -->
    <div class="bg-white rounded-3xl border border-[#F0E6EC] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px] bg-gray-50/50">
                        <th class="py-3.5 px-6">Coupon Code</th>
                        <th class="py-3.5 px-4">Discount</th>
                        <th class="py-3.5 px-4">Min. Order</th>
                        <th class="py-3.5 px-4">Usages</th>
                        <th class="py-3.5 px-4">Valid Until</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($coupons as $coupon)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="py-3.5 px-6">
                            <span class="font-mono font-black text-sm text-[#F50070] bg-[#FFF3F8] px-2.5 py-1 rounded-lg border border-[#F0E6EC]">
                                {{ $coupon->code }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-[#25232A]">
                            @if($coupon->type === 'percentage')
                                {{ number_format($coupon->discount_amount, 0) }}% OFF
                                @if($coupon->max_discount_amount)
                                    <span class="text-[10px] text-gray-400 block font-normal">(Max ₹{{ number_format($coupon->max_discount_amount, 0) }})</span>
                                @endif
                            @else
                                ₹{{ number_format($coupon->discount_amount, 2) }} FLAT
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-gray-700">
                            ₹{{ number_format($coupon->min_order_amount, 2) }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-[#25232A]">
                            {{ $coupon->usages_count }} / {{ $coupon->usage_limit ?? '∞' }}
                        </td>
                        <td class="py-3.5 px-4 text-gray-500">
                            {{ $coupon->expires_at ? $coupon->expires_at->format('M d, Y') : 'Never Expires' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase border {{ $coupon->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500' }}">
                                {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-6 text-right space-x-2">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" 
                               class="inline-block p-2 rounded-xl text-gray-500 hover:text-[#F50070] hover:bg-[#FFF3F8] transition-colors">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>

                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('Delete coupon {{ $coupon->code }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-gray-400">No promo coupons created yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($coupons->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $coupons->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
