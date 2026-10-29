@extends('layouts.app')

@section('title', 'Saved Addresses | My 2nd Wife')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" x-data="{ showAddModal: false }">

    <div class="flex items-center justify-between border-b border-[#EFF0F6] pb-5">
        <div>
            <h1 class="text-2xl font-bold text-[#1F1F39] font-rubik">Saved Addresses</h1>
            <p class="text-xs text-[#6E7191] mt-1">Manage addresses for fast doorstep delivery</p>
        </div>
        <button @click="showAddModal = true" 
                class="px-4 py-2.5 rounded-full bg-primary hover:bg-[#E0005E] text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add New Address</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Sidebar Navigation -->
        <div class="space-y-1 bg-white p-3 rounded-2xl border border-[#EFF0F6] shadow-sm self-start">
            <a href="{{ route('account.profile') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium text-[#6E7191] hover:text-[#1F1F39] hover:bg-[#F7F7FC] transition-colors">
                <i data-lucide="user" class="w-4 h-4 text-[#6E7191]"></i> Profile Details
            </a>
            <a href="{{ route('account.addresses') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-[#FFF0F6] text-primary">
                <i data-lucide="map-pin" class="w-4 h-4 text-primary"></i> Saved Addresses
            </a>
            <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium text-[#6E7191] hover:text-[#1F1F39] hover:bg-[#F7F7FC] transition-colors">
                <i data-lucide="package" class="w-4 h-4 text-[#6E7191]"></i> Order History
            </a>
        </div>

        <!-- Address List -->
        <div class="md:col-span-3 space-y-4">
            @forelse($addresses as $addr)
            <div class="bg-white p-5 rounded-2xl border border-[#EFF0F6] shadow-sm flex items-start justify-between gap-4 hover:border-pink-200 transition-colors">
                <div class="space-y-1.5 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-[#F7F7FC] text-[#1F1F39] border border-[#EFF0F6]">
                            {{ $addr->type }}
                        </span>
                        @if($addr->is_default)
                            <span class="text-[10px] font-bold text-primary bg-[#FFF0F6] px-2 py-0.5 rounded-full border border-pink-200">
                                DEFAULT
                            </span>
                        @endif
                    </div>
                    <p class="font-bold text-[#1F1F39] text-sm font-rubik">{{ $addr->contact_name }} • {{ $addr->contact_phone }}</p>
                    <p class="text-[#6E7191] leading-relaxed">{{ $addr->full_address }}</p>
                    @if($addr->delivery_instructions)
                        <p class="text-[11px] text-amber-700 italic bg-amber-50 p-2 rounded-xl mt-1">
                            Note: {{ $addr->delivery_instructions }}
                        </p>
                    @endif
                </div>

                <form action="{{ route('account.addresses.destroy', $addr) }}" method="POST" onsubmit="return confirm('Remove this saved address?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-xl text-[#6E7191] hover:text-[#FB4E4E] hover:bg-rose-50 transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
            @empty
            <div class="bg-white p-10 rounded-2xl border border-[#EFF0F6] text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-[#F7F7FC] text-primary flex items-center justify-center mx-auto text-xl border border-[#EFF0F6]">
                    📍
                </div>
                <h3 class="text-base font-bold text-[#1F1F39] font-rubik">No Saved Addresses</h3>
                <p class="text-xs text-[#6E7191]">Add your home or office address to enable instant one-click checkout.</p>
                <button @click="showAddModal = true" class="px-5 py-2.5 rounded-full bg-primary text-white text-xs font-bold hover:bg-[#E0005E] transition-colors">
                    Add Delivery Address
                </button>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Modal to Add New Address -->
    <div x-show="showAddModal" x-cloak style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.outside="showAddModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-[#EFF0F6] space-y-5">
            <div class="flex items-center justify-between border-b border-[#EFF0F6] pb-3">
                <h3 class="text-base font-bold text-[#1F1F39] font-rubik">Add New Address</h3>
                <button @click="showAddModal = false" class="text-[#6E7191] hover:text-[#1F1F39]"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('account.addresses.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-3 gap-2">
                    <label class="p-2.5 rounded-xl border border-[#EFF0F6] text-center cursor-pointer font-bold bg-[#F7F7FC] hover:bg-pink-50 transition-colors">
                        <input type="radio" name="type" value="home" checked class="accent-primary mr-1">
                        <span>🏠 Home</span>
                    </label>
                    <label class="p-2.5 rounded-xl border border-[#EFF0F6] text-center cursor-pointer font-bold bg-[#F7F7FC] hover:bg-pink-50 transition-colors">
                        <input type="radio" name="type" value="work" class="accent-primary mr-1">
                        <span>🏢 Work</span>
                    </label>
                    <label class="p-2.5 rounded-xl border border-[#EFF0F6] text-center cursor-pointer font-bold bg-[#F7F7FC] hover:bg-pink-50 transition-colors">
                        <input type="radio" name="type" value="other" class="accent-primary mr-1">
                        <span>📍 Other</span>
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#1F1F39] mb-1">Contact Name</label>
                        <input type="text" name="contact_name" value="{{ auth()->user()->name }}" required class="w-full p-2.5 rounded-xl border border-[#EFF0F6] focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-[#1F1F39] mb-1">Contact Phone</label>
                        <input type="tel" name="contact_phone" value="{{ auth()->user()->phone }}" required class="w-full p-2.5 rounded-xl border border-[#EFF0F6] focus:border-primary focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#1F1F39] mb-1">Address Line 1</label>
                    <input type="text" name="address_line_1" required placeholder="Flat, Building name" class="w-full p-2.5 rounded-xl border border-[#EFF0F6] focus:border-primary focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#1F1F39] mb-1">City</label>
                        <input type="text" name="city" value="Kolkata" required class="w-full p-2.5 rounded-xl border border-[#EFF0F6] focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-[#1F1F39] mb-1">Postal Code</label>
                        <input type="text" name="postal_code" required placeholder="700016" class="w-full p-2.5 rounded-xl border border-[#EFF0F6] focus:border-primary focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#1F1F39] mb-1">Delivery Instructions</label>
                    <input type="text" name="delivery_instructions" placeholder="E.g. Call upon arrival" class="w-full p-2.5 rounded-xl border border-[#EFF0F6] focus:border-primary focus:outline-none">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_default" value="1" id="is_def" class="rounded accent-primary">
                    <label for="is_def" class="text-xs font-medium text-[#1F1F39]">Set as default address</label>
                </div>

                <div class="pt-3 border-t border-[#EFF0F6] flex justify-end gap-3">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 text-[#6E7191] font-bold hover:text-[#1F1F39]">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-primary text-white font-bold hover:bg-[#E0005E] transition-colors">Save Address</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
