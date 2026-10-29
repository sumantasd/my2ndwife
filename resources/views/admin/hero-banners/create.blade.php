@extends('layouts.admin')

@section('title', 'Upload Homepage Banner')
@section('header_title', 'Upload Homepage Banner')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    newBanner: {
        name: '',
        alt_text: 'My 2nd Wife Food Delivery promotional banner',
        button_text: 'Order Now',
        button_url: '/menu',
        display_order: {{ $nextOrder ?? 1 }},
        is_active: true,
        preview_image: '{{ asset('images/banners/banner-biryani.svg') }}',
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
                this.newBanner.preview_image = e.target.result;
                const img = new Image();
                img.onload = () => {
                    this.newBanner.imgWidth = img.naturalWidth;
                    this.newBanner.imgHeight = img.naturalHeight;
                    const ratio = (img.naturalWidth / img.naturalHeight).toFixed(2);
                    this.newBanner.imgRatio = ratio;
                    const targetRatio = (20 / 7).toFixed(2);
                    const diff = Math.abs(ratio - targetRatio);
                    if (img.naturalWidth < 1000) {
                        this.newBanner.warningMsg = `Resolution is small (${img.naturalWidth}×${img.naturalHeight}px). For crisp display, 2000 × 700 px is recommended.`;
                        this.newBanner.successMsg = '';
                    } else if (diff > 0.35) {
                        this.newBanner.warningMsg = `Aspect ratio is ${ratio}:1 (${img.naturalWidth}×${img.naturalHeight}px). Standard banner ratio is 20:7 (~2.86:1, e.g. 2000×700px). Image will be centered proportionally without cropping.`;
                        this.newBanner.successMsg = '';
                    } else {
                        this.newBanner.warningMsg = '';
                        this.newBanner.successMsg = `Optimal 20:7 proportions verified (${img.naturalWidth}×${img.naturalHeight}px) ✓`;
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
                <span>Live Preview (Strict 20:7 Aspect Ratio)</span>
            </label>
            <span class="text-xs text-gray-400" x-show="newBanner.imgWidth > 0" 
                  x-text="`Detected: ${newBanner.imgWidth} × ${newBanner.imgHeight} px`"></span>
        </div>

        <div class="homepage-banner w-full relative rounded-2xl overflow-hidden bg-white shadow-xs border-2 border-dashed border-gray-300 flex items-center justify-center"
             style="aspect-ratio: 20 / 7;">
            <img :src="newBanner.preview_image" alt="Preview Banner" 
                 class="block w-full h-full object-contain object-center"
                 onerror="this.src='{{ asset('images/defaults/banner-placeholder.jpg') }}'">
        </div>

        <div x-show="newBanner.warningMsg" class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0"></i>
            <span x-text="newBanner.warningMsg"></span>
        </div>
        <div x-show="newBanner.successMsg" class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span x-text="newBanner.successMsg"></span>
        </div>
    </div>

    <!-- Banner Form Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 sm:p-8">
        <form action="{{ route('admin.hero-banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Title / Name -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Banner Title / Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="newBanner.name" required placeholder="e.g. Weekend Biryani Feast"
                           class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                </div>

                <!-- Banner Image Upload -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Banner Image File <span class="text-red-500">*</span> (Recommended: 2000 × 700 px)
                    </label>
                    <input type="file" name="image" required accept="image/jpeg,image/png,image/webp,image/jpg"
                           @change="handleFileSelect($event)"
                           class="w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-pink-50 file:text-[#FF006B] hover:file:bg-pink-100 cursor-pointer">
                    <span class="text-[10px] text-gray-400">JPEG, JPG, PNG or WebP up to 5MB. Identical 20:7 ratio on desktop and mobile.</span>
                </div>

                <!-- Alt Text -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Alt Text (Accessibility & SEO)</label>
                    <input type="text" name="alt_text" x-model="newBanner.alt_text" placeholder="e.g. Delicious Kolkata Biryani special offer"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                </div>

                <!-- Destination URL -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Destination URL (Optional)</label>
                    <input type="text" name="button_url" x-model="newBanner.button_url" placeholder="/menu"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                </div>

                <!-- Display Order -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Display Order Position</label>
                    <input type="number" name="display_order" x-model="newBanner.display_order" min="0"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                </div>
            </div>

            <!-- Active Checkbox -->
            <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                <input type="checkbox" id="page_add_is_active" name="is_active" value="1" x-model="newBanner.is_active"
                       class="w-4 h-4 rounded text-[#F50070] focus:ring-[#F50070] border-gray-300">
                <label for="page_add_is_active" class="text-xs font-bold text-gray-700">
                    Enable this banner immediately on the customer homepage
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
                    Upload & Publish Banner
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
