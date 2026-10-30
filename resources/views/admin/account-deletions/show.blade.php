@extends('layouts.admin')

@section('title', 'Request ' . $accountDeletion->reference_number)
@section('header_title', 'Deletion Request Details')

@section('content')
<div class="space-y-6">

    <!-- Top Navigation / Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.account-deletions.index') }}"
               class="p-2 rounded-xl bg-white border border-[#F0E6EC] hover:bg-gray-50 text-gray-700 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-[#25232A] font-rubik tracking-tight">
                        {{ $accountDeletion->reference_number }}
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $accountDeletion->status_badge_class }}">
                        {{ $accountDeletion->status_label }}
                    </span>
                    @if($accountDeletion->isIdentityVerified())
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <i data-lucide="check" class="w-3 h-3"></i>
                            <span>Identity Verified</span>
                        </span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-0.5">
                    Submitted on {{ $accountDeletion->created_at->format('F d, Y \a\t h:i A') }} ({{ $accountDeletion->created_at->diffForHumans() }})
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($accountDeletion->user)
                <a href="{{ route('admin.customers.show', $accountDeletion->user) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white border border-[#F0E6EC] hover:border-[#F50070] text-gray-700 font-bold text-xs shadow-xs transition-colors">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-[#F50070]"></i>
                    <span>View Customer Profile</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Active Orders Alert Banner (High-Priority Operational Guard) -->
    @if($activeOrders->isNotEmpty())
        <div class="p-5 rounded-3xl bg-amber-50 border-2 border-amber-300 text-amber-900 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-amber-950 font-rubik">
                        Customer Has {{ $activeOrders->count() }} In-Flight Active Order(s)
                    </h3>
                    <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                        In accordance with food fulfillment and order integrity guidelines, account deletion <strong>cannot</strong> be executed while meals are in preparation or out for delivery. These orders must be delivered or cancelled first.
                    </p>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($activeOrders as $activeOrder)
                            <div class="p-2.5 rounded-xl bg-white border border-amber-200 text-xs flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-gray-900">#{{ $activeOrder->order_number }}</span>
                                    <span class="text-[10px] text-gray-400 block">{{ ucfirst(str_replace('_', ' ', $activeOrder->order_status)) }}</span>
                                </div>
                                <span class="font-bold text-[#F50070]">₹{{ number_format($activeOrder->total_amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Details & Matched User (2 cols) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card 1: Request Details -->
            <div class="bg-white rounded-3xl p-6 border border-[#F0E6EC] shadow-sm space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-400 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4 text-[#F50070]"></i>
                    <span>Submitted Information</span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100">
                        <span class="text-[11px] font-bold text-gray-400 block uppercase">Full Name</span>
                        <span class="text-sm font-extrabold text-[#25232A]">{{ $accountDeletion->full_name }}</span>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100">
                        <span class="text-[11px] font-bold text-gray-400 block uppercase">Registered Mobile</span>
                        <span class="text-sm font-extrabold text-[#25232A] font-mono">{{ $accountDeletion->mobile_number }}</span>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100">
                        <span class="text-[11px] font-bold text-gray-400 block uppercase">Email Address</span>
                        <span class="text-sm font-bold text-[#25232A]">{{ $accountDeletion->email ?: 'Not provided' }}</span>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100">
                        <span class="text-[11px] font-bold text-gray-400 block uppercase">Selected Reason</span>
                        <span class="text-sm font-bold text-[#F50070]">{{ $accountDeletion->reason_label }}</span>
                    </div>
                </div>

                <!-- Message -->
                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                    <span class="text-[11px] font-bold text-gray-400 block uppercase mb-1">Additional Message / Note</span>
                    <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-wrap">
                        {{ $accountDeletion->message ?: 'No additional message was submitted by the requester.' }}
                    </p>
                </div>

                <!-- Technical Audit Metadata -->
                <div class="pt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-gray-500">
                    <div>
                        <span class="font-bold text-gray-700">Submission IP:</span>
                        <code class="text-[11px] bg-gray-100 px-2 py-0.5 rounded">{{ $accountDeletion->ip_address ?: 'Unknown' }}</code>
                    </div>
                    <div>
                        <span class="font-bold text-gray-700">Browser / User Agent:</span>
                        <p class="text-[10px] text-gray-400 truncate mt-0.5" title="{{ $accountDeletion->user_agent }}">
                            {{ $accountDeletion->user_agent ?: 'Unknown' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card 2: Matched Customer Account -->
            <div class="bg-white rounded-3xl p-6 border border-[#F0E6EC] shadow-sm space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-400 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-[#F50070]"></i>
                    <span>Linked System User Account</span>
                </h2>

                @if($accountDeletion->user)
                    @php $matchedUser = $accountDeletion->user; @endphp
                    <div class="p-4 rounded-2xl bg-pink-50/40 border border-pink-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-[#FFF3F8] text-[#F50070] font-black text-lg flex items-center justify-center border border-[#F0E6EC]">
                                {{ substr($matchedUser->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-[#25232A]">
                                    {{ $matchedUser->name }} (ID #{{ $matchedUser->id }})
                                </h3>
                                <p class="text-xs text-gray-500 font-mono">{{ $matchedUser->phone }} &bull; {{ $matchedUser->email }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">
                                    Joined: {{ $matchedUser->created_at->format('M d, Y') }} &bull; Role: {{ ucfirst($matchedUser->role) }}
                                </p>
                            </div>
                        </div>
                        <div>
                            @if($matchedUser->is_active)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Active Account
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-300">
                                    Deactivated / Anonymized
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- User Stats Summary -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 rounded-2xl bg-gray-50 border border-gray-100 text-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Total Orders</span>
                            <span class="text-base font-black text-[#25232A]">{{ $matchedUser->orders()->count() }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-gray-50 border border-gray-100 text-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Total Spent</span>
                            <span class="text-base font-black text-[#F50070]">₹{{ number_format($matchedUser->orders()->sum('total_amount'), 2) }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-gray-50 border border-gray-100 text-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Saved Addresses</span>
                            <span class="text-base font-black text-[#25232A]">{{ $matchedUser->addresses()->count() }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-gray-50 border border-gray-100 text-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Device Tokens</span>
                            <span class="text-base font-black text-[#25232A]">{{ $matchedUser->deviceTokens()->count() }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-5 rounded-2xl bg-gray-50 border border-gray-200 text-center text-xs text-gray-500">
                        <i data-lucide="user-x" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                        <p class="font-bold text-gray-700">No Registered User Account Matched</p>
                        <p class="text-gray-400 mt-1">
                            No active user was found with mobile number <code>{{ $accountDeletion->mobile_number }}</code> or email <code>{{ $accountDeletion->email }}</code> at the time of submission.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Card 3: Audit Trail -->
            <div class="bg-white rounded-3xl p-6 border border-[#F0E6EC] shadow-sm space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-400 flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-[#F50070]"></i>
                    <span>Activity & Audit Trail</span>
                </h2>

                <div class="space-y-3">
                    @forelse($auditLogs as $log)
                        <div class="p-3 rounded-xl bg-gray-50 border border-gray-100 text-xs flex items-center justify-between">
                            <div>
                                <span class="font-bold text-gray-900">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span>
                                <span class="text-gray-400 text-[10px] block">
                                    By: {{ $log->user ? $log->user->name : 'System/Public' }} &bull; IP: {{ $log->ip_address }}
                                </span>
                            </div>
                            <span class="text-[11px] text-gray-400 font-mono">{{ $log->created_at->format('M d, h:i A') }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">No previous audit logs recorded for this request.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Column: Verification, Status & Execution (1 col) -->
        <div class="space-y-6">

            <!-- Card 4: Identity Verification -->
            <div class="bg-white rounded-3xl p-6 border border-[#F0E6EC] shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-[#25232A] flex items-center gap-2 font-rubik">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#F50070]"></i>
                    <span>Identity Verification</span>
                </h3>

                <p class="text-xs text-gray-500 leading-relaxed">
                    Verify that the requester is the true account holder. Identity verification must be conducted and documented before permanent account deletion can be executed.
                </p>

                @if($accountDeletion->isIdentityVerified())
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs space-y-2">
                        <div class="flex items-center gap-2 font-bold">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>Verified on {{ $accountDeletion->identity_verified_at->format('M d, Y \a\t h:i A') }}</span>
                        </div>
                        @if($accountDeletion->identity_verification_method)
                            <div class="text-[11px] text-emerald-700">
                                <strong>Method:</strong> {{ $accountDeletion->verification_method_label }}
                            </div>
                        @endif
                    </div>

                    @if(!$accountDeletion->isCompleted())
                        <form action="{{ route('admin.account-deletions.clear-verification', $accountDeletion) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-rose-50 text-gray-600 hover:text-rose-600 text-xs font-bold transition-colors cursor-pointer border border-gray-200 hover:border-rose-200">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                <span>Revoke / Clear Verification</span>
                            </button>
                        </form>
                    @endif
                @else
                    <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                        <div class="flex items-center gap-2 font-bold">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600"></i>
                            <span>Identity Not Yet Verified</span>
                        </div>
                        <p class="text-[11px] text-amber-700 mt-1">
                            An administrator must verify the requester's identity through one of the approved channels below.
                        </p>
                    </div>

                    @if(!$accountDeletion->isCompleted())
                        <form action="{{ route('admin.account-deletions.verify', $accountDeletion) }}" method="POST" class="space-y-3 pt-2">
                            @csrf

                            <div>
                                <label for="verification_method" class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Verification Method <span class="text-rose-500">*</span>
                                </label>
                                <select name="verification_method" id="verification_method" required
                                        class="w-full text-xs px-3 py-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                                    <option value="" disabled selected>Select verification method...</option>
                                    @foreach($verificationMethods as $key => $label)
                                        <option value="{{ $key }}" {{ old('verification_method') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="verification_notes" class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Verification Proof & Notes <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="verification_notes" id="verification_notes" rows="2" required
                                          placeholder="e.g. Called customer on registered number, verified last delivery address and recent order details..."
                                          class="w-full text-xs px-3 py-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">{{ old('verification_notes') }}</textarea>
                            </div>

                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors cursor-pointer shadow-xs">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span>Verify & Document Identity</span>
                            </button>
                        </form>
                    @endif
                @endif
            </div>

            <!-- Card 5: Update Status & Notes -->
            <div class="bg-white rounded-3xl p-6 border border-[#F0E6EC] shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-[#25232A] flex items-center gap-2 font-rubik">
                    <i data-lucide="edit-3" class="w-4 h-4 text-[#F50070]"></i>
                    <span>Review & Status Update</span>
                </h3>

                <form action="{{ route('admin.account-deletions.update-status', $accountDeletion) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="status" class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">
                            Request Status
                        </label>
                        <select name="status" id="status" class="w-full text-xs px-3 py-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">
                            <option value="pending" {{ $accountDeletion->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="under_review" {{ $accountDeletion->status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                            <option value="approved" {{ $accountDeletion->status === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $accountDeletion->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div>
                        <label for="admin_notes" class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">
                            Internal Admin Notes
                        </label>
                        <textarea name="admin_notes" id="admin_notes" rows="3"
                                  placeholder="Record verification notes, customer communication, or reasons..."
                                  class="w-full text-xs px-3 py-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] focus:outline-none">{{ old('admin_notes', $accountDeletion->admin_notes) }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#25232A] hover:bg-black text-white font-bold text-xs transition-colors cursor-pointer">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i>
                        <span>Save Status & Notes</span>
                    </button>
                </form>
            </div>

            <!-- Card 6: Safe Deletion & Anonymization Processing -->
            <div class="bg-white rounded-3xl p-6 border-2 {{ $accountDeletion->isCompleted() ? 'border-gray-200' : 'border-rose-200' }} shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-[#25232A] flex items-center gap-2 font-rubik">
                    <i data-lucide="trash-2" class="w-4 h-4 text-rose-600"></i>
                    <span>Execute Account Deletion</span>
                </h3>

                @if($accountDeletion->isCompleted())
                    <div class="p-4 rounded-2xl bg-gray-100 text-gray-700 text-xs space-y-1">
                        <div class="flex items-center gap-1.5 font-bold text-gray-900">
                            <i data-lucide="check-check" class="w-4 h-4 text-emerald-600"></i>
                            <span>Deletion Completed</span>
                        </div>
                        <p class="text-[11px] text-gray-500">
                            Processed on {{ $accountDeletion->completed_at ? $accountDeletion->completed_at->format('M d, Y \a\t h:i A') : 'N/A' }}
                            @if($accountDeletion->reviewer)
                                by {{ $accountDeletion->reviewer->name }}.
                            @endif
                        </p>
                    </div>
                @else
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Executing deletion will safely anonymize user identity, delete saved delivery addresses, revoke all Sanctum API tokens, and purge FCM device tokens. Past financial orders will remain intact for tax compliance.
                    </p>

                    @if(!$accountDeletion->isIdentityVerified())
                        <div class="p-3 rounded-xl bg-rose-50 text-rose-800 text-[11px] font-semibold border border-rose-200">
                            Deletion is locked until identity verification is completed and documented above.
                        </div>
                    @endif

                    @if($activeOrders->isNotEmpty())
                        <div class="p-3 rounded-xl bg-amber-50 text-amber-800 text-[11px] font-semibold border border-amber-200">
                            Deletion is locked while {{ $activeOrders->count() }} active order(s) remain in progress.
                        </div>
                    @endif

                    <form action="{{ route('admin.account-deletions.process', $accountDeletion) }}"
                          method="POST"
                          onsubmit="return confirm('ARE YOU ABSOLUTELY SURE?\n\nThis will revoke all active security tokens, delete saved addresses, delete device push tokens, and permanently anonymize user personal records. This action cannot be undone.');">
                        @csrf
                        <button type="submit"
                                {{ ($activeOrders->isNotEmpty() || !$accountDeletion->isIdentityVerified()) ? 'disabled' : '' }}
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl {{ ($activeOrders->isNotEmpty() || !$accountDeletion->isIdentityVerified()) ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-rose-600 hover:bg-rose-700 text-white shadow-md shadow-rose-600/20 cursor-pointer' }} font-bold text-xs transition-all">
                            <i data-lucide="user-x" class="w-4 h-4"></i>
                            <span>Process & Complete Deletion</span>
                        </button>
                    </form>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
