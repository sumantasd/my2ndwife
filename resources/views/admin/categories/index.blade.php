@extends('layouts.admin')

@section('title', 'Food Categories')
@section('header_title', 'Category Management')

@section('content')
<div class="space-y-6">

    <!-- Actions Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.categories.index') }}" method="GET" class="w-full sm:w-80 relative">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search categories..."
                   class="w-full text-xs pl-9 pr-4 py-2.5 rounded-full border border-[#F0E6EC] focus:outline-none focus:border-[#F50070]">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
        </form>

        <a href="{{ route('admin.categories.create') }}" 
           class="px-5 py-2.5 rounded-full bg-[#F50070] hover:bg-[#D80063] text-white text-xs font-bold flex items-center gap-2 shadow-sm transition-all self-end sm:self-auto">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add Category</span>
        </a>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-3xl border border-[#F0E6EC] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px] bg-gray-50/50">
                        <th class="py-3.5 px-6">Image</th>
                        <th class="py-3.5 px-4">Name & Slug</th>
                        <th class="py-3.5 px-4">Dishes Count</th>
                        <th class="py-3.5 px-4">Order</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($categories as $category)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="py-3.5 px-6">
                            <div class="w-12 h-12 rounded-xl bg-[#FFF3F8] overflow-hidden border border-[#F0E6EC] p-1 flex items-center justify-center">
                                <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-full h-full object-contain">
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <h4 class="font-extrabold text-[#25232A] text-sm">{{ $category->name }}</h4>
                            <span class="text-gray-400 font-mono text-[11px]">{{ $category->slug }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-[#25232A]">
                            <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs">
                                {{ $category->products_count }} items
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-gray-500 font-mono">{{ $category->display_order }}</td>
                        <td class="py-3.5 px-4 space-y-1">
                            <div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase {{ $category->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            @if($category->is_featured)
                                <div>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                        ⭐ Featured
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 text-right space-x-2">
                            <a href="{{ route('admin.categories.edit', $category) }}" 
                               class="inline-block p-2 rounded-xl text-gray-500 hover:text-[#F50070] hover:bg-[#FFF3F8] transition-colors">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>

                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('Are you sure you want to delete category \'{{ $category->name }}\'?');">
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
                        <td colspan="6" class="text-center py-10 text-gray-400">No categories found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
