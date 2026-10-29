@extends('layouts.admin')

@section('title', 'Homepage Banners')
@section('header_title', 'Homepage Banners')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ session('active_tab', 'hero') }}',
    addModalOpen: false,
    editModalOpen: false,
    
    // Add banner state for Hero
    newBanner: {
        name: '',
        alt_text: 'My 2nd Wife Food Delivery promotional banner',
        button_text: 'Order Now',
        button_url: '/menu',
        display_order: {{ ($heroBanners->max('display_order') ?? 0) + 1 }},
        is_active: true,
        preview_image: '{{ asset('images/banners/banner-biryani.svg') }}',
        imgWidth: 0,
        imgHeight: 0,
        imgRatio: 0,
        warningMsg: '',
        successMsg: ''
    },

    // Edit banner state for Hero
    editData: {
        id: null,
        name: '',
        alt_text: '',
        button_text: 'Order Now',
        button_url: '/menu',
        display_order: 0,
        is_active: true,
        image_url: '',
        update_url: '',
        preview_image: '',
        imgWidth: 0,
        imgHeight: 0,
        imgRatio: 0,
        warningMsg: '',
        successMsg: ''
    },

    // Offer 1 Live Preview State
    offer1: {
        title: '{{ addslashes($offer1->title ?? '') }}',
        badge_text: '{{ addslashes($offer1->badge_text ?? '') }}',
        subtitle: '{{ addslashes($offer1->subtitle ?? '') }}',
        button_text: '{{ addslashes($offer1->button_text ?? 'Order Now') }}',
        button_url: '{{ addslashes($offer1->button_url ?? '/menu') }}',
        bg_gradient: '{{ $offer1->bg_gradient ?? 'mint' }}',
        image_preview: '{{ $offer1->image_url }}',
        is_active: {{ ($offer1->is_active ?? true) ? 'true' : 'false' }}
    },

    // Offer 2 Live Preview State
    offer2: {
        title: '{{ addslashes($offer2->title ?? '') }}',
        badge_text: '{{ addslashes($offer2->badge_text ?? '') }}',
        subtitle: '{{ addslashes($offer2->subtitle ?? '') }}',
        button_text: '{{ addslashes($offer2->button_text ?? 'Order Now') }}',
        button_url: '{{ addslashes($offer2->button_url ?? '/menu') }}',
        bg_gradient: '{{ $offer2->bg_gradient ?? 'peach' }}',
        image_preview: '{{ $offer2->image_url }}',
        is_active: {{ ($offer2->is_active ?? true) ? 'true' : 'false' }}
    },

    handleFileSelect(event, targetObj) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                targetObj.preview_image = e.target.result;
                
                const img = new Image();
                img.onload = () => {
                    targetObj.imgWidth = img.naturalWidth;
                    targetObj.imgHeight = img.naturalHeight;
                    const ratio = (img.naturalWidth / img.naturalHeight).toFixed(2);
                    targetObj.imgRatio = ratio;
                    
                    const targetRatio = (20 / 7).toFixed(2); // ~2.86
                    if (img.naturalWidth < 1000) {
                        targetObj.warningMsg = `⚠️ Image resolution (${img.naturalWidth} × ${img.naturalHeight} px) is lower than recommended (2000 × 700 px). It may appear blurry on large desktop monitors.`;
                        targetObj.successMsg = '';
                    } else if (Math.abs(ratio - targetRatio) > 0.35) {
                        targetObj.warningMsg = `⚠️ Uploaded aspect ratio is ${ratio}:1, differing from the 20:7 ratio (2.86:1). While contained without distortion, horizontal or vertical letterboxing will appear.`;
                        targetObj.successMsg = '';
                    } else {
                        targetObj.warningMsg = '';
                        targetObj.successMsg = `✓ Excellent dimensions (${img.naturalWidth} × ${img.naturalHeight} px). Aspect ratio ${ratio}:1 fits the 20:7 container cleanly!`;
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    },

    handleOfferPreview(event, offerKey) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                if (offerKey === 'offer1') {
                    this.offer1.image_preview = e.target.result;
                } else if (offerKey === 'offer2') {
                    this.offer2.image_preview = e.target.result;
                }
            };
            reader.readAsDataURL(file);
        }
    }
}">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-gray-900 font-rubik flex items-center gap-2.5">
                <span class="p-2 bg-pink-50 text-[#F50070] rounded-xl inline-flex items-center justify-center">
                    <i data-lucide="image" class="w-6 h-6"></i>
                </span>
                <span>Homepage Banners</span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                Manage the three independent promotional banner areas on the homepage: Main Hero Banner, Offer Banner 1, and Offer Banner 2.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" target="_blank" 
               class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs flex items-center gap-1.5 transition-colors shadow-xs">
                <i data-lucide="external-link" class="w-4 h-4 text-gray-400"></i>
                <span>View Storefront</span>
            </a>
            <button type="button" @click="addModalOpen = true; activeTab = 'hero';"
                    class="px-5 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white font-bold text-xs flex items-center gap-1.5 transition-colors shadow-md shadow-pink-500/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Add Hero Banner</span>
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
        <div class="font-bold flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
            <span>Please correct the errors below:</span>
        </div>
        <ul class="list-disc list-inside pl-5 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Three Main Banner Tabs -->
    <div class="border-b border-gray-200 bg-white rounded-2xl p-2 shadow-xs">
        <nav class="flex flex-wrap gap-2" aria-label="Banner Sections">
            <!-- Tab 1: Hero Banner -->
            <button type="button" @click="activeTab = 'hero'"
                    :class="activeTab === 'hero' ? 'bg-[#F50070] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <i data-lucide="layout" class="w-4 h-4"></i>
                <span>1. Main Hero Banner</span>
                <span class="px-2 py-0.5 rounded-full text-[10px]"
                      :class="activeTab === 'hero' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600'">
                    {{ $heroBanners->count() }}
                </span>
            </button>

            <!-- Tab 2: Offer Banner 1 -->
            <button type="button" @click="activeTab = 'offer1'"
                    :class="activeTab === 'offer1' ? 'bg-[#F50070] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <i data-lucide="tag" class="w-4 h-4"></i>
                <span>2. Offer Banner 1 (Left Promo)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px]"
                      :class="activeTab === 'offer1' ? 'bg-white/20 text-white' : (offer1.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500')">
                    {{ ($offer1->is_active ?? true) ? 'Active' : 'Disabled' }}
                </span>
            </button>

            <!-- Tab 3: Offer Banner 2 -->
            <button type="button" @click="activeTab = 'offer2'"
                    :class="activeTab === 'offer2' ? 'bg-[#F50070] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <i data-lucide="tag" class="w-4 h-4"></i>
                <span>3. Offer Banner 2 (Right Promo)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px]"
                      :class="activeTab === 'offer2' ? 'bg-white/20 text-white' : (offer2.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500')">
                    {{ ($offer2->is_active ?? true) ? 'Active' : 'Disabled' }}
                </span>
            </button>
        </nav>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 1: MAIN HERO BANNER MANAGEMENT                                    -->
    <!-- ===================================================================== -->
    <div x-show="activeTab === 'hero'" class="space-y-6">

        <!-- Live Customer Simulation Preview (Hero) -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-gray-100 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-pink-50 text-[#F50070]">
                        <i data-lucide="monitor" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-black text-gray-900 font-rubik">Storefront Hero Simulation (20:7 Ratio)</h2>
                        <p class="text-[11px] text-gray-500">Live preview of how the primary hero banner appears on desktop and mobile.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-gray-400">Desktop & Mobile Container:</span>
                    <span class="text-[10px] font-mono font-bold bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md">20:7 Ratio</span>
                </div>
            </div>

            <!-- Customer Hero Banner Responsive Preview Box -->
            <div class="w-full max-w-4xl mx-auto rounded-2xl sm:rounded-3xl overflow-hidden border border-gray-200 bg-white shadow-xs">
                @if($heroBanners->where('is_active', true)->count() > 0)
                    @php $firstHero = $heroBanners->where('is_active', true)->first(); @endphp
                    <div class="homepage-banner w-full relative bg-white" style="aspect-ratio: 20 / 7;">
                        <img src="{{ $firstHero->image_url }}" alt="{{ $firstHero->alt_text ?: $firstHero->title }}" 
                             class="block w-full h-full object-contain object-center"
                             onerror="this.src='{{ asset('images/defaults/banner-placeholder.jpg') }}'">
                    </div>
                @else
                    <div class="homepage-banner w-full flex items-center justify-center bg-gray-50 text-gray-400 text-xs p-8" style="aspect-ratio: 20 / 7;">
                        <div class="text-center space-y-1">
                            <i data-lucide="image-off" class="w-8 h-8 mx-auto text-gray-300"></i>
                            <p class="font-bold text-gray-600">No active hero banners enabled</p>
                            <p class="text-[11px]">Upload or enable a hero banner below to display on the storefront.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Resolution Guidance -->
            <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/80 flex items-start gap-3 text-xs text-amber-900">
                <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                <div class="space-y-0.5">
                    <p class="font-bold">Strict 20:7 Aspect Ratio Architecture</p>
                    <p class="text-[11px] text-amber-800 leading-relaxed">
                        Recommended resolution is <strong>2000 × 700 px</strong>. The system maintains an identical 20:7 ratio across desktop, tablet, and mobile, ensuring the full image is always visible without cropping or distortion.
                    </p>
                </div>
            </div>
        </div>

        <!-- Hero Banners Table -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-black text-gray-900 font-rubik flex items-center gap-2">
                        <span>Hero Banners</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-pink-50 text-[#F50070] font-bold">
                            {{ $heroBanners->count() }} Total
                        </span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">These banners rotate in the top carousel. Changing a hero image will never affect the offer banners.</p>
                </div>
                <button type="button" @click="addModalOpen = true"
                        class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold flex items-center gap-1.5 transition-colors self-start sm:self-auto">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Add Another Banner</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 font-bold border-b border-gray-100">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">Order</th>
                            <th class="py-3 px-4 w-44">Banner Image</th>
                            <th class="py-3 px-4">Title & Details</th>
                            <th class="py-3 px-4">Destination Link</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @forelse($heroBanners as $banner)
                        <tr class="hover:bg-gray-50/60 transition-colors {{ !$banner->is_active ? 'opacity-60 bg-gray-50/30' : '' }}">
                            <!-- Order -->
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-700 font-bold text-xs">
                                    {{ $banner->display_order }}
                                </span>
                            </td>

                            <!-- 20:7 Thumbnail -->
                            <td class="py-4 px-4">
                                <div class="w-40 rounded-xl overflow-hidden bg-white border border-gray-200 shadow-xs relative" 
                                     style="aspect-ratio: 20 / 7;">
                                    <img src="{{ $banner->image_url }}" alt="{{ $banner->alt_text ?: $banner->title }}" 
                                         class="w-full h-full object-contain object-center"
                                         onerror="this.src='{{ asset('images/defaults/banner-placeholder.jpg') }}'">
                                </div>
                            </td>

                            <!-- Title & Metadata -->
                            <td class="py-4 px-4 space-y-1">
                                <p class="font-bold text-gray-900 text-xs">{{ $banner->title ?: $banner->name }}</p>
                                @if($banner->alt_text)
                                    <p class="text-[11px] text-gray-500 flex items-center gap-1">
                                        <span class="text-gray-400 font-bold">Alt:</span>
                                        <span class="truncate max-w-xs">{{ $banner->alt_text }}</span>
                                    </p>
                                @endif
                                <p class="text-[10px] text-gray-400">Updated: {{ $banner->updated_at?->diffForHumans() ?? 'N/A' }}</p>
                            </td>

                            <!-- Destination Link -->
                            <td class="py-4 px-4">
                                @if($banner->button_url)
                                    <a href="{{ $banner->button_url }}" target="_blank" 
                                       class="inline-flex items-center gap-1 font-mono text-[11px] text-pink-600 bg-pink-50 px-2 py-1 rounded-lg hover:underline max-w-[180px] truncate">
                                        <span>{{ $banner->button_url }}</span>
                                        <i data-lucide="external-link" class="w-3 h-3 shrink-0"></i>
                                    </a>
                                @else
                                    <span class="text-[11px] text-gray-400 italic">None (Display only)</span>
                                @endif
                            </td>

                            <!-- Active Toggle -->
                            <td class="py-4 px-4 text-center">
                                <form action="{{ route('admin.hero-banners.toggle', $banner->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold transition-colors cursor-pointer {{ $banner->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $banner->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        <span>{{ $banner->is_active ? 'Active' : 'Disabled' }}</span>
                                    </button>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Edit Trigger Modal -->
                                    <button type="button" 
                                            @click="editData = {
                                                id: {{ $banner->id }},
                                                name: '{{ addslashes($banner->name ?: $banner->title) }}',
                                                alt_text: '{{ addslashes($banner->alt_text ?? '') }}',
                                                button_text: '{{ addslashes($banner->button_text ?? 'Order Now') }}',
                                                button_url: '{{ addslashes($banner->button_url ?? '') }}',
                                                display_order: {{ $banner->display_order ?? 0 }},
                                                is_active: {{ $banner->is_active ? 'true' : 'false' }},
                                                image_url: '{{ $banner->image_url }}',
                                                update_url: '{{ route('admin.hero-banners.update', $banner->id) }}',
                                                preview_image: '',
                                                imgWidth: 0,
                                                imgHeight: 0,
                                                imgRatio: 0,
                                                warningMsg: '',
                                                successMsg: ''
                                            }; editModalOpen = true;"
                                            class="p-2 text-gray-600 hover:text-[#F50070] rounded-xl hover:bg-pink-50 transition-colors"
                                            title="Quick Edit">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Standalone Edit Page -->
                                    <a href="{{ route('admin.hero-banners.edit', $banner->id) }}" 
                                       class="p-2 text-gray-400 hover:text-gray-700 rounded-xl hover:bg-gray-100 transition-colors"
                                       title="Detailed Edit">
                                        <i data-lucide="maximize-2" class="w-4 h-4"></i>
                                    </a>

                                    <!-- Delete Banner -->
                                    <form action="{{ route('admin.hero-banners.destroy', $banner->id) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this hero banner? The image file will be removed safely.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition-colors"
                                                title="Delete Banner">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                <i data-lucide="image" class="w-10 h-10 mx-auto text-gray-300 mb-2"></i>
                                <p class="font-bold text-gray-700 text-sm">No hero banners uploaded yet</p>
                                <p class="text-xs text-gray-400 mt-0.5">Click "Add Hero Banner" above to upload your first 20:7 promotional banner.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 2: OFFER BANNER 1 (LEFT PROMOTIONAL CARD)                         -->
    <!-- ===================================================================== -->
    <div x-show="activeTab === 'offer1'" class="space-y-6" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left Col: Live Preview matching FoodKing/Customer design -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                        <h3 class="text-xs font-black text-gray-900 font-rubik flex items-center gap-1.5">
                            <i data-lucide="eye" class="w-4 h-4 text-[#FF006B]"></i>
                            <span>Storefront Live Card Preview</span>
                        </h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                              :class="offer1.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500'"
                              x-text="offer1.is_active ? 'Active' : 'Disabled'"></span>
                    </div>

                    <!-- Customer Preview Card matching customer/home.blade.php -->
                    <div class="rounded-2xl p-4 sm:p-5 border flex items-center justify-between gap-4 shadow-xs relative overflow-hidden transition-all"
                         :class="{
                            'bg-[#E8F8F2] border-[#D5EFE5]': offer1.bg_gradient === 'mint',
                            'bg-[#FFF6E9] border-[#FBEAD2]': offer1.bg_gradient === 'peach',
                            'bg-[#FFF3F8] border-[#FFE4F0]': offer1.bg_gradient === 'rose',
                            'bg-[#F3E8FF] border-[#E9D5FF]': offer1.bg_gradient === 'lavender'
                         }">
                        <div class="space-y-1.5 z-10 max-w-[65%]">
                            <span class="text-xs font-bold block"
                                  :class="{
                                    'text-[#FF006B]': offer1.bg_gradient === 'mint',
                                    'text-[#FF8A26]': offer1.bg_gradient === 'peach',
                                    'text-[#F50070]': offer1.bg_gradient === 'rose',
                                    'text-[#9333EA]': offer1.bg_gradient === 'lavender'
                                  }"
                                  x-text="offer1.badge_text || 'Badge Text'"></span>
                            <h4 class="text-xs sm:text-sm font-bold text-[#1F1F39] leading-tight"
                                x-text="offer1.title || 'Offer Banner 1 Title'"></h4>
                            <p class="text-[11px] sm:text-xs text-gray-600 line-clamp-1"
                               x-text="offer1.subtitle || 'Short descriptive offer subtitle'"></p>
                            <div class="pt-2">
                                <span class="inline-block px-3.5 py-1 rounded-full border border-[#1F1F39] text-[#1F1F39] font-bold text-[11px]"
                                      x-text="offer1.button_text || 'Order Now'"></span>
                            </div>
                        </div>

                        <!-- Card Image -->
                        <div class="w-24 h-20 sm:w-28 sm:h-24 shrink-0 rounded-xl overflow-hidden bg-white/40 flex items-center justify-center p-1 border border-white/60">
                            <img :src="offer1.image_preview" alt="Offer 1 Preview" 
                                 class="w-full h-full object-contain"
                                 onerror="this.src='{{ asset('images/defaults/banner-placeholder.jpg') }}'">
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-2xl text-[11px] text-gray-500 space-y-1">
                        <p class="font-bold text-gray-700">Display Location:</p>
                        <p>Below the <strong>Our Menu</strong> section, on the <strong>left</strong> side.</p>
                        <p class="text-gray-400">Recommended image: Square or transparent PNG/SVG (~300 × 300 px).</p>
                    </div>
                </div>
            </div>

            <!-- Right Col: Form for Offer Banner 1 -->
            <div class="lg:col-span-7">
                <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="text-sm font-black text-gray-900 font-rubik flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span>Edit Offer Banner 1 (Left Promo)</span>
                            </h3>
                            <p class="text-xs text-gray-500">Updating this banner will NOT affect the Hero Banner or Offer Banner 2.</p>
                        </div>

                        <!-- One-click Toggle -->
                        <form action="{{ route('admin.banners.offer1.toggle') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="px-3 py-1 rounded-full text-xs font-bold border transition-colors cursor-pointer {{ ($offer1->is_active ?? true) ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' }}">
                                {{ ($offer1->is_active ?? true) ? 'Active' : 'Disabled' }}
                            </button>
                        </form>
                    </div>

                    <form action="{{ route('admin.banners.offer1.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        
                        <!-- Dedicated Image Upload Field: offer_banner_1_image -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Offer Banner 1 Image <span class="text-gray-400 font-normal">(Leave blank to keep current image)</span>
                            </label>
                            <input type="file" name="offer_banner_1_image" accept="image/jpeg,image/png,image/webp,image/svg+xml,image/jpg"
                                   @change="handleOfferPreview($event, 'offer1')"
                                   class="w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-pink-50 file:text-[#F50070] hover:file:bg-pink-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 mt-1">Current file: <code class="text-gray-600 bg-gray-50 px-1 py-0.5 rounded">{{ $offer1->image_path ?? 'None' }}</code></p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Badge / Small Heading -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Small Heading / Badge Text</label>
                                <input type="text" name="badge_text" x-model="offer1.badge_text" placeholder="e.g. Uplifting anytime"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                            </div>

                            <!-- Main Title -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Main Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="title" x-model="offer1.title" required placeholder="e.g. Flat 20% off on Dum Biryani"
                                       class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                            </div>

                            <!-- Description / Subtitle -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Description / Subtitle</label>
                                <input type="text" name="subtitle" x-model="offer1.subtitle" placeholder="e.g. Slow dum-cooked with royal saffron & spices"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                            </div>

                            <!-- Button Text -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Button Text</label>
                                <input type="text" name="button_text" x-model="offer1.button_text" placeholder="Order Now"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                            </div>

                            <!-- Button Destination URL -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Button Destination URL</label>
                                <input type="text" name="button_url" x-model="offer1.button_url" placeholder="/menu?search=biryani"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                            </div>

                            <!-- Background Theme -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Background Pastel Theme</label>
                                <select name="bg_gradient" x-model="offer1.bg_gradient"
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden bg-white">
                                    <option value="mint">Mint Green (#E8F8F2)</option>
                                    <option value="peach">Warm Peach (#FFF6E9)</option>
                                    <option value="rose">Soft Rose (#FFF3F8)</option>
                                    <option value="lavender">Gentle Lavender (#F3E8FF)</option>
                                </select>
                            </div>

                            <!-- Alt Text -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Image Alt Text (SEO)</label>
                                <input type="text" name="alt_text" value="{{ $offer1->alt_text ?? '' }}" placeholder="Dum Biryani Promo"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                            </div>
                        </div>

                        <!-- Active Toggle Checkbox -->
                        <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                            <input type="checkbox" id="offer1_is_active" name="is_active" value="1" x-model="offer1.is_active"
                                   class="w-4 h-4 rounded text-[#F50070] focus:ring-[#F50070] border-gray-300">
                            <label for="offer1_is_active" class="text-xs font-bold text-gray-700 cursor-pointer">
                                Offer Banner 1 is Active and Visible to Customers
                            </label>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <button type="submit" 
                                    class="px-6 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 cursor-pointer">
                                Save Offer Banner 1
                            </button>
                        </div>
                    </form>

                    <!-- Reset / Delete Offer 1 -->
                    @if($offer1)
                    <div class="pt-3 border-t border-gray-100 flex justify-end">
                        <form action="{{ route('admin.banners.offer1.destroy') }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to remove Offer Banner 1? The image file will be cleaned up safely.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-bold flex items-center gap-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                <span>Reset / Delete Offer Banner 1</span>
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 3: OFFER BANNER 2 (RIGHT PROMOTIONAL CARD)                        -->
    <!-- ===================================================================== -->
    <div x-show="activeTab === 'offer2'" class="space-y-6" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left Col: Live Preview matching FoodKing/Customer design -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                        <h3 class="text-xs font-black text-gray-900 font-rubik flex items-center gap-1.5">
                            <i data-lucide="eye" class="w-4 h-4 text-[#FF006B]"></i>
                            <span>Storefront Live Card Preview</span>
                        </h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                              :class="offer2.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500'"
                              x-text="offer2.is_active ? 'Active' : 'Disabled'"></span>
                    </div>

                    <!-- Customer Preview Card matching customer/home.blade.php -->
                    <div class="rounded-2xl p-4 sm:p-5 border flex items-center justify-between gap-4 shadow-xs relative overflow-hidden transition-all"
                         :class="{
                            'bg-[#E8F8F2] border-[#D5EFE5]': offer2.bg_gradient === 'mint',
                            'bg-[#FFF6E9] border-[#FBEAD2]': offer2.bg_gradient === 'peach',
                            'bg-[#FFF3F8] border-[#FFE4F0]': offer2.bg_gradient === 'rose',
                            'bg-[#F3E8FF] border-[#E9D5FF]': offer2.bg_gradient === 'lavender'
                         }">
                        <div class="space-y-1.5 z-10 max-w-[65%]">
                            <span class="text-xs font-bold block"
                                  :class="{
                                    'text-[#FF006B]': offer2.bg_gradient === 'mint',
                                    'text-[#FF8A26]': offer2.bg_gradient === 'peach',
                                    'text-[#F50070]': offer2.bg_gradient === 'rose',
                                    'text-[#9333EA]': offer2.bg_gradient === 'lavender'
                                  }"
                                  x-text="offer2.badge_text || 'Badge Text'"></span>
                            <h4 class="text-xs sm:text-sm font-bold text-[#1F1F39] leading-tight"
                                x-text="offer2.title || 'Offer Banner 2 Title'"></h4>
                            <p class="text-[11px] sm:text-xs text-gray-600 line-clamp-1"
                               x-text="offer2.subtitle || 'Short descriptive offer subtitle'"></p>
                            <div class="pt-2">
                                <span class="inline-block px-3.5 py-1 rounded-full border border-[#1F1F39] text-[#1F1F39] font-bold text-[11px]"
                                      x-text="offer2.button_text || 'Order Now'"></span>
                            </div>
                        </div>

                        <!-- Card Image -->
                        <div class="w-24 h-20 sm:w-28 sm:h-24 shrink-0 rounded-xl overflow-hidden bg-white/40 flex items-center justify-center p-1 border border-white/60">
                            <img :src="offer2.image_preview" alt="Offer 2 Preview" 
                                 class="w-full h-full object-contain"
                                 onerror="this.src='{{ asset('images/defaults/banner-placeholder.jpg') }}'">
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-2xl text-[11px] text-gray-500 space-y-1">
                        <p class="font-bold text-gray-700">Display Location:</p>
                        <p>Below the <strong>Our Menu</strong> section, on the <strong>right</strong> side.</p>
                        <p class="text-gray-400">Recommended image: Square or transparent PNG/SVG (~300 × 300 px).</p>
                    </div>
                </div>
            </div>

            <!-- Right Col: Form for Offer Banner 2 -->
            <div class="lg:col-span-7">
                <div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="text-sm font-black text-gray-900 font-rubik flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span>Edit Offer Banner 2 (Right Promo)</span>
                            </h3>
                            <p class="text-xs text-gray-500">Updating this banner will NOT affect the Hero Banner or Offer Banner 1.</p>
                        </div>

                        <!-- One-click Toggle -->
                        <form action="{{ route('admin.banners.offer2.toggle') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="px-3 py-1 rounded-full text-xs font-bold border transition-colors cursor-pointer {{ ($offer2->is_active ?? true) ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' }}">
                                {{ ($offer2->is_active ?? true) ? 'Active' : 'Disabled' }}
                            </button>
                        </form>
                    </div>

                    <form action="{{ route('admin.banners.offer2.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        
                        <!-- Dedicated Image Upload Field: offer_banner_2_image -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Offer Banner 2 Image <span class="text-gray-400 font-normal">(Leave blank to keep current image)</span>
                            </label>
                            <input type="file" name="offer_banner_2_image" accept="image/jpeg,image/png,image/webp,image/svg+xml,image/jpg"
                                   @change="handleOfferPreview($event, 'offer2')"
                                   class="w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-pink-50 file:text-[#F50070] hover:file:bg-pink-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 mt-1">Current file: <code class="text-gray-600 bg-gray-50 px-1 py-0.5 rounded">{{ $offer2->image_path ?? 'None' }}</code></p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Badge / Small Heading -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Small Heading / Badge Text</label>
                                <input type="text" name="badge_text" x-model="offer2.badge_text" placeholder="e.g. Savory and Satisfying"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                            </div>

                            <!-- Main Title -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Main Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="title" x-model="offer2.title" required placeholder="e.g. Flat 15% off on Kolkata Rolls"
                                       class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                            </div>

                            <!-- Description / Subtitle -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Description / Subtitle</label>
                                <input type="text" name="subtitle" x-model="offer2.subtitle" placeholder="e.g. Crispy parathas with spiced chicken & chutney"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                            </div>

                            <!-- Button Text -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Button Text</label>
                                <input type="text" name="button_text" x-model="offer2.button_text" placeholder="Order Now"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                            </div>

                            <!-- Button Destination URL -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Button Destination URL</label>
                                <input type="text" name="button_url" x-model="offer2.button_url" placeholder="/menu?search=roll"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                            </div>

                            <!-- Background Theme -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Background Pastel Theme</label>
                                <select name="bg_gradient" x-model="offer2.bg_gradient"
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden bg-white">
                                    <option value="peach">Warm Peach (#FFF6E9)</option>
                                    <option value="mint">Mint Green (#E8F8F2)</option>
                                    <option value="rose">Soft Rose (#FFF3F8)</option>
                                    <option value="lavender">Gentle Lavender (#F3E8FF)</option>
                                </select>
                            </div>

                            <!-- Alt Text -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Image Alt Text (SEO)</label>
                                <input type="text" name="alt_text" value="{{ $offer2->alt_text ?? '' }}" placeholder="Kolkata Rolls Promo"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                            </div>
                        </div>

                        <!-- Active Toggle Checkbox -->
                        <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                            <input type="checkbox" id="offer2_is_active" name="is_active" value="1" x-model="offer2.is_active"
                                   class="w-4 h-4 rounded text-[#F50070] focus:ring-[#F50070] border-gray-300">
                            <label for="offer2_is_active" class="text-xs font-bold text-gray-700 cursor-pointer">
                                Offer Banner 2 is Active and Visible to Customers
                            </label>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <button type="submit" 
                                    class="px-6 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 cursor-pointer">
                                Save Offer Banner 2
                            </button>
                        </div>
                    </form>

                    <!-- Reset / Delete Offer 2 -->
                    @if($offer2)
                    <div class="pt-3 border-t border-gray-100 flex justify-end">
                        <form action="{{ route('admin.banners.offer2.destroy') }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to remove Offer Banner 2? The image file will be cleaned up safely.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-bold flex items-center gap-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                <span>Reset / Delete Offer Banner 2</span>
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL: ADD HERO BANNER                                                -->
    <!-- ===================================================================== -->
    <div x-show="addModalOpen" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="addModalOpen = false" 
             class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            
            <div class="p-5 sm:p-6 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 bg-pink-50 text-[#F50070] rounded-xl">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-black text-gray-900 font-rubik">Add New Hero Banner</h2>
                        <p class="text-xs text-gray-500">Upload a single 20:7 promotional banner for desktop and mobile.</p>
                    </div>
                </div>
                <button type="button" @click="addModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.hero-banners.store') }}" method="POST" enctype="multipart/form-data" class="p-5 sm:p-6 space-y-4">
                @csrf
                
                <!-- 20:7 Live Preview -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                            <i data-lucide="eye" class="w-4 h-4 text-[#FF006B]"></i>
                            <span>Live 20:7 Banner Ratio Preview</span>
                        </label>
                        <span class="text-[11px] text-gray-400" x-show="newBanner.imgWidth > 0" 
                              x-text="`Uploaded: ${newBanner.imgWidth} × ${newBanner.imgHeight} px`"></span>
                    </div>

                    <div class="homepage-banner w-full relative rounded-2xl overflow-hidden bg-gray-50 border border-gray-200 shadow-xs"
                         style="aspect-ratio: 20 / 7;">
                        <img :src="newBanner.preview_image" alt="Preview" 
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

                <!-- Inputs -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Banner Title / Admin Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="newBanner.name" required placeholder="e.g. Royal Biryani Festival"
                               class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Banner Image File <span class="text-red-500">*</span> (Recommended: 2000 × 700 px)
                        </label>
                        <input type="file" name="hero_banner_image" accept="image/jpeg,image/png,image/webp,image/jpg,image/svg+xml" required
                               @change="handleFileSelect($event, newBanner)"
                               class="w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Alt Text (Accessibility & SEO)</label>
                        <input type="text" name="alt_text" x-model="newBanner.alt_text" placeholder="My 2nd Wife Flat 20% off on Royal Dum Biryani"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Destination URL (Optional)</label>
                        <input type="text" name="button_url" x-model="newBanner.button_url" placeholder="/menu?search=biryani"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Display Order Position</label>
                        <input type="number" name="display_order" x-model="newBanner.display_order" min="0"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                    <input type="checkbox" id="new_banner_active" name="is_active" value="1" x-model="newBanner.is_active"
                           class="w-4 h-4 rounded text-[#F50070] focus:ring-[#F50070] border-gray-300">
                    <label for="new_banner_active" class="text-xs font-bold text-gray-700">
                        Banner is active and visible on the storefront
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="addModalOpen = false" 
                            class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20">
                        Save Hero Banner
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL: EDIT HERO BANNER                                               -->
    <!-- ===================================================================== -->
    <div x-show="editModalOpen" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="editModalOpen = false" 
             class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            
            <div class="p-5 sm:p-6 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 bg-pink-50 text-[#F50070] rounded-xl">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-black text-gray-900 font-rubik">Edit & Replace Hero Banner</h2>
                        <p class="text-xs text-gray-500">Update destination link, order, or upload a replacement 20:7 image.</p>
                    </div>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="editData.update_url" method="POST" enctype="multipart/form-data" class="p-5 sm:p-6 space-y-4">
                @csrf
                @method('PUT')
                
                <!-- 20:7 Live Preview -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                            <i data-lucide="eye" class="w-4 h-4 text-[#FF006B]"></i>
                            <span>Current / Replacement Banner (20:7 Ratio)</span>
                        </label>
                        <span class="text-[11px] text-gray-400" x-show="editData.imgWidth > 0" 
                              x-text="`New Image: ${editData.imgWidth} × ${editData.imgHeight} px`"></span>
                    </div>

                    <div class="homepage-banner w-full relative rounded-2xl overflow-hidden bg-gray-50 border border-gray-200 shadow-xs"
                         style="aspect-ratio: 20 / 7;">
                        <img :src="editData.preview_image || editData.image_url" alt="Banner Preview" 
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

                <!-- Form Inputs -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Banner Title / Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="editData.name" required
                               class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Replace Image File (Optional, ~2000 × 700 px)
                        </label>
                        <input type="file" name="hero_banner_image" accept="image/jpeg,image/png,image/webp,image/jpg,image/svg+xml"
                               @change="handleFileSelect($event, editData)"
                               class="w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                        <span class="text-[10px] text-gray-400">Leave blank to keep the current image intact</span>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Alt Text (Accessibility & SEO)</label>
                        <input type="text" name="alt_text" x-model="editData.alt_text"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Destination URL (Optional)</label>
                        <input type="text" name="button_url" x-model="editData.button_url" placeholder="/menu"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Display Order Position</label>
                        <input type="number" name="display_order" x-model="editData.display_order" min="0"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-[#F50070] outline-hidden">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                    <input type="checkbox" id="edit_banner_active" name="is_active" value="1" x-model="editData.is_active"
                           class="w-4 h-4 rounded text-[#F50070] focus:ring-[#F50070] border-gray-300">
                    <label for="edit_banner_active" class="text-xs font-bold text-gray-700">
                        Banner is active and visible on the storefront
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false" 
                            class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
