@extends('layouts.admin')

@section('title', 'Add Category')
@section('header_title', 'Create Category')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.categories.index') }}" class="text-xs font-bold text-gray-500 hover:text-[#F50070]">
            ← Back to Categories
        </a>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-[#25232A] mb-1">Category Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="E.g. Sizzling Tandoori Kebabs"
                       class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-[#25232A] mb-1">URL Slug (Leave blank to generate automatically)</label>
                <input type="text" name="slug" value="{{ old('slug') }}" placeholder="sizzling-tandoori-kebabs"
                       class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-[#25232A] mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="Brief description of dishes in this category..."
                          class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block font-bold text-[#25232A] mb-1">Category Image / Icon</label>
                <input type="file" name="image" accept="image/*"
                       class="w-full p-2 rounded-xl border border-[#F0E6EC] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#FFF3F8] file:text-[#F50070] hover:file:bg-pink-100">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Display Order</label>
                    <input type="number" name="display_order" value="{{ old('display_order', 0) }}" min="0"
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_active" value="1" id="is_active" checked class="rounded text-[#F50070]">
                    <label for="is_active" class="font-bold text-[#25232A]">Active & Visible</label>
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_featured" value="1" id="is_featured" class="rounded text-[#F50070]">
                    <label for="is_featured" class="font-bold text-[#25232A]">⭐ Featured Category</label>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.categories.index') }}" class="px-5 py-2.5 rounded-full text-gray-500 font-bold">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] hover:bg-[#D80063] text-white font-bold shadow-md">
                    Create Category
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
