@extends('layouts.admin')

@section('title', 'Customer: ' . $customer->name)
@section('header_title', 'Customer Profile: ' . $customer->name)

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.customers.index') }}" class="text-xs font-bold text-gray-500 hover:text-[#F50070]">
            ← Back to Customers
        </a>
    </div>

    <!-- Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Customer Details Card -->
        <div class="bg-white p-6 rounded-3xl border border-[#F0E6EC] shadow-sm space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-[#FFF3F8] text-[#F50070] font-black text-lg flex items-center justify-center border border-[#F0E6EC]">
                    {{ substr($customer->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="font-extrabold text-[#25232A] text-base">{{ $customer->name }}</h3>
                    <span class="text-xs text-gray-400">Registered: {{ $customer->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            <div class="space-y-2 text-xs border-t border-gray-100 pt-3">
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase font-bold">Email Address</span>
                    <span class="font-bold text-[#25232A]">{{ $customer->email }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase font-bold">Phone Number</span>
                    <span class="font-bold text-[#25232A]">{{ $customer->phone }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase font-bold">Total Spent</span>
                    <span class="font-black text-base text-[#F50070]">₹{{ number_format($customer->orders()->sum('total_amount'), 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Saved Addresses -->
        <div class="md:col-span-2 bg-white p-6 rounded-3xl border border-[#F0E6EC] shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#25232A]">Saved Delivery Addresses ({{ $customer->addresses->count() }})</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                @forelse($customer->addresses as $addr)
                <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 space-y-1">
                    <span class="text-[9px] uppercase font-bold px-2 py-0.5 rounded-full bg-white border border-gray-200">{{ $addr->type }}</span>
                    <p class="font-bold text-[#25232A]">{{ $addr->contact_name }} ({{ $addr->contact_phone }})</p>
                    <p class="text-gray-500 leading-relaxed">{{ $addr->full_address }}</p>
                </div>
                @empty
                <p class="text-gray-400 text-xs py-4 col-span-2">No saved addresses on file.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Order History Table -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-[#25232A]">Order History ({{ $orders->total() }})</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-2">Order #</th>
                        <th class="py-3 px-2">Date</th>
                        <th class="py-3 px-2">Items</th>
                        <th class="py-3 px-2">Status</th>
                        <th class="py-3 px-2">Total</th>
                        <th class="py-3 px-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-2 font-mono font-bold text-[#25232A]">#{{ $order->order_number }}</td>
                        <td class="py-3 px-2 text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                        <td class="py-3 px-2 text-gray-700">{{ $order->items->count() }} items</td>
                        <td class="py-3 px-2">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $order->status_badge_color }}">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-2 font-black text-[#25232A]">₹{{ number_format($order->total_amount, 2) }}</td>
                        <td class="py-3 px-2 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-[#F50070] font-bold hover:underline">
                                Manage
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-6 text-gray-400">No orders placed by this customer.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="pt-4 border-t border-gray-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
