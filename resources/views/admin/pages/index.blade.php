@extends('layouts.admin')

@section('title', 'Website Pages | Admin Portal')
@section('header_title', 'Website Pages CMS')

@section('content')
<div class="space-y-6">

    <!-- Header Stats & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-[#25232A]">Managed Website Pages</h2>
            <p class="text-xs text-gray-500 mt-1">Manage public CMS pages, legal policies, About Us, and customer information.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pages.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 transition-all active:scale-95">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Create New Page</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-[#F0E6EC] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 block uppercase tracking-wider">Total Pages</span>
                <span class="text-2xl font-black text-[#25232A]">{{ $stats['total'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-[#F0E6EC] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 block uppercase tracking-wider">Published</span>
                <span class="text-2xl font-black text-emerald-600">{{ $stats['published'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-[#F0E6EC] shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 block uppercase tracking-wider">Draft / Hidden</span>
                <span class="text-2xl font-black text-amber-600">{{ $stats['draft'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="eye-off" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="p-4 bg-white rounded-2xl border border-[#F0E6EC] shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.pages.index') }}" method="GET" class="w-full md:w-auto flex flex-col sm:flex-row items-center gap-3">
            <div class="relative w-full sm:w-72">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or slug..." 
                       class="w-full pl-10 pr-4 py-2 bg-gray-50 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:border-[#F50070] transition-colors">
            </div>

            <select name="status" onchange="this.form.submit()" 
                    class="w-full sm:w-44 py-2 px-3 bg-gray-50 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:border-[#F50070]">
                <option value="">All Statuses</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published Only</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft / Unpublished</option>
            </select>

            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.pages.index') }}" class="text-xs text-rose-500 hover:underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Pages Table -->
    <div class="bg-white rounded-2xl border border-[#F0E6EC] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100 text-[11px] font-black uppercase tracking-wider text-gray-500">
                        <th class="py-3.5 px-4">Order</th>
                        <th class="py-3.5 px-4">Page Title & Slug</th>
                        <th class="py-3.5 px-4">Meta SEO</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4">Last Updated</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs text-gray-700 font-medium">
                    @forelse($pages as $page)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="py-3.5 px-4 font-mono text-gray-400">
                                #{{ $page->display_order }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-[#25232A] text-sm">{{ $page->title }}</div>
                                <div class="text-[11px] text-gray-400 font-mono mt-0.5 flex items-center gap-1.5">
                                    <span>/page/{{ $page->slug }}</span>
                                    <span class="text-gray-300">&bull;</span>
                                    <a href="{{ $page->public_url }}" target="_blank" class="text-pink-600 hover:underline inline-flex items-center gap-0.5">
                                        <span>Preview</span>
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                @if($page->meta_title)
                                    <span class="block font-semibold text-gray-800 truncate">{{ $page->meta_title }}</span>
                                @else
                                    <span class="text-gray-400 italic">No custom meta</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('admin.pages.toggle', $page) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border transition-colors cursor-pointer {{ $page->is_published ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' }}"
                                            title="Click to toggle status">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $page->is_published ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        <span>{{ $page->is_published ? 'Published' : 'Draft' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-gray-500">
                                <div>{{ $page->updated_at->format('d M Y, h:i A') }}</div>
                                <div class="text-[10px] text-gray-400">{{ $page->updated_at->diffForHumans() }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.pages.edit', $page) }}" 
                                       class="p-2 rounded-xl bg-gray-100 hover:bg-[#F50070] text-gray-600 hover:text-white transition-colors"
                                       title="Edit Page">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </a>

                                    @if(!in_array($page->slug, ['about', 'terms', 'privacy', 'refund-policy', 'faq', 'contact']))
                                        <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete page \'{{ $page->title }}\'?');" 
                                              class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-2 rounded-xl bg-gray-100 hover:bg-rose-600 text-gray-600 hover:text-white transition-colors cursor-pointer"
                                                    title="Delete Page">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                <i data-lucide="file-x" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                <p>No website pages found matching your criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pages->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $pages->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
