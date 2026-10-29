@extends('layouts.admin')

@section('title', 'Food Items')
@section('header_title', 'Menu Dishes Management')

@section('content')
<div class="space-y-6">

    <!-- Filters & Action Bar -->
    <div class="bg-white p-5 rounded-3xl border border-[#F0E6EC] shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        
        <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 w-full md:w-auto flex-1">
            <div class="relative flex-1 sm:w-64">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search food by name or SKU..."
                       class="w-full text-xs pl-9 pr-4 py-2.5 rounded-full border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
            </div>

            <select name="category_id" class="text-xs p-2.5 rounded-xl border border-[#F0E6EC] bg-white">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <select name="diet" class="text-xs p-2.5 rounded-xl border border-[#F0E6EC] bg-white">
                <option value="">All Diets</option>
                <option value="veg" {{ request('diet') === 'veg' ? 'selected' : '' }}>Veg Only</option>
                <option value="non-veg" {{ request('diet') === 'non-veg' ? 'selected' : '' }}>Non-Veg Only</option>
            </select>

            <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#25232A] text-white text-xs font-bold hover:bg-[#F50070] transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'category_id', 'diet']))
                <a href="{{ route('admin.products.index') }}" class="text-xs text-gray-500 hover:text-[#F50070] font-bold">Reset</a>
            @endif
        </form>

        <a href="{{ route('admin.products.create') }}" 
           class="px-5 py-2.5 rounded-full bg-[#F50070] hover:bg-[#D80063] text-white text-xs font-bold flex items-center gap-2 shadow-sm transition-all whitespace-nowrap">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add Food Item</span>
        </a>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-3xl border border-[#F0E6EC] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px] bg-gray-50/50">
                        <th class="py-3.5 px-6">Dish</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Price</th>
                        <th class="py-3.5 px-4">Diet</th>
                        <th class="py-3.5 px-4">Badges</th>
                        <th class="py-3.5 px-4">Availability</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="py-3.5 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-[#FFF3F8] overflow-hidden border border-[#F0E6EC] shrink-0">
                                    <img src="{{ $product->main_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-[#25232A] text-sm">{{ $product->name }}</h4>
                                    <span class="text-gray-400 font-mono text-[10px]">{{ $product->sku }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#FFF3F8] text-[#F50070] border border-[#F0E6EC]">
                                {{ $product->category->name }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-black text-[#25232A]">
                            <span>₹{{ number_format($product->current_price, 2) }}</span>
                            @if($product->has_discount)
                                <span class="text-[10px] text-gray-400 line-through block">₹{{ number_format($product->regular_price, 2) }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $product->is_veg ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $product->is_veg ? 'bg-emerald-600' : 'bg-rose-600' }}"></span>
                                {{ $product->is_veg ? 'Veg' : 'Non-Veg' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 space-x-1">
                            @if($product->is_featured) <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-pink-100 text-pink-800">Featured</span> @endif
                            @if($product->is_popular) <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">Popular</span> @endif
                            @if($product->is_best_seller) <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-800">Best Seller</span> @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <form action="{{ route('admin.products.toggle', $product) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase transition-all {{ $product->is_available ? 'bg-emerald-50 text-emerald-700 border border-emerald-300 hover:bg-rose-50 hover:text-rose-700' : 'bg-gray-100 text-gray-500 hover:bg-emerald-50 hover:text-emerald-700' }}">
                                    {{ $product->is_available ? 'In Stock' : 'Sold Out' }}
                                </button>
                            </form>
                        </td>
                        <td class="py-3.5 px-6 text-right space-x-2">
                            <a href="{{ route('admin.products.edit', $product) }}" 
                               class="inline-block p-2 rounded-xl text-gray-500 hover:text-[#F50070] hover:bg-[#FFF3F8] transition-colors">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>

                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('Are you sure you want to delete dish \'{{ $product->name }}\'?');">
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
                        <td colspan="7" class="text-center py-10 text-gray-400">No food items found matching your filters.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
