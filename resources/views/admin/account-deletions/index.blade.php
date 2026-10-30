@extends('layouts.admin')

@section('title', 'Account Deletion Requests')
@section('header_title', 'Account Deletion Requests')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Total -->
        <a href="{{ route('admin.account-deletions.index') }}"
           class="bg-white p-4 rounded-2xl border {{ !request('status') || request('status') === 'all' ? 'border-[#F50070] shadow-sm' : 'border-[#F0E6EC]' }} hover:border-[#F50070] transition-all">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Total Requests</span>
            <span class="text-2xl font-black text-[#25232A]">{{ $counts['total'] }}</span>
        </a>

        <!-- Pending -->
        <a href="{{ route('admin.account-deletions.index', ['status' => 'pending']) }}"
           class="bg-white p-4 rounded-2xl border {{ request('status') === 'pending' ? 'border-amber-400 shadow-sm' : 'border-[#F0E6EC]' }} hover:border-amber-400 transition-all">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 block mb-1">Pending</span>
            <span class="text-2xl font-black text-amber-600">{{ $counts['pending'] }}</span>
        </a>

        <!-- Under Review -->
        <a href="{{ route('admin.account-deletions.index', ['status' => 'under_review']) }}"
           class="bg-white p-4 rounded-2xl border {{ request('status') === 'under_review' ? 'border-blue-400 shadow-sm' : 'border-[#F0E6EC]' }} hover:border-blue-400 transition-all">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 block mb-1">Under Review</span>
            <span class="text-2xl font-black text-blue-600">{{ $counts['under_review'] }}</span>
        </a>

        <!-- Approved -->
        <a href="{{ route('admin.account-deletions.index', ['status' => 'approved']) }}"
           class="bg-white p-4 rounded-2xl border {{ request('status') === 'approved' ? 'border-emerald-400 shadow-sm' : 'border-[#F0E6EC]' }} hover:border-emerald-400 transition-all">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 block mb-1">Approved</span>
            <span class="text-2xl font-black text-emerald-600">{{ $counts['approved'] }}</span>
        </a>

        <!-- Completed -->
        <a href="{{ route('admin.account-deletions.index', ['status' => 'completed']) }}"
           class="bg-white p-4 rounded-2xl border {{ request('status') === 'completed' ? 'border-gray-500 shadow-sm' : 'border-[#F0E6EC]' }} hover:border-gray-500 transition-all">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-600 block mb-1">Completed</span>
            <span class="text-2xl font-black text-gray-700">{{ $counts['completed'] }}</span>
        </a>

        <!-- Rejected -->
        <a href="{{ route('admin.account-deletions.index', ['status' => 'rejected']) }}"
           class="bg-white p-4 rounded-2xl border {{ request('status') === 'rejected' ? 'border-rose-400 shadow-sm' : 'border-[#F0E6EC]' }} hover:border-rose-400 transition-all">
            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600 block mb-1">Rejected</span>
            <span class="text-2xl font-black text-rose-600">{{ $counts['rejected'] }}</span>
        </a>
    </div>

    <!-- Filters and Search Bar -->
    <div class="bg-white p-5 rounded-3xl border border-[#F0E6EC] shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.account-deletions.index') }}" method="GET" class="w-full sm:w-96 relative flex items-center gap-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative flex-1">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search reference #, name, phone, email..."
                       class="w-full text-xs pl-9 pr-4 py-2.5 rounded-full border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
            </div>
            @if(request('search'))
                <a href="{{ route('admin.account-deletions.index', request()->except('search')) }}"
                   class="px-3 py-2 text-xs font-semibold text-gray-500 hover:text-gray-700 bg-gray-100 rounded-full">
                    Clear
                </a>
            @endif
        </form>

        <!-- Status Filter Links -->
        <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto">
            <a href="{{ route('admin.account-deletions.index', request()->except('status')) }}"
               class="px-3 py-1.5 rounded-full text-xs font-bold transition-all {{ !request('status') || request('status') === 'all' ? 'bg-[#F50070] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                All
            </a>
            <a href="{{ route('admin.account-deletions.index', array_merge(request()->all(), ['status' => 'pending'])) }}"
               class="px-3 py-1.5 rounded-full text-xs font-bold transition-all {{ request('status') === 'pending' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Pending
            </a>
            <a href="{{ route('admin.account-deletions.index', array_merge(request()->all(), ['status' => 'under_review'])) }}"
               class="px-3 py-1.5 rounded-full text-xs font-bold transition-all {{ request('status') === 'under_review' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Under Review
            </a>
            <a href="{{ route('admin.account-deletions.index', array_merge(request()->all(), ['status' => 'approved'])) }}"
               class="px-3 py-1.5 rounded-full text-xs font-bold transition-all {{ request('status') === 'approved' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Approved
            </a>
            <a href="{{ route('admin.account-deletions.index', array_merge(request()->all(), ['status' => 'completed'])) }}"
               class="px-3 py-1.5 rounded-full text-xs font-bold transition-all {{ request('status') === 'completed' ? 'bg-gray-700 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Completed
            </a>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="bg-white rounded-3xl border border-[#F0E6EC] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px] bg-gray-50/50">
                        <th class="py-3.5 px-6">Reference #</th>
                        <th class="py-3.5 px-4">Requester Info</th>
                        <th class="py-3.5 px-4">Reason</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Identity Verification</th>
                        <th class="py-3.5 px-4">Submitted At</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requests as $req)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <!-- Reference # -->
                        <td class="py-3.5 px-6 font-mono font-bold text-[#25232A]">
                            <a href="{{ route('admin.account-deletions.show', $req) }}" class="text-[#F50070] hover:underline">
                                {{ $req->reference_number }}
                            </a>
                        </td>

                        <!-- Requester Info -->
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-sm text-[#25232A]">{{ $req->full_name }}</div>
                            <div class="text-gray-500 text-[11px] font-mono">{{ $req->mobile_number }}</div>
                            @if($req->email)
                                <div class="text-gray-400 text-[10px]">{{ $req->email }}</div>
                            @endif
                            @if($req->user)
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] bg-pink-50 text-pink-700 border border-pink-100">
                                        <i data-lucide="user-check" class="w-2.5 h-2.5"></i>
                                        <span>Matched User #{{ $req->user_id }}</span>
                                    </span>
                                </div>
                            @endif
                        </td>

                        <!-- Reason -->
                        <td class="py-3.5 px-4">
                            <span class="font-semibold text-gray-700 block">{{ $req->reason_label }}</span>
                            @if($req->message)
                                <span class="text-gray-400 text-[11px] line-clamp-1 max-w-xs">{{ $req->message }}</span>
                            @endif
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $req->status_badge_class }}">
                                {{ $req->status_label }}
                            </span>
                        </td>

                        <!-- Identity Verified -->
                        <td class="py-3.5 px-4">
                            @if($req->isIdentityVerified())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                    <span>Verified</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-500 border border-gray-200">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    <span>Unverified</span>
                                </span>
                            @endif
                        </td>

                        <!-- Submitted At -->
                        <td class="py-3.5 px-4 text-gray-500">
                            <div>{{ $req->created_at->format('M d, Y') }}</div>
                            <div class="text-[10px] text-gray-400">{{ $req->created_at->format('h:i A') }}</div>
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-6 text-right">
                            <a href="{{ route('admin.account-deletions.show', $req) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-[#F50070] text-gray-700 hover:text-white font-bold text-xs transition-colors">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Details</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-400">
                            <i data-lucide="inbox" class="w-10 h-10 mx-auto text-gray-300 mb-2"></i>
                            <p class="font-bold text-sm text-gray-500">No account deletion requests found.</p>
                            <p class="text-xs text-gray-400">Requests submitted via the public deletion page or mobile app will appear here.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $requests->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
