@extends('layouts.admin')

@section('title', 'Add Food Item')
@section('header_title', 'Create Food Item')

@section('content')
<div class="max-w-4xl mx-auto space-y-6"
     x-data="{
         variations: [],
         addVariation() {
             this.variations.push({ name: '', price: '' });
         },
         removeVariation(index) {
             this.variations.splice(index, 1);
         }
     }">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-gray-500 hover:text-[#F50070]">
            ← Back to Food Items
        </a>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">
            @csrf

            <!-- Section 1: Basic Dish Details -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#25232A] border-b border-gray-100 pb-2">1. Dish Information</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Category <span class="text-rose-500">*</span></label>
                        <select name="category_id" required class="w-full p-3 rounded-xl border border-[#F0E6EC] bg-white">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Food Item Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="E.g. Awadhi Mutton Dum Biryani"
                               class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">SKU / Item Code</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" placeholder="M2W-BIRY-005"
                               class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none uppercase">
                    </div>

                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Cooking / Prep Time (Minutes) <span class="text-rose-500">*</span></label>
                        <input type="number" name="prep_time_minutes" value="{{ old('prep_time_minutes', 20) }}" min="1" required
                               class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-[#25232A] mb-1">Description & Ingredients</label>
                        <textarea name="description" rows="3" placeholder="Tell customers about the spices, ingredients, and taste..."
                                  class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Pricing & Dietary -->
            <div class="space-y-4 pt-4 border-t border-gray-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#25232A] border-b border-gray-100 pb-2">2. Pricing & Attributes</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Regular Price (₹) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" name="regular_price" value="{{ old('regular_price') }}" required placeholder="399.00"
                               class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-[#25232A] mb-1">Discounted Price (₹, Optional)</label>
                        <input type="number" step="0.01" name="discount_price" value="{{ old('discount_price') }}" placeholder="349.00"
                               class="w-full p-3 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                    </div>
                </div>

                <!-- Availability & Stock Status -->
                <div class="p-4 rounded-2xl border bg-emerald-50/60 border-emerald-200 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h4 class="font-bold text-[#25232A] text-xs">Food Dish Availability / Stock Status</h4>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            When enabled ("In Stock"), customers can immediately order this dish upon publishing.
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_available" value="0">
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <!-- Checkboxes -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                    <input type="hidden" name="is_veg" value="0">
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="is_veg" value="1" {{ old('is_veg') ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-600">
                        <span class="font-bold text-[#25232A]">Pure Veg</span>
                    </label>

                    <input type="hidden" name="is_featured" value="0">
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="rounded text-[#F50070] focus:ring-[#F50070]">
                        <span class="font-bold text-[#25232A]">Featured</span>
                    </label>

                    <input type="hidden" name="is_popular" value="0">
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="is_popular" value="1" {{ old('is_popular') ? 'checked' : '' }} class="rounded text-amber-500 focus:ring-amber-500">
                        <span class="font-bold text-[#25232A]">Popular</span>
                    </label>

                    <input type="hidden" name="is_best_seller" value="0">
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="is_best_seller" value="1" {{ old('is_best_seller') ? 'checked' : '' }} class="rounded text-purple-600 focus:ring-purple-600">
                        <span class="font-bold text-[#25232A]">Best Seller</span>
                    </label>
                </div>
            </div>

            <!-- Section 3: Media Upload -->
            <div class="space-y-4 pt-4 border-t border-gray-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#25232A] border-b border-gray-100 pb-2">3. Food Photography</h3>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Main Dish Image (JPEG, PNG, WebP, SVG)</label>
                    <input type="file" name="main_image" accept="image/*"
                           class="w-full p-2 rounded-xl border border-[#F0E6EC] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#FFF3F8] file:text-[#F50070] hover:file:bg-pink-100">
                </div>
            </div>

            <!-- Section 4: Portions / Variations Dynamic Repeater -->
            <div class="space-y-4 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#25232A]">4. Portion Sizes & Variations (Optional)</h3>
                        <p class="text-[11px] text-gray-400">E.g., Half, Full, 10 inch, 14 inch</p>
                    </div>
                    <button type="button" @click="addVariation()" 
                            class="px-3 py-1.5 rounded-full bg-gray-100 hover:bg-[#F50070] hover:text-white font-bold text-[11px] transition-colors flex items-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Add Portion Size</span>
                    </button>
                </div>

                <template x-for="(v, index) in variations" :key="index">
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 border border-gray-200">
                        <div class="flex-1">
                            <input type="text" :name="'variations[' + index + '][name]'" x-model="v.name" placeholder="Variation Name (e.g. Full Portion, 12 inch)" required
                                   class="w-full p-2 rounded-xl border border-gray-200 bg-white">
                        </div>
                        <div class="w-36">
                            <input type="number" step="0.01" :name="'variations[' + index + '][price]'" x-model="v.price" placeholder="Price (₹)" required
                                   class="w-full p-2 rounded-xl border border-gray-200 bg-white">
                        </div>
                        <button type="button" @click="removeVariation(index)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Section 5: Add-on Groups -->
            @if($addonGroups->count() > 0)
            <div class="space-y-4 pt-4 border-t border-gray-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#25232A] border-b border-gray-100 pb-2">5. Eligible Add-on Groups</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($addonGroups as $group)
                    <label class="flex items-center gap-2.5 p-3 rounded-2xl border border-gray-200 hover:bg-[#FFF3F8] cursor-pointer transition-colors">
                        <input type="checkbox" name="addon_groups[]" value="{{ $group->id }}" class="rounded text-[#F50070] focus:ring-[#F50070]">
                        <div>
                            <span class="font-bold text-[#25232A] block">{{ $group->name }}</span>
                            <span class="text-[10px] text-gray-500">{{ $group->addons->count() }} add-on items</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="pt-6 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-full text-gray-500 font-bold">Cancel</a>
                <button type="submit" class="px-7 py-3 rounded-full bg-[#F50070] hover:bg-[#D80063] text-white font-extrabold text-sm shadow-md transition-all">
                    Save Dish to Menu
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
