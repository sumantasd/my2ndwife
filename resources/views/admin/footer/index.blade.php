@extends('layouts.admin')

@section('title', 'Footer Management | Admin Portal')
@section('header_title', 'Footer Management')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6 min-w-0 box-border" x-data="footerManager({
    activeTab: '{{ request('tab', 'branding') }}',
    cmsPages: {{ Js::from($pages) }},
    linkModalOpen: false,
    deleteModalOpen: false,
    editMode: false,
    modalLink: {
        id: null,
        section: 'quick_links',
        title: '',
        link_type: 'page',
        page_id: '',
        url: '',
        icon: '',
        open_in_new_tab: false,
        is_enabled: true,
        display_order: 1
    },
    linkToDelete: { id: null, title: '', section: '' }
})">

    <!-- Top Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
            <div class="flex items-center gap-2 font-bold">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="list-disc list-inside pl-6 text-[11px] font-normal space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-[#F0E6EC] shadow-sm min-w-0">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-[#FFF0F6] text-[#F50070]">
                    <i data-lucide="layout-template" class="w-5 h-5"></i>
                </span>
                <h2 class="text-xl font-black text-[#25232A]">Footer Settings & Navigation Links</h2>
            </div>
            <p class="text-xs text-gray-500 mt-1 pl-9">
                Manage all footer sections, dynamic navigation links, social profiles, mobile apps, contact details, and copyright credit.
            </p>
        </div>

        <div class="flex items-center gap-2 self-start md:self-auto">
            <a href="{{ route('home') }}#footer" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                <span>Preview Customer Footer</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs (Responsive Flex-Wrap Multi-Row Layout) -->
    <div class="w-full bg-white p-3 sm:p-3.5 rounded-2xl border border-[#F0E6EC] shadow-2xs">
        <nav class="flex flex-wrap items-center gap-2 sm:gap-2.5" aria-label="Footer Management Tabs">
            <button type="button" @click="activeTab = 'branding'" 
                    :class="activeTab === 'branding' 
                        ? 'bg-[#F50070] text-white shadow-md shadow-pink-500/20 ring-2 ring-[#F50070]/20 font-extrabold' 
                        : 'bg-gray-50/80 text-gray-700 hover:bg-gray-100 hover:text-[#25232A] border border-gray-200/80 font-bold'"
                    class="group inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 cursor-pointer focus:outline-none shrink-0">
                <i data-lucide="image" class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"></i>
                <span>Branding & Tagline</span>
            </button>

            <button type="button" @click="activeTab = 'quick_links'" 
                    :class="activeTab === 'quick_links' 
                        ? 'bg-[#F50070] text-white shadow-md shadow-pink-500/20 ring-2 ring-[#F50070]/20 font-extrabold' 
                        : 'bg-gray-50/80 text-gray-700 hover:bg-gray-100 hover:text-[#25232A] border border-gray-200/80 font-bold'"
                    class="group inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 cursor-pointer focus:outline-none shrink-0">
                <i data-lucide="link" class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"></i>
                <span>Quick Links</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold" 
                      :class="activeTab === 'quick_links' ? 'bg-white/25 text-white' : 'bg-gray-200/80 text-gray-700'">
                    {{ $quickLinks->count() }}
                </span>
            </button>

            <button type="button" @click="activeTab = 'support_links'" 
                    :class="activeTab === 'support_links' 
                        ? 'bg-[#F50070] text-white shadow-md shadow-pink-500/20 ring-2 ring-[#F50070]/20 font-extrabold' 
                        : 'bg-gray-50/80 text-gray-700 hover:bg-gray-100 hover:text-[#25232A] border border-gray-200/80 font-bold'"
                    class="group inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 cursor-pointer focus:outline-none shrink-0">
                <i data-lucide="help-circle" class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"></i>
                <span>Help & Support</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold" 
                      :class="activeTab === 'support_links' ? 'bg-white/25 text-white' : 'bg-gray-200/80 text-gray-700'">
                    {{ $supportLinks->count() }}
                </span>
            </button>

            <button type="button" @click="activeTab = 'policy_links'" 
                    :class="activeTab === 'policy_links' 
                        ? 'bg-[#F50070] text-white shadow-md shadow-pink-500/20 ring-2 ring-[#F50070]/20 font-extrabold' 
                        : 'bg-gray-50/80 text-gray-700 hover:bg-gray-100 hover:text-[#25232A] border border-gray-200/80 font-bold'"
                    class="group inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 cursor-pointer focus:outline-none shrink-0">
                <i data-lucide="shield" class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"></i>
                <span>Bottom Policy Bar</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold" 
                      :class="activeTab === 'policy_links' ? 'bg-white/25 text-white' : 'bg-gray-200/80 text-gray-700'">
                    {{ $policyLinks->count() }}
                </span>
            </button>

            <button type="button" @click="activeTab = 'social'" 
                    :class="activeTab === 'social' 
                        ? 'bg-[#F50070] text-white shadow-md shadow-pink-500/20 ring-2 ring-[#F50070]/20 font-extrabold' 
                        : 'bg-gray-50/80 text-gray-700 hover:bg-gray-100 hover:text-[#25232A] border border-gray-200/80 font-bold'"
                    class="group inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 cursor-pointer focus:outline-none shrink-0">
                <i data-lucide="share-2" class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"></i>
                <span>Social Media</span>
            </button>

            <button type="button" @click="activeTab = 'contact'" 
                    :class="activeTab === 'contact' 
                        ? 'bg-[#F50070] text-white shadow-md shadow-pink-500/20 ring-2 ring-[#F50070]/20 font-extrabold' 
                        : 'bg-gray-50/80 text-gray-700 hover:bg-gray-100 hover:text-[#25232A] border border-gray-200/80 font-bold'"
                    class="group inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 cursor-pointer focus:outline-none shrink-0">
                <i data-lucide="phone-call" class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"></i>
                <span>Contact Information</span>
            </button>

            <button type="button" @click="activeTab = 'apps'" 
                    :class="activeTab === 'apps' 
                        ? 'bg-[#F50070] text-white shadow-md shadow-pink-500/20 ring-2 ring-[#F50070]/20 font-extrabold' 
                        : 'bg-gray-50/80 text-gray-700 hover:bg-gray-100 hover:text-[#25232A] border border-gray-200/80 font-bold'"
                    class="group inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 cursor-pointer focus:outline-none shrink-0">
                <i data-lucide="smartphone" class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"></i>
                <span>App Downloads</span>
            </button>

            <button type="button" @click="activeTab = 'newsletter'" 
                    :class="activeTab === 'newsletter' 
                        ? 'bg-[#F50070] text-white shadow-md shadow-pink-500/20 ring-2 ring-[#F50070]/20 font-extrabold' 
                        : 'bg-gray-50/80 text-gray-700 hover:bg-gray-100 hover:text-[#25232A] border border-gray-200/80 font-bold'"
                    class="group inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 cursor-pointer focus:outline-none shrink-0">
                <i data-lucide="mail" class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"></i>
                <span>Newsletter</span>
            </button>

            <button type="button" @click="activeTab = 'copyright'" 
                    :class="activeTab === 'copyright' 
                        ? 'bg-[#F50070] text-white shadow-md shadow-pink-500/20 ring-2 ring-[#F50070]/20 font-extrabold' 
                        : 'bg-gray-50/80 text-gray-700 hover:bg-gray-100 hover:text-[#25232A] border border-gray-200/80 font-bold'"
                    class="group inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 cursor-pointer focus:outline-none shrink-0">
                <i data-lucide="award" class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"></i>
                <span>Copyright & Credits</span>
            </button>

            <button type="button" @click="activeTab = 'visibility'" 
                    :class="activeTab === 'visibility' 
                        ? 'bg-[#F50070] text-white shadow-md shadow-pink-500/20 ring-2 ring-[#F50070]/20 font-extrabold' 
                        : 'bg-gray-50/80 text-gray-700 hover:bg-gray-100 hover:text-[#25232A] border border-gray-200/80 font-bold'"
                    class="group inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 cursor-pointer focus:outline-none shrink-0">
                <i data-lucide="eye" class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"></i>
                <span>Section Toggles</span>
            </button>
        </nav>
    </div>

    <!-- TAB 1: BRANDING & DESCRIPTION -->
    <div x-show="activeTab === 'branding'" class="space-y-6">
        <form action="{{ route('admin.footer.general.update') }}" method="POST" class="bg-white p-6 rounded-2xl border border-[#F0E6EC] shadow-sm space-y-6">
            @csrf
            <input type="hidden" name="_tab" value="branding">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="text-base font-bold text-[#25232A]">Footer Brand Logo & Short Description</h3>
                <p class="text-xs text-gray-500 mt-0.5">The customer website footer displays your complete official logo and short culinary tagline.</p>
            </div>

            <!-- Official Logo Preview Notice -->
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-bold text-gray-700 block">Current Official Logo (Used in Footer & Customer Header)</span>
                    <p class="text-[11px] text-gray-500">
                        To maintain unified branding across the website, the footer reuses the official logo configured in Branding Settings. No duplicate logo upload needed.
                    </p>
                    <a href="{{ route('admin.settings.index') }}#branding" class="inline-flex items-center gap-1 text-[11px] font-bold text-[#F50070] hover:underline pt-1">
                        <span>Change official logo in Restaurant Branding Settings</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>

                <!-- Logo Preview Box against Dark Footer Background -->
                <div class="px-5 py-3 rounded-xl bg-[#0D1127] border border-[#1E2548] flex items-center justify-center shrink-0">
                    <img src="{{ asset($officialLogo) }}" alt="Footer Logo Preview" class="h-10 w-auto max-w-[200px] object-contain">
                </div>
            </div>

            <!-- Footer Description Textarea -->
            <div>
                <label for="footer_description" class="block text-xs font-bold text-gray-700 mb-1">
                    Footer Brand Description / About Tagline
                </label>
                <textarea name="footer_description" id="footer_description" rows="3"
                          class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] focus:ring-0 p-3 leading-relaxed text-gray-800"
                          placeholder="Authentic Indian & Bengali culinary delicacies delivered piping hot across Kolkata...">{{ old('footer_description', $settings['footer_description'] ?? '') }}</textarea>
                <p class="text-[11px] text-gray-400 mt-1">This text appears directly beneath the official logo in the left footer column.</p>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Branding Settings</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 2: QUICK LINKS -->
    <div x-show="activeTab === 'quick_links'" class="space-y-6">
        <div class="bg-white p-6 rounded-2xl border border-[#F0E6EC] shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-[#25232A]">Manage Quick Links Column</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Configure navigation shortcuts (Menu, Offers, Dish Search, Orders, or CMS Pages).</p>
                </div>
                <button type="button" @click="openCreateLinkModal('quick_links')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Add Quick Link</span>
                </button>
            </div>

            <!-- Table of Quick Links -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-400 uppercase text-[10px] tracking-wider bg-gray-50/50">
                            <th class="py-3 px-4">Order</th>
                            <th class="py-3 px-4">Title & Icon</th>
                            <th class="py-3 px-4">Destination Target</th>
                            <th class="py-3 px-4">Window</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($quickLinks as $link)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-gray-500 w-16">
                                    #{{ $link->display_order }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="p-1.5 rounded-lg bg-gray-100 text-gray-600">
                                            <i data-lucide="{{ $link->icon ?: 'link' }}" class="w-3.5 h-3.5"></i>
                                        </span>
                                        <span class="font-bold text-gray-800">{{ $link->title }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($link->page)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-semibold text-[11px] border border-purple-200">
                                            <i data-lucide="file-text" class="w-3 h-3"></i>
                                            <span>CMS: {{ $link->page->title }}</span>
                                        </span>
                                    @else
                                        <code class="text-gray-600 font-mono text-[11px] bg-gray-100 px-2 py-0.5 rounded">
                                            {{ $link->url }}
                                        </code>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($link->open_in_new_tab)
                                        <span class="text-[11px] text-gray-500 flex items-center gap-1">
                                            <i data-lucide="external-link" class="w-3 h-3 text-gray-400"></i>
                                            <span>New Tab</span>
                                        </span>
                                    @else
                                        <span class="text-[11px] text-gray-400">Same Tab</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <form action="{{ route('admin.footer.links.toggle', $link) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                title="Click to toggle link visibility"
                                                class="px-2.5 py-1 rounded-full text-[10px] font-bold border transition-all {{ $link->is_enabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' }}">
                                            {{ $link->is_enabled ? 'Visible' : 'Hidden' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 px-4 text-right space-x-1">
                                    <button type="button" @click="openEditLinkModal({{ Js::from($link) }})"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-[#F50070] hover:bg-pink-50 transition-colors"
                                            title="Edit Link">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button type="button" @click="confirmDeleteLink({{ Js::from($link) }})"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                            title="Delete Link">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400 text-xs">
                                    No quick links added yet. Click "+ Add Quick Link" above to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: HELP & SUPPORT LINKS -->
    <div x-show="activeTab === 'support_links'" class="space-y-6">
        <div class="bg-white p-6 rounded-2xl border border-[#F0E6EC] shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-[#25232A]">Manage Help & Support Column</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Links for customer support, FAQ, Terms, Privacy Policy, and Contact pages.</p>
                </div>
                <button type="button" @click="openCreateLinkModal('support_links')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Add Support Link</span>
                </button>
            </div>

            <!-- Table of Support Links -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-400 uppercase text-[10px] tracking-wider bg-gray-50/50">
                            <th class="py-3 px-4">Order</th>
                            <th class="py-3 px-4">Title & Icon</th>
                            <th class="py-3 px-4">Destination Target</th>
                            <th class="py-3 px-4">Window</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($supportLinks as $link)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-gray-500 w-16">
                                    #{{ $link->display_order }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="p-1.5 rounded-lg bg-gray-100 text-gray-600">
                                            <i data-lucide="{{ $link->icon ?: 'help-circle' }}" class="w-3.5 h-3.5"></i>
                                        </span>
                                        <span class="font-bold text-gray-800">{{ $link->title }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($link->page)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-semibold text-[11px] border border-purple-200">
                                            <i data-lucide="file-text" class="w-3 h-3"></i>
                                            <span>CMS: {{ $link->page->title }}</span>
                                        </span>
                                    @else
                                        <code class="text-gray-600 font-mono text-[11px] bg-gray-100 px-2 py-0.5 rounded">
                                            {{ $link->url }}
                                        </code>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($link->open_in_new_tab)
                                        <span class="text-[11px] text-gray-500 flex items-center gap-1">
                                            <i data-lucide="external-link" class="w-3 h-3 text-gray-400"></i>
                                            <span>New Tab</span>
                                        </span>
                                    @else
                                        <span class="text-[11px] text-gray-400">Same Tab</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <form action="{{ route('admin.footer.links.toggle', $link) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="px-2.5 py-1 rounded-full text-[10px] font-bold border transition-all {{ $link->is_enabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' }}">
                                            {{ $link->is_enabled ? 'Visible' : 'Hidden' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 px-4 text-right space-x-1">
                                    <button type="button" @click="openEditLinkModal({{ Js::from($link) }})"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-[#F50070] hover:bg-pink-50 transition-colors"
                                            title="Edit Link">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button type="button" @click="confirmDeleteLink({{ Js::from($link) }})"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                            title="Delete Link">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400 text-xs">
                                    No help & support links added yet. Click "+ Add Support Link" above.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: BOTTOM POLICY BAR LINKS -->
    <div x-show="activeTab === 'policy_links'" class="space-y-6">
        <div class="bg-white p-6 rounded-2xl border border-[#F0E6EC] shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-[#25232A]">Manage Bottom Policy Bar Links</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Horizontal policy shortcuts placed at the very bottom bar next to the copyright credit.</p>
                </div>
                <button type="button" @click="openCreateLinkModal('policy_links')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Add Policy Link</span>
                </button>
            </div>

            <!-- Table of Policy Links -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-400 uppercase text-[10px] tracking-wider bg-gray-50/50">
                            <th class="py-3 px-4">Order</th>
                            <th class="py-3 px-4">Title</th>
                            <th class="py-3 px-4">Destination Target</th>
                            <th class="py-3 px-4">Window</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($policyLinks as $link)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-gray-500 w-16">
                                    #{{ $link->display_order }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-gray-800">{{ $link->title }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($link->page)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-semibold text-[11px] border border-purple-200">
                                            <i data-lucide="file-text" class="w-3 h-3"></i>
                                            <span>CMS: {{ $link->page->title }}</span>
                                        </span>
                                    @else
                                        <code class="text-gray-600 font-mono text-[11px] bg-gray-100 px-2 py-0.5 rounded">
                                            {{ $link->url }}
                                        </code>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($link->open_in_new_tab)
                                        <span class="text-[11px] text-gray-500 flex items-center gap-1">
                                            <i data-lucide="external-link" class="w-3 h-3 text-gray-400"></i>
                                            <span>New Tab</span>
                                        </span>
                                    @else
                                        <span class="text-[11px] text-gray-400">Same Tab</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <form action="{{ route('admin.footer.links.toggle', $link) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="px-2.5 py-1 rounded-full text-[10px] font-bold border transition-all {{ $link->is_enabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200' }}">
                                            {{ $link->is_enabled ? 'Visible' : 'Hidden' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 px-4 text-right space-x-1">
                                    <button type="button" @click="openEditLinkModal({{ Js::from($link) }})"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-[#F50070] hover:bg-pink-50 transition-colors"
                                            title="Edit Link">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button type="button" @click="confirmDeleteLink({{ Js::from($link) }})"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                            title="Delete Link">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400 text-xs">
                                    No policy links configured.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 5: SOCIAL MEDIA PROFILES -->
    <div x-show="activeTab === 'social'" class="space-y-6">
        <form action="{{ route('admin.footer.general.update') }}" method="POST" class="bg-white p-6 rounded-2xl border border-[#F0E6EC] shadow-sm space-y-6">
            @csrf
            <input type="hidden" name="_tab" value="social">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="text-base font-bold text-[#25232A]">Social Media Profiles & Visible Inline Icons</h3>
                <p class="text-xs text-gray-500 mt-0.5">Manage URLs and active toggles for all official social badges shown in the footer branding column.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Facebook -->
                <div class="p-4 rounded-xl border border-gray-200 hover:border-blue-300 transition-colors space-y-3 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-full bg-[#1877F2] text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-800">Facebook Page</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="facebook_enabled" value="1" class="sr-only peer"
                                   {{ !empty($settings['facebook_enabled']) ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>
                    <div>
                        <input type="url" name="facebook_url" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}"
                               placeholder="https://facebook.com/my2ndwife"
                               class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                    </div>
                </div>

                <!-- Instagram -->
                <div class="p-4 rounded-xl border border-gray-200 hover:border-pink-300 transition-colors space-y-3 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-full bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-800">Instagram Profile</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="instagram_enabled" value="1" class="sr-only peer"
                                   {{ !empty($settings['instagram_enabled']) ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>
                    <div>
                        <input type="url" name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}"
                               placeholder="https://instagram.com/my2ndwife"
                               class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                    </div>
                </div>

                <!-- Twitter / X -->
                <div class="p-4 rounded-xl border border-gray-200 hover:border-gray-400 transition-colors space-y-3 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-full bg-black text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-800">Twitter / X Profile</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="twitter_enabled" value="1" class="sr-only peer"
                                   {{ !empty($settings['twitter_enabled']) ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>
                    <div>
                        <input type="url" name="twitter_url" value="{{ old('twitter_url', $settings['twitter_url'] ?? '') }}"
                               placeholder="https://x.com/my2ndwife"
                               class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                    </div>
                </div>

                <!-- YouTube -->
                <div class="p-4 rounded-xl border border-gray-200 hover:border-red-300 transition-colors space-y-3 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-full bg-[#FF0000] text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-800">YouTube Channel</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="youtube_enabled" value="1" class="sr-only peer"
                                   {{ !empty($settings['youtube_enabled']) ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>
                    <div>
                        <input type="url" name="youtube_url" value="{{ old('youtube_url', $settings['youtube_url'] ?? '') }}"
                               placeholder="https://youtube.com/@my2ndwife"
                               class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                    </div>
                </div>

                <!-- LinkedIn -->
                <div class="p-4 rounded-xl border border-gray-200 hover:border-blue-300 transition-colors space-y-3 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-full bg-[#0A66C2] text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-800">LinkedIn Profile / Company</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="linkedin_enabled" value="1" class="sr-only peer"
                                   {{ !empty($settings['linkedin_enabled']) ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>
                    <div>
                        <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $settings['linkedin_url'] ?? '') }}"
                               placeholder="https://linkedin.com/company/my2ndwife"
                               class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                    </div>
                </div>

                <!-- WhatsApp -->
                <div class="p-4 rounded-xl border border-gray-200 hover:border-emerald-300 transition-colors space-y-3 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-full bg-[#25D366] text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            </span>
                            <span class="text-xs font-bold text-gray-800">WhatsApp Chat</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="whatsapp_enabled" value="1" class="sr-only peer"
                                   {{ !empty($settings['whatsapp_enabled']) ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>
                    <div>
                        <input type="text" name="restaurant_whatsapp" value="{{ old('restaurant_whatsapp', $settings['restaurant_whatsapp'] ?? '') }}"
                               placeholder="+91 98300 12345"
                               class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Social Profiles</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 6: CONTACT INFORMATION -->
    <div x-show="activeTab === 'contact'" class="space-y-6">
        <form action="{{ route('admin.footer.general.update') }}" method="POST" class="bg-white p-6 rounded-2xl border border-[#F0E6EC] shadow-sm space-y-6">
            @csrf
            <input type="hidden" name="_tab" value="contact">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="text-base font-bold text-[#25232A]">Contact Details & Operating Schedule</h3>
                <p class="text-xs text-gray-500 mt-0.5">Shown in Column 4 of the customer footer.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Phone -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Customer Support Phone</label>
                    <input type="text" name="restaurant_phone" value="{{ old('restaurant_phone', $settings['restaurant_phone'] ?? '') }}"
                           placeholder="+91 98300 12345"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2.5 text-gray-800">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Customer Support Email</label>
                    <input type="email" name="restaurant_email" value="{{ old('restaurant_email', $settings['restaurant_email'] ?? '') }}"
                           placeholder="hello@my2ndwife.com"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2.5 text-gray-800">
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Physical Restaurant Address</label>
                    <input type="text" name="restaurant_address" value="{{ old('restaurant_address', $settings['restaurant_address'] ?? '') }}"
                           placeholder="Plot 42, Park Street Boulevard, Park Circus, Kolkata - 700016"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2.5 text-gray-800">
                </div>

                <!-- Opening Time -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Opening Time</label>
                    <input type="text" name="opening_time" value="{{ old('opening_time', $settings['opening_time'] ?? '11:00 AM') }}"
                           placeholder="11:00 AM"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2.5 text-gray-800">
                </div>

                <!-- Closing Time -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Closing Time</label>
                    <input type="text" name="closing_time" value="{{ old('closing_time', $settings['closing_time'] ?? '11:30 PM') }}"
                           placeholder="11:30 PM"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2.5 text-gray-800">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Contact Info</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 7: APP DOWNLOAD SECTION -->
    <div x-show="activeTab === 'apps'" class="space-y-6">
        <form action="{{ route('admin.footer.general.update') }}" method="POST" class="bg-white p-6 rounded-2xl border border-[#F0E6EC] shadow-sm space-y-6">
            @csrf
            <input type="hidden" name="_tab" value="apps">

            <div class="border-b border-gray-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#25232A]">Mobile App & PWA Download Buttons</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Controls the 5th column promoting Android Google Play Store and PWA installation.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-700">Section Enabled</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="footer_app_download_enabled" value="1" class="sr-only peer"
                               {{ !isset($settings['footer_app_download_enabled']) || !empty($settings['footer_app_download_enabled']) ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Heading -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Section Heading</label>
                    <input type="text" name="footer_app_heading" value="{{ old('footer_app_heading', $settings['footer_app_heading'] ?? 'Order Faster with Our Mobile App') }}"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2.5 text-gray-800">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Section Subtitle / Description</label>
                    <input type="text" name="footer_app_description" value="{{ old('footer_app_description', $settings['footer_app_description'] ?? 'Get our app for a faster, smoother and better food ordering experience.') }}"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2.5 text-gray-800">
                </div>

                <!-- Android Play Store URL & Label -->
                <div class="p-4 rounded-xl border border-gray-200 space-y-3 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-800 flex items-center gap-2">
                            <i data-lucide="play" class="w-3.5 h-3.5 text-emerald-600"></i>
                            <span>Google Play Store Button</span>
                        </span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="app_android_enabled" value="1" class="sr-only peer"
                                   {{ !isset($settings['app_android_enabled']) || !empty($settings['app_android_enabled']) ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 mb-1">Play Store Listing URL</label>
                        <input type="text" name="app_android_url" value="{{ old('app_android_url', $settings['app_android_url'] ?? '') }}"
                               placeholder="https://play.google.com/store/apps/details?id=com.my2ndwife.app"
                               class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                        <p class="text-[10px] text-gray-400 mt-1">If left empty, a polished "Coming Soon" badge will be shown automatically without fake dead links.</p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 mb-1">Button Subtext Label</label>
                        <input type="text" name="app_android_button_label" value="{{ old('app_android_button_label', $settings['app_android_button_label'] ?? 'Android App') }}"
                               class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                    </div>
                </div>

                <!-- PWA Button -->
                <div class="p-4 rounded-xl border border-gray-200 space-y-3 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-800 flex items-center gap-2">
                            <i data-lucide="globe" class="w-3.5 h-3.5 text-blue-600"></i>
                            <span>Progressive Web App (PWA) Button</span>
                        </span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="app_pwa_enabled" value="1" class="sr-only peer"
                                   {{ !isset($settings['app_pwa_enabled']) || !empty($settings['app_pwa_enabled']) ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 mb-1">Button Subtext Label</label>
                        <input type="text" name="app_pwa_button_label" value="{{ old('app_pwa_button_label', $settings['app_pwa_button_label'] ?? 'PWA App') }}"
                               class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                    </div>
                    <p class="text-[11px] text-gray-500 pt-1">
                        Opens the installable web manifest allowing customers to add My 2nd Wife to their Android or iOS home screens with zero download.
                    </p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save App Download Settings</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 8: NEWSLETTER SETTINGS -->
    <div x-show="activeTab === 'newsletter'" class="space-y-6">
        <form action="{{ route('admin.footer.general.update') }}" method="POST" class="bg-white p-6 rounded-2xl border border-[#F0E6EC] shadow-sm space-y-6">
            @csrf
            <input type="hidden" name="_tab" value="newsletter">

            <div class="border-b border-gray-100 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#25232A]">Newsletter Subscription Section</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Customize copy and input placeholders. The existing live AJAX subscription endpoint is preserved.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-700">Section Enabled</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="footer_newsletter_enabled" value="1" class="sr-only peer"
                               {{ !isset($settings['footer_newsletter_enabled']) || !empty($settings['footer_newsletter_enabled']) ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Heading -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Newsletter Heading</label>
                    <input type="text" name="footer_newsletter_heading" value="{{ old('footer_newsletter_heading', $settings['footer_newsletter_heading'] ?? 'Subscribe to our newsletter') }}"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2.5 text-gray-800">
                </div>

                <!-- Button Text -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Subscribe Button Text</label>
                    <input type="text" name="footer_newsletter_button_text" value="{{ old('footer_newsletter_button_text', $settings['footer_newsletter_button_text'] ?? 'Subscribe') }}"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2.5 text-gray-800">
                </div>

                <!-- Input Placeholder -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Email Input Placeholder</label>
                    <input type="text" name="footer_newsletter_placeholder" value="{{ old('footer_newsletter_placeholder', $settings['footer_newsletter_placeholder'] ?? 'Enter your email address') }}"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2.5 text-gray-800">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Description Subtext</label>
                    <input type="text" name="footer_newsletter_description" value="{{ old('footer_newsletter_description', $settings['footer_newsletter_description'] ?? 'Get the latest offers, new dishes and updates delivered to your inbox.') }}"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2.5 text-gray-800">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Newsletter Settings</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 9: COPYRIGHT & LEGAL CREDIT -->
    <div x-show="activeTab === 'copyright'" class="space-y-6">
        <form action="{{ route('admin.footer.general.update') }}" method="POST" class="bg-white p-6 rounded-2xl border border-[#F0E6EC] shadow-sm space-y-6">
            @csrf
            <input type="hidden" name="_tab" value="copyright">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="text-base font-bold text-[#25232A]">Copyright Notice & Developer Credit</h3>
                <p class="text-xs text-gray-500 mt-0.5">Controls the bottom legal credit bar of the customer website.</p>
            </div>

            <div class="space-y-4">
                <!-- Custom Copyright Notice -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Custom Copyright Text</label>
                    <input type="text" name="footer_copyright_text" value="{{ old('footer_copyright_text', $settings['footer_copyright_text'] ?? 'All rights reserved.') }}"
                           placeholder="All rights reserved."
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2.5 text-gray-800">
                    <p class="text-[11px] text-gray-400 mt-1">Rendered after "© {{ date('Y') }} {{ $settings['restaurant_name'] ?? 'My 2nd Wife' }} Food Delivery System."</p>
                </div>

                <!-- Preserved Tech Googly Credit Notice & URL -->
                <div class="p-4 rounded-xl bg-purple-50 border border-purple-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="award" class="w-4 h-4 text-purple-700"></i>
                            <span class="text-xs font-bold text-purple-900">Developer Credit — Tech Googly</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-200 text-purple-800">Protected Legal Credit</span>
                    </div>
                    <p class="text-[11px] text-purple-800 leading-relaxed">
                        The customer website proudly credits <strong>Developed By Tech Googly</strong> with a link to <a href="https://techgoogly.com/" target="_blank" class="underline font-bold">https://techgoogly.com/</a>. The obsolete text <em>"Online & Delivering Across Kolkata"</em> remains permanently removed.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-[11px] font-bold text-purple-900 mb-1">Developer Name Text</label>
                            <input type="text" name="footer_dev_credit_text" value="{{ old('footer_dev_credit_text', $settings['footer_dev_credit_text'] ?? 'Tech Googly') }}"
                                   class="w-full text-xs rounded-xl border border-purple-300 focus:border-purple-600 px-3 py-2 bg-white text-gray-800">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-purple-900 mb-1">Developer Website URL</label>
                            <input type="text" name="footer_dev_credit_url" value="{{ old('footer_dev_credit_url', $settings['footer_dev_credit_url'] ?? 'https://techgoogly.com/') }}"
                                   class="w-full text-xs rounded-xl border border-purple-300 focus:border-purple-600 px-3 py-2 bg-white text-gray-800">
                        </div>
                    </div>
                </div>

                <!-- Footer Accent Badges & Culinary Wave Controls -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-gray-800 block">Payment Methods Badges</span>
                            <span class="text-[11px] text-gray-500">VISA, Mastercard, UPI, RuPay & COD logos</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="footer_payment_methods_enabled" value="1" class="sr-only peer"
                                   {{ !isset($settings['footer_payment_methods_enabled']) || !empty($settings['footer_payment_methods_enabled']) ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-gray-800 block">Decorative Wave & Food Doodles</span>
                            <span class="text-[11px] text-gray-500">Subtle background culinary line art wave</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="footer_decorations_enabled" value="1" class="sr-only peer"
                                   {{ !isset($settings['footer_decorations_enabled']) || !empty($settings['footer_decorations_enabled']) ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Copyright Settings</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 10: SECTION VISIBILITY -->
    <div x-show="activeTab === 'visibility'" class="space-y-6">
        <form action="{{ route('admin.footer.general.update') }}" method="POST" class="bg-white p-6 rounded-2xl border border-[#F0E6EC] shadow-sm space-y-6">
            @csrf
            <input type="hidden" name="_tab" value="visibility">

            <div class="border-b border-gray-100 pb-4">
                <h3 class="text-base font-bold text-[#25232A]">Enable / Disable Footer Sections</h3>
                <p class="text-xs text-gray-500 mt-0.5">Toggle complete columns and aesthetic elements on or off with a single click.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Quick Links Column -->
                <div class="p-4 rounded-xl border border-gray-200 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-800 block">Quick Links Column</span>
                        <span class="text-[11px] text-gray-500">Column 2 with navigation links</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="footer_quick_links_enabled" value="1" class="sr-only peer"
                               {{ !isset($settings['footer_quick_links_enabled']) || !empty($settings['footer_quick_links_enabled']) ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>

                <!-- Help & Support Column -->
                <div class="p-4 rounded-xl border border-gray-200 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-800 block">Help & Support Column</span>
                        <span class="text-[11px] text-gray-500">Column 3 with support, FAQ, and legal pages</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="footer_support_links_enabled" value="1" class="sr-only peer"
                               {{ !isset($settings['footer_support_links_enabled']) || !empty($settings['footer_support_links_enabled']) ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>

                <!-- App Download Column -->
                <div class="p-4 rounded-xl border border-gray-200 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-800 block">Mobile App Download Column</span>
                        <span class="text-[11px] text-gray-500">Column 5 with Android & PWA badges</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="footer_app_download_enabled" value="1" class="sr-only peer"
                               {{ !isset($settings['footer_app_download_enabled']) || !empty($settings['footer_app_download_enabled']) ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>

                <!-- Newsletter Subsection -->
                <div class="p-4 rounded-xl border border-gray-200 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-800 block">Newsletter Box</span>
                        <span class="text-[11px] text-gray-500">Email subscription form under branding</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="footer_newsletter_enabled" value="1" class="sr-only peer"
                               {{ !isset($settings['footer_newsletter_enabled']) || !empty($settings['footer_newsletter_enabled']) ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>

                <!-- Payment Methods Badges -->
                <div class="p-4 rounded-xl border border-gray-200 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-800 block">Payment Methods Badges</span>
                        <span class="text-[11px] text-gray-500">VISA, Mastercard, UPI, RuPay & COD logos</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="footer_payment_methods_enabled" value="1" class="sr-only peer"
                               {{ !isset($settings['footer_payment_methods_enabled']) || !empty($settings['footer_payment_methods_enabled']) ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>

                <!-- Bottom Decorative Food Wave & Doodles -->
                <div class="p-4 rounded-xl border border-gray-200 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-800 block">Decorative Wave & Food Doodles</span>
                        <span class="text-[11px] text-gray-500">Ambient chef hat, herb leaves, and wave gradient</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="footer_decorations_enabled" value="1" class="sr-only peer"
                               {{ !isset($settings['footer_decorations_enabled']) || !empty($settings['footer_decorations_enabled']) ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F50070]"></div>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#F50070] hover:bg-[#D40060] text-white text-xs font-bold shadow-md shadow-pink-500/20 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Visibility Settings</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ADD / EDIT LINK MODAL -->
    <div x-show="linkModalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.outside="linkModalOpen = false"
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 space-y-5 animate-in fade-in zoom-in-95 duration-150">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-pink-50 text-[#F50070]">
                        <i data-lucide="link-2" class="w-4 h-4"></i>
                    </span>
                    <h3 class="text-sm font-bold text-gray-800" x-text="editMode ? 'Edit Footer Link' : 'Add New Footer Link'"></h3>
                </div>
                <button type="button" @click="linkModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="editMode ? '{{ url('admin/footer-settings/links') }}/' + modalLink.id : '{{ route('admin.footer.links.store') }}'" 
                  method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <input type="hidden" name="section" :value="modalLink.section">

                <!-- Link Title -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Link Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" x-model="modalLink.title" required
                           placeholder="e.g., Home Page, Full Menu, Privacy Policy"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                </div>

                <!-- Destination Type Selector -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Destination Type</label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                               :class="modalLink.link_type === 'page' ? 'border-[#F50070] bg-pink-50/50 font-bold text-[#F50070]' : 'border-gray-200 text-gray-600'">
                            <input type="radio" name="link_type" value="page" x-model="modalLink.link_type" class="sr-only">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                            <span>Select CMS Page</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                               :class="modalLink.link_type === 'url' ? 'border-[#F50070] bg-pink-50/50 font-bold text-[#F50070]' : 'border-gray-200 text-gray-600'">
                            <input type="radio" name="link_type" value="url" x-model="modalLink.link_type" class="sr-only">
                            <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                            <span>Custom URL</span>
                        </label>
                    </div>
                </div>

                <!-- Select CMS Page -->
                <div x-show="modalLink.link_type === 'page'">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Target CMS Page <span class="text-rose-500">*</span></label>
                    <select name="page_id" x-model="modalLink.page_id"
                            class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                        <option value="">-- Choose a published CMS Page --</option>
                        <template x-for="p in cmsPages" :key="p.id">
                            <option :value="p.id" x-text="p.title + ' (/page/' + p.slug + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Custom URL -->
                <div x-show="modalLink.link_type === 'url'">
                    <label class="block text-xs font-bold text-gray-700 mb-1">URL Path or Full Web Address <span class="text-rose-500">*</span></label>
                    <input type="text" name="url" x-model="modalLink.url"
                           placeholder="e.g., /menu or /offers or https://example.com"
                           class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                </div>

                <!-- Lucide Icon Name (Optional) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Lucide Icon Name (Optional)</label>
                    <div class="relative">
                        <input type="text" name="icon" x-model="modalLink.icon"
                               placeholder="e.g. home, utensils-crossed, tag, search, truck, help-circle, shield-check"
                               class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">Accepts standard Lucide icon names like home, search, tag, truck, info, file-text, shield-check, rotate-ccw.</p>
                </div>

                <!-- Display Order & Checkboxes -->
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Display Order</label>
                        <input type="number" name="display_order" x-model="modalLink.display_order" min="1"
                               class="w-full text-xs rounded-xl border border-gray-200 focus:border-[#F50070] px-3 py-2 text-gray-800">
                    </div>

                    <div class="space-y-2 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-700">
                            <input type="checkbox" name="open_in_new_tab" value="1" x-model="modalLink.open_in_new_tab"
                                   class="rounded text-[#F50070] focus:ring-0">
                            <span>Open in new tab</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-700">
                            <input type="checkbox" name="is_enabled" value="1" x-model="modalLink.is_enabled"
                                   class="rounded text-[#F50070] focus:ring-0">
                            <span>Link is active</span>
                        </label>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" @click="linkModalOpen = false"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-bold bg-[#F50070] hover:bg-[#D40060] text-white transition-all shadow-md shadow-pink-500/20">
                        <span x-text="editMode ? 'Update Link' : 'Create Link'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div x-show="deleteModalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.outside="deleteModalOpen = false"
             class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 space-y-4 animate-in fade-in zoom-in-95 duration-150 text-center">
            
            <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 mx-auto flex items-center justify-center">
                <i data-lucide="trash-2" class="w-6 h-6"></i>
            </div>

            <div>
                <h3 class="text-sm font-bold text-gray-800">Delete Footer Link?</h3>
                <p class="text-xs text-gray-500 mt-1">
                    Are you sure you want to remove <span class="font-bold text-gray-800" x-text="'\"' + linkToDelete.title + '\"'"></span> from the footer? This action cannot be undone.
                </p>
            </div>

            <form :action="'{{ url('admin/footer-settings/links') }}/' + linkToDelete.id" method="POST" class="pt-2 flex items-center justify-center gap-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition-all shadow-md shadow-rose-500/20">
                    Yes, Delete Link
                </button>
            </form>
        </div>
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('footerManager', (config) => ({
        activeTab: config.activeTab,
        cmsPages: config.cmsPages,
        linkModalOpen: false,
        deleteModalOpen: false,
        editMode: false,
        modalLink: {
            id: null,
            section: 'quick_links',
            title: '',
            link_type: 'page',
            page_id: '',
            url: '',
            icon: '',
            open_in_new_tab: false,
            is_enabled: true,
            display_order: 1
        },
        linkToDelete: { id: null, title: '', section: '' },

        init() {
            const hash = window.location.hash.replace('#', '');
            const validTabs = ['branding', 'quick_links', 'support_links', 'policy_links', 'social', 'contact', 'apps', 'newsletter', 'copyright', 'visibility'];
            if (hash && validTabs.includes(hash)) {
                this.activeTab = hash;
            }
            this.$watch('activeTab', (newTab) => {
                if (window.history && window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.set('tab', newTab);
                    window.history.replaceState(null, '', url);
                }
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            });
        },

        openCreateLinkModal(section) {
            this.editMode = false;
            this.modalLink = {
                id: null,
                section: section,
                title: '',
                link_type: 'page',
                page_id: '',
                url: '',
                icon: section === 'quick_links' ? 'link' : (section === 'support_links' ? 'help-circle' : ''),
                open_in_new_tab: false,
                is_enabled: true,
                display_order: 10
            };
            this.linkModalOpen = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        openEditLinkModal(link) {
            this.editMode = true;
            this.modalLink = {
                id: link.id,
                section: link.section,
                title: link.title,
                link_type: link.page_id ? 'page' : 'url',
                page_id: link.page_id || '',
                url: link.url || '',
                icon: link.icon || '',
                open_in_new_tab: Boolean(link.open_in_new_tab),
                is_enabled: Boolean(link.is_enabled),
                display_order: link.display_order
            };
            this.linkModalOpen = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        confirmDeleteLink(link) {
            this.linkToDelete = { id: link.id, title: link.title, section: link.section };
            this.deleteModalOpen = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        }
    }));
});
</script>
@endsection
