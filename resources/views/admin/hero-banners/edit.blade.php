@extends('layouts.admin')

@section('title', 'Edit Banner: ' . ($banner->name ?: $banner->title))
@section('header_title', 'Edit Homepage Banner')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    editData: {
        name: '{{ addslashes($banner->name ?: $banner->title) }}',
        alt_text: '{{ addslashes($banner->alt_text ?? '') }}',
        button_text: '{{ addslashes($banner->button_text ?? 'Order Now') }}',
        button_url: '{{ addslashes($banner->button_url ?? '') }}',
        display_order: {{ $banner->display_order ?? 0 }},
        is_active: {{ $banner->is_active ? 'true' : 'false' }},
        preview_image: '{{ $banner->image_url }}',
        imgWidth: 0,
        imgHeight: 0,
        imgRatio: 0,
        warningMsg: '',
        successMsg: ''
    },
    handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.editData.preview_image = e.target.result;
                const img = new Image();
                img.onload = () => {
                    this.editData.imgWidth = img.naturalWidth;
                    this.editData.imgHeight = img.naturalHeight;
                    const ratio = (img.naturalWidth / img.naturalHeight).toFixed(2);
                    this.editData.imgRatio = ratio;
                    const targetRatio = (20 / 7).toFixed(2);
                    const diff = Math.abs(ratio - targetRatio);
                    if (img.naturalWidth < 1000) {
                        this.editData.warningMsg = `Resolution is small (${img.naturalWidth}×${img.naturalHeight}px). For crisp display, 2000 × 700 px is recommended.`;
                        this.editData.successMsg = '';
                    } else if (diff > 0.35) {
                        this.editData.warningMsg = `Aspect ratio is ${ratio}:1 (${img.naturalWidth}×${img.naturalHeight}px). Standard banner ratio is 20:7 (~2.86:1, e.g. 2000×700px). Image will be centered proportionally without cropping.`;
                        this.editData.successMsg = '';
                    } else {
                        this.editData.warningMsg = '';
                        this.editData.successMsg = `Optimal 20:7 proportions verified (${img.naturalWidth}×${img.naturalHeight}px) ✓`;
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
}">

    <!-- Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.hero-banners.index') }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-gray-900 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to All Banners</span>
        </a>
    </div>

    <!-- Live 20:7 Preview Container -->
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <label class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                <i data-lucide="eye" class="w-4 h-4 text-[#FF006B]"></i>
                <span>Current / Replacement Banner (20:7 Responsive Ratio)</span>
            </label>
            <span class="text-xs text-gray-400" x-show="editData.imgWidth > 0" 
                  x-text="`New Image: ${editData.imgWidth} × ${editData.imgHeight} px`"></span>
        </div>

        <div class="homepage-banner w-full relative rounded-2xl overflow-hidden bg-white shadow-xs border border-gray-200"
             style="aspect-ratio: 20 / 7;">
            <img :src="editData.preview_image" alt="Preview Banner" 
                 class="block w-full h-full object-contain object-center"
                 onerror="this.src='{{ asset('images/defaults/banner-placeholder.jpg') }}'">
        </div>

        <div x-show="editData.warningMsg" class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0"></i>
            <span x-text="editData.warningMsg"></span>
        </div>
        <div x-show="editData.successMsg" class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span x-text="editData.successMsg"></span>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 sm:p-8">
        <form action="{{ route('admin.hero-banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Title / Name -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Banner Title / Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editData.name" required
                           class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                </div>

                <!-- Replace Image File -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Replace Banner Image File (Optional, ~2000 × 700 px)
                    </label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/jpg"
                           @change="handleFileSelect($event)"
                           class="w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                    <span class="text-[10px] text-gray-400">Leave empty to keep current image. Uploading replaces the file safely.</span>
                </div>

                <!-- Alt Text -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Alt Text (Accessibility & SEO)</label>
                    <input type="text" name="alt_text" x-model="editData.alt_text"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                </div>

                <!-- Destination URL -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Destination URL (Optional)</label>
                    <input type="text" name="button_url" x-model="editData.button_url" placeholder="/menu"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                </div>

                <!-- Display Order -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Display Order Position</label>
                    <input type="number" name="display_order" x-model="editData.display_order" min="0"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                </div>
            </div>

            <!-- Active Checkbox -->
            <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                <input type="checkbox" id="page_edit_is_active" name="is_active" value="1" x-model="editData.is_active"
                       class="w-4 h-4 rounded text-[#F50070] focus:ring-[#F50070] border-gray-300">
                <label for="page_edit_is_active" class="text-xs font-bold text-gray-700">
                    Banner is active and publicly visible
                </label>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.hero-banners.index') }}" 
                   class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
