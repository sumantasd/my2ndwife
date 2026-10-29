@props(['category'])

<a href="{{ route('menu.index', ['category' => $category->slug]) }}" 
   class="group flex flex-col items-center text-center p-3 rounded-3xl bg-[#FFF3F8]/50 hover:bg-[#FFF3F8] border border-[#F0E6EC] hover:border-[#F50070] transition-all">
    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden mb-3 bg-white shadow-sm flex items-center justify-center p-2 group-hover:scale-105 transition-transform">
        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-full h-full object-contain">
    </div>
    <span class="text-xs sm:text-sm font-bold text-[#25232A] group-hover:text-[#F50070] transition-colors line-clamp-1">
        {{ $category->name }}
    </span>
    <span class="text-[10px] text-gray-400 font-semibold mt-0.5">
        {{ $category->products_count ?? $category->activeProducts()->count() }} dishes
    </span>
</a>
