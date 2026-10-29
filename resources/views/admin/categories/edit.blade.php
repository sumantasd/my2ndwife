@extends('layouts.admin')

@section('title', 'Edit Category: ' . $category->name)
@section('header_title', 'Edit Category')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.categories.index') }}" class="text-xs font-bold text-gray-500 hover:text-[#F50070]">
            ← Back to Categories
        </a>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="space-y-5 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-[#25232A] mb-1">Category Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                       class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-[#25232A] mb-1">URL Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $category->slug) }}"
                       class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-[#25232A] mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#FFF3F8] p-1 border border-[#F0E6EC] shrink-0">
                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-full h-full object-contain">
                </div>
                <div class="flex-1">
                    <label class="block font-bold text-[#25232A] mb-1">Replace Image</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full p-2 rounded-xl border border-[#F0E6EC] file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#FFF3F8] file:text-[#F50070]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Display Order</label>
                    <input type="number" name="display_order" value="{{ old('display_order', $category->display_order) }}" min="0"
                           class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ $category->is_active ? 'checked' : '' }} class="rounded text-[#F50070]">
                    <label for="is_active" class="font-bold text-[#25232A]">Active & Visible</label>
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_featured" value="1" id="is_featured" {{ $category->is_featured ? 'checked' : '' }} class="rounded text-[#F50070]">
                    <label for="is_featured" class="font-bold text-[#25232A]">⭐ Featured Category</label>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.categories.index') }}" class="px-5 py-2.5 rounded-full text-gray-500 font-bold">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-full bg-[#F50070] hover:bg-[#D80063] text-white font-bold shadow-md">
                    Update Category
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
