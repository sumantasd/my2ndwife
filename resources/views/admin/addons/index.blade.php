@extends('layouts.admin')

@section('title', 'Add-ons & Extras')
@section('header_title', 'Add-ons & Enhancements')

@section('content')
<div class="space-y-8" x-data="{ newGroupModal: false }">

    <!-- Header Actions -->
    <div class="bg-white p-6 rounded-3xl border border-[#F0E6EC] shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold text-[#25232A]">Custom Food Enhancements</h2>
            <p class="text-xs text-gray-500">Configure dips, crust upgrades, extra cheese, and beverage pairings that customers can select.</p>
        </div>
        <button @click="newGroupModal = true" 
                class="px-5 py-2.5 rounded-full bg-[#F50070] hover:bg-[#D80063] text-white text-xs font-bold flex items-center gap-2 shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Create Add-on Group</span>
        </button>
    </div>

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($groups as $group)
        <div class="bg-white rounded-3xl border border-[#F0E6EC] shadow-sm p-6 flex flex-col justify-between space-y-4">
            
            <!-- Group Header -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-pink-100 text-[#F50070]">
                        {{ $group->is_required ? 'Required' : 'Optional' }}
                    </span>
                    <form action="{{ route('admin.addons.groups.destroy', $group) }}" method="POST" onsubmit="return confirm('Delete this entire addon group?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-gray-400 hover:text-rose-600 p-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </form>
                </div>
                <h3 class="text-base font-extrabold text-[#25232A]">{{ $group->name }}</h3>
                <p class="text-[11px] text-gray-400">Min: {{ $group->min_selection }} • Max: {{ $group->max_selection ?? 'Unlimited' }}</p>
            </div>

            <!-- Addon Items List -->
            <div class="space-y-2 border-t border-gray-100 pt-3">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Configured Extras ({{ $group->addons->count() }})</p>
                <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1 text-xs">
                    @foreach($group->addons as $addon)
                    <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50 border border-gray-100">
                        <span class="font-bold text-[#25232A]">{{ $addon->name }}</span>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-[#F50070]">₹{{ number_format($addon->price, 2) }}</span>
                            <form action="{{ route('admin.addons.items.destroy', $addon) }}" method="POST" onsubmit="return confirm('Remove {{ $addon->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-rose-600"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Add Addon Form -->
            <form action="{{ route('admin.addons.items.store', $group) }}" method="POST" class="pt-3 border-t border-gray-100 space-y-2">
                @csrf
                <div class="flex items-center gap-2">
                    <input type="text" name="name" placeholder="Extra item name" required class="flex-1 text-xs p-2 rounded-xl border border-[#F0E6EC] focus:outline-none focus:border-[#F50070]">
                    <input type="number" step="0.01" name="price" placeholder="₹ Price" required class="w-20 text-xs p-2 rounded-xl border border-[#F0E6EC] focus:outline-none focus:border-[#F50070]">
                    <button type="submit" class="p-2 rounded-xl bg-[#25232A] text-white hover:bg-[#F50070] transition-colors"><i data-lucide="plus" class="w-4 h-4"></i></button>
                </div>
            </form>
        </div>
        @empty
        <div class="col-span-full bg-white p-12 rounded-3xl border border-[#F0E6EC] text-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-[#FFF3F8] text-[#F50070] flex items-center justify-center mx-auto text-xl">
                ➕
            </div>
            <h3 class="text-base font-bold text-[#25232A]">No Add-on Groups Configured</h3>
            <p class="text-xs text-gray-500">Create add-on groups like Extra Cheese, Sauces, and Drink combos.</p>
            <button @click="newGroupModal = true" class="px-5 py-2.5 rounded-full bg-[#F50070] text-white text-xs font-bold hover:bg-[#D80063]">
                Create First Add-on Group
            </button>
        </div>
        @endforelse
    </div>

    <!-- Create Group Modal -->
    <div x-show="newGroupModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.outside="newGroupModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#F0E6EC] space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-black text-[#25232A]">Create Add-on Group</h3>
                <button @click="newGroupModal = false" class="text-gray-400 hover:text-gray-700"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form action="{{ route('admin.addons.groups.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Group Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="E.g. Extra Cheese & Dips" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Min Selection</label>
                        <input type="number" name="min_selection" value="0" min="0" required class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                    </div>
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Max Selection (0 = Unlimited)</label>
                        <input type="number" name="max_selection" value="3" min="0" class="w-full p-2.5 rounded-xl border border-[#F0E6EC]">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_required" value="1" id="is_req" class="rounded text-[#F50070]">
                    <label for="is_req" class="font-bold text-[#25232A]">Customer must select at least one item (Required)</label>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="newGroupModal = false" class="px-4 py-2 font-bold text-gray-500">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063]">Save Group</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
