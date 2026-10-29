@extends('layouts.admin')

@section('title', 'Delivery Management')
@section('header_title', 'Delivery Partners & Fleet')

@section('content')
<div class="space-y-6" x-data="{ 
    newRiderModal: false,
    editRiderModal: false,
    activeEditRider: {
        id: '',
        name: '',
        email: '',
        phone: '',
        vehicle_type: '',
        vehicle_number: '',
        is_active: 1
    },
    openEdit(rider) {
        this.activeEditRider = {
            id: rider.id,
            name: rider.name,
            email: rider.email,
            phone: rider.phone,
            vehicle_type: rider.vehicle_type || '',
            vehicle_number: rider.vehicle_number || '',
            is_active: rider.is_active ? 1 : 0
        };
        this.editRiderModal = true;
    }
}">

    <!-- Actions & Filter Bar -->
    <div class="bg-white p-6 rounded-3xl border border-[#F0E6EC] shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold text-[#25232A]">Delivery Fleet Management</h2>
            <p class="text-xs text-gray-500">Manage delivery partners, vehicle registration, and dispatch assignments.</p>
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto flex-wrap">
            <a href="{{ route('delivery.login') }}" target="_blank" 
               class="px-4 py-2.5 rounded-full bg-pink-50 hover:bg-pink-100 text-[#F50070] text-xs font-bold flex items-center gap-2 border border-pink-100 transition-all">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Open Rider Portal</span>
            </a>

            <button @click="newRiderModal = true" 
                    class="px-5 py-2.5 rounded-full bg-[#F50070] hover:bg-[#D80063] text-white text-xs font-bold flex items-center gap-2 shadow-sm transition-all cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Delivery Partner</span>
            </button>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 rounded-2xl border border-[#F0E6EC] shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.delivery.index') }}" method="GET" class="w-full flex flex-col sm:flex-row items-center gap-2">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search by partner name, phone, email, vehicle..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-[#F0E6EC] text-xs focus:border-[#F50070] focus:outline-none">
            </div>

            <select name="status" onchange="this.form.submit()" class="w-full sm:w-44 px-3 py-2 rounded-xl border border-[#F0E6EC] text-xs font-bold focus:border-[#F50070] focus:outline-none">
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Offline Only</option>
            </select>

            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-xs font-bold text-gray-700">
                Filter
            </button>
        </form>
    </div>

    <!-- Riders Table -->
    <div class="bg-white rounded-3xl border border-[#F0E6EC] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px] bg-gray-50/50">
                        <th class="py-3.5 px-6">Delivery Partner</th>
                        <th class="py-3.5 px-4">Contact Phone</th>
                        <th class="py-3.5 px-4">Vehicle Details</th>
                        <th class="py-3.5 px-4">Duty Status</th>
                        <th class="py-3.5 px-4">Active Deliveries</th>
                        <th class="py-3.5 px-4">Completed</th>
                        <th class="py-3.5 px-4">Account Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($riders as $rider)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="py-3.5 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-pink-100 text-[#F50070] font-bold flex items-center justify-center">
                                    {{ substr($rider->name, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-[#25232A]">{{ $rider->name }}</h4>
                                    <span class="text-gray-400 text-[11px] font-mono">{{ $rider->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-gray-700">{{ $rider->phone }}</td>
                        <td class="py-3.5 px-4">
                            @if($rider->vehicle_type || $rider->vehicle_number)
                                <div class="font-bold text-gray-700">{{ $rider->vehicle_type ?? 'Bike' }}</div>
                                <span class="text-[10px] font-mono text-gray-400 uppercase">{{ $rider->vehicle_number ?? 'Reg. Pending' }}</span>
                            @else
                                <span class="text-gray-400">Not recorded</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($rider->is_online)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Online</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>Offline</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($rider->active_deliveries_count > 0)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-pink-100 text-[#F50070] animate-pulse">
                                    {{ $rider->active_deliveries_count }} in transit
                                </span>
                            @else
                                <span class="text-gray-400">Idle / Ready</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-extrabold text-[#25232A]">
                            {{ $rider->completed_deliveries_count }} delivered
                        </td>
                        <td class="py-3.5 px-4">
                            <form action="{{ route('admin.delivery.toggle', $rider) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        title="Click to toggle active status"
                                        class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase border cursor-pointer {{ $rider->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-rose-50 hover:text-rose-700' : 'bg-gray-100 text-gray-500 hover:bg-emerald-50' }}">
                                    {{ $rider->is_active ? 'Active' : 'Deactivated' }}
                                </button>
                            </form>
                        </td>
                        <td class="py-3.5 px-6 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button type="button" 
                                        @click="openEdit({{ $rider->toJson() }})"
                                        class="text-xs font-bold text-gray-600 hover:text-[#F50070] transition-colors cursor-pointer">
                                    Edit Details
                                </button>
                                <span class="text-gray-200">|</span>
                                <a href="{{ route('admin.orders.index', ['search' => $rider->name]) }}" 
                                   class="text-xs font-bold text-[#F50070] hover:underline">
                                    Orders →
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-gray-400">No delivery partners found matching the search.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Rider Modal -->
    <div x-show="newRiderModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.outside="newRiderModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#F0E6EC] space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-black text-[#25232A]">Add Delivery Partner</h3>
                <button @click="newRiderModal = false" class="text-gray-400 hover:text-gray-700"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form action="{{ route('admin.delivery.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Rider Full Name" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" required placeholder="rider@my2ndwife.com" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Mobile Phone <span class="text-rose-500">*</span></label>
                    <input type="tel" name="phone" required placeholder="+91 98000 00000" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Vehicle Type</label>
                        <input type="text" name="vehicle_type" placeholder="e.g. Motorcycle" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                    </div>
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Vehicle Reg Number</label>
                        <input type="text" name="vehicle_number" placeholder="e.g. WB-02-1234" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Initial Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="newRiderModal = false" class="px-4 py-2 font-bold text-gray-500">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063]">Save Partner</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Rider Modal -->
    <div x-show="editRiderModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.outside="editRiderModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#F0E6EC] space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-black text-[#25232A]">Edit Delivery Partner</h3>
                <button @click="editRiderModal = false" class="text-gray-400 hover:text-gray-700"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form :action="'{{ url('admin/delivery') }}/' + activeEditRider.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="activeEditRider.name" required class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" x-model="activeEditRider.email" required class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Mobile Phone <span class="text-rose-500">*</span></label>
                    <input type="tel" name="phone" x-model="activeEditRider.phone" required class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Vehicle Type</label>
                        <input type="text" name="vehicle_type" x-model="activeEditRider.vehicle_type" placeholder="e.g. Motorcycle" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                    </div>
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Vehicle Reg Number</label>
                        <input type="text" name="vehicle_number" x-model="activeEditRider.vehicle_number" placeholder="e.g. WB-02-1234" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">New Password (Leave blank to keep current)</label>
                    <input type="password" name="password" placeholder="••••••••" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-[#25232A]">
                        <input type="checkbox" name="is_active" value="1" :checked="activeEditRider.is_active == 1" class="w-4 h-4 rounded text-[#F50070]">
                        <span>Active Account Status</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="editRiderModal = false" class="px-4 py-2 font-bold text-gray-500">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063]">Update Partner</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
