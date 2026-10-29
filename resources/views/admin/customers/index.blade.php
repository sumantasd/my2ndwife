@extends('layouts.admin')

@section('title', 'Customer Management')
@section('header_title', 'Registered Customers')

@section('content')
<div class="space-y-6">

    <!-- Search Bar -->
    <div class="bg-white p-5 rounded-3xl border border-[#F0E6EC] shadow-sm flex items-center justify-between">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="w-full sm:w-80 relative">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search customer name, email, phone..."
                   class="w-full text-xs pl-9 pr-4 py-2.5 rounded-full border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-3xl border border-[#F0E6EC] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px] bg-gray-50/50">
                        <th class="py-3.5 px-6">Customer</th>
                        <th class="py-3.5 px-4">Contact</th>
                        <th class="py-3.5 px-4">Total Orders</th>
                        <th class="py-3.5 px-4">Total Spent</th>
                        <th class="py-3.5 px-4">Account Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="py-3.5 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#FFF3F8] text-[#F50070] font-bold flex items-center justify-center border border-[#F0E6EC]">
                                    {{ substr($customer->name, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-[#25232A]">{{ $customer->name }}</h4>
                                    <span class="text-gray-400 text-[11px]">Joined {{ $customer->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-[#25232A] block">{{ $customer->phone }}</span>
                            <span class="text-gray-400 text-[11px]">{{ $customer->email }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-extrabold text-[#25232A]">
                            {{ $customer->orders_count }} orders
                        </td>
                        <td class="py-3.5 px-4 font-black text-[#F50070]">
                            ₹{{ number_format($customer->orders_sum_total_amount ?? 0, 2) }}
                        </td>
                        <td class="py-3.5 px-4">
                            <form action="{{ route('admin.customers.toggle', $customer) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase border {{ $customer->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-rose-50 hover:text-rose-700' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-emerald-50 hover:text-emerald-700' }}">
                                    {{ $customer->is_active ? 'Active' : 'Suspended' }}
                                </button>
                            </form>
                        </td>
                        <td class="py-3.5 px-6 text-right">
                            <a href="{{ route('admin.customers.show', $customer) }}" 
                               class="px-3.5 py-1.5 rounded-xl bg-gray-100 text-[#25232A] font-bold text-xs hover:bg-gray-200 transition-colors">
                                View Profile
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-400">No customers registered yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
