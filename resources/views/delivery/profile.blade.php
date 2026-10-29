@extends('layouts.delivery')

@section('title', 'My Rider Profile')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    <!-- Profile Overview Card -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E8E5EF] shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        @php
            $profileAvatar = (!empty($rider->avatar))
                ? (str_starts_with($rider->avatar, 'http') ? $rider->avatar : asset('storage/' . ltrim($rider->avatar, '/')))
                : null;
            $profileInitials = strtoupper(substr($rider->name ?? 'R', 0, 1));
        @endphp
        <div class="flex items-center gap-4">
            @if($profileAvatar)
                <img src="{{ $profileAvatar }}" alt="{{ $rider->name }}" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-md ring-2 ring-[#FF006E]/20 shrink-0">
            @else
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#FF006E] to-[#E60063] text-white font-black text-2xl flex items-center justify-center shadow-md border-2 border-white ring-2 ring-[#FF006E]/20 shrink-0">
                    {{ $profileInitials }}
                </div>
            @endif
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-black text-[#202033]">{{ $rider->name }}</h1>
                    @if($rider->is_online)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-900 border border-emerald-200">
                            Online
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-900 border border-amber-200">
                            Offline
                        </span>
                    @endif
                </div>
                <p class="text-xs text-[#55597D] font-mono">{{ $rider->email }} • {{ $rider->phone }}</p>
                <p class="text-[11px] text-[#FF006E] font-bold">
                    Vehicle: {{ $rider->vehicle_type ?? 'Motorcycle' }} ({{ $rider->vehicle_number ?? 'Reg Pending' }})
                </p>
            </div>
        </div>

        <div class="text-left sm:text-right w-full sm:w-auto p-3 sm:p-0 bg-gray-50 sm:bg-transparent rounded-2xl">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#667085] block">Completed Deliveries</span>
            <span class="text-2xl font-black text-[#00B884]">{{ $completedCount }}</span>
        </div>
    </div>

    <!-- Edit Profile & Vehicle Details -->
    <div class="bg-white rounded-3xl p-5 sm:p-8 border border-[#E8E5EF] shadow-xs space-y-6">
        <div>
            <h2 class="text-base font-extrabold text-[#202033]">Rider & Vehicle Information</h2>
            <p class="text-xs text-[#55597D]">Update your active contact phone and field vehicle details</p>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-bold space-y-1">
                @foreach($errors->all() as $error)
                    <div class="flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0 text-[#E5484D]"></i>
                        <span>{{ $error }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('delivery.profile.update') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <!-- Name (Readonly) -->
            <div>
                <label class="block font-bold text-[#55597D] mb-1">Full Name</label>
                <input type="text" value="{{ $rider->name }}" disabled class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-[#667085] font-bold">
            </div>

            <!-- Email (Readonly) -->
            <div>
                <label class="block font-bold text-[#55597D] mb-1">Email Address</label>
                <input type="email" value="{{ $rider->email }}" disabled class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-[#667085] font-mono">
            </div>

            <!-- Phone Number -->
            <div>
                <label for="phone" class="block font-bold text-[#202033] mb-1">Contact Phone Number <span class="text-[#E5484D]">*</span></label>
                <input type="tel" id="phone" name="phone" required value="{{ old('phone', $rider->phone) }}" class="w-full p-3 rounded-xl border border-[#E8E5EF] text-xs font-mono text-[#202033] focus:border-[#FF006E] focus:ring-2 focus:ring-[#FF006E]/20 focus:outline-none">
            </div>

            <!-- Vehicle Type & Registration -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="vehicle_type" class="block font-bold text-[#202033] mb-1">Vehicle Type</label>
                    <input type="text" id="vehicle_type" name="vehicle_type" value="{{ old('vehicle_type', $rider->vehicle_type) }}" placeholder="e.g. Motorcycle, Scooter, EV" class="w-full p-3 rounded-xl border border-[#E8E5EF] text-xs text-[#202033] focus:border-[#FF006E] focus:ring-2 focus:ring-[#FF006E]/20 focus:outline-none">
                </div>

                <div>
                    <label for="vehicle_number" class="block font-bold text-[#202033] mb-1">Vehicle Registration Number</label>
                    <input type="text" id="vehicle_number" name="vehicle_number" value="{{ old('vehicle_number', $rider->vehicle_number) }}" placeholder="e.g. WB-02-AB-1234" class="w-full p-3 rounded-xl border border-[#E8E5EF] text-xs uppercase font-mono text-[#202033] focus:border-[#FF006E] focus:ring-2 focus:ring-[#FF006E]/20 focus:outline-none">
                </div>
            </div>

            <!-- Change Password Section (Optional) -->
            <div class="pt-5 border-t border-[#E8E5EF] space-y-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-[#667085]">Security & Password (Optional)</h3>

                <div>
                    <label for="current_password" class="block font-bold text-[#202033] mb-1">Current Password</label>
                    <input type="password" id="current_password" name="current_password" placeholder="Enter current password to change" class="w-full p-3 rounded-xl border border-[#E8E5EF] text-xs text-[#202033] focus:border-[#FF006E] focus:ring-2 focus:ring-[#FF006E]/20 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="new_password" class="block font-bold text-[#202033] mb-1">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="At least 6 characters" class="w-full p-3 rounded-xl border border-[#E8E5EF] text-xs text-[#202033] focus:border-[#FF006E] focus:ring-2 focus:ring-[#FF006E]/20 focus:outline-none">
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block font-bold text-[#202033] mb-1">Confirm New Password</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" placeholder="Confirm new password" class="w-full p-3 rounded-xl border border-[#E8E5EF] text-xs text-[#202033] focus:border-[#FF006E] focus:ring-2 focus:ring-[#FF006E]/20 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-[#E8E5EF]">
                <form action="{{ route('delivery.duty.toggle') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-[#55597D] hover:text-[#202033] underline cursor-pointer">
                        Toggle Online/Offline Duty Status
                    </button>
                </form>

                <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-[#FF006E] to-[#E60063] hover:from-[#E60063] hover:to-[#CC0058] text-white font-black shadow-xs transition-all active:scale-[0.99] cursor-pointer">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
