@extends('layouts.admin')

@section('title', 'Push Notifications')
@section('header_title', 'Firebase Push Notifications & Logs')

@section('content')
<div class="space-y-6" x-data="adminPushNotifications()">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xl">🔔</span>
                <h3 class="font-extrabold text-[#25232A] text-lg">Firebase Push Notifications</h3>
                @php
                    $badgeClass = match($configStatus['status_severity'] ?? 'gray') {
                        'green' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'red' => 'bg-rose-50 text-rose-700 border-rose-200',
                        default => 'bg-gray-100 text-gray-500 border-gray-200',
                    };
                @endphp
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border {{ $badgeClass }}">
                    {{ $configStatus['status_label'] }}
                </span>
                @if($configStatus['file_loaded'] ?? false)
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                        Service Account JSON Loaded
                    </span>
                @endif
            </div>
            <p class="text-gray-500 text-xs mt-1">
                Real-time delivery status, failed notification triage, live test dispatcher, and Android/iOS device token monitoring.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <button type="button" @click="testModalOpen = true" 
                    class="px-4 py-2.5 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] text-xs flex items-center gap-2 shadow-sm transition-all active:scale-95">
                <i data-lucide="send" class="w-3.5 h-3.5"></i>
                <span>Send Test Push</span>
            </button>

            <button type="button" @click="testConnection()" :disabled="validating"
                    class="px-4 py-2.5 rounded-full bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 text-xs flex items-center gap-2 border border-gray-200 transition-all">
                <span x-show="!validating"><i data-lucide="wifi" class="w-3.5 h-3.5"></i></span>
                <span x-show="validating" class="inline-block w-3.5 h-3.5 border-2 border-[#F50070] border-t-transparent rounded-full animate-spin"></span>
                <span x-text="validating ? 'Testing...' : 'Test Connection'"></span>
            </button>

            <a href="{{ route('admin.settings.index') }}#notifications" 
               class="px-4 py-2.5 rounded-full bg-white text-gray-700 font-bold hover:bg-gray-50 text-xs flex items-center gap-2 border border-[#F0E6EC] shadow-2xs">
                <i data-lucide="settings" class="w-3.5 h-3.5 text-gray-400"></i>
                <span>Configure Credentials</span>
            </a>
        </div>
    </div>

    <!-- Status & Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Logs -->
        <div class="bg-white p-5 rounded-2xl border border-[#F0E6EC] shadow-xs">
            <div class="flex items-center justify-between text-gray-400 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Total Dispatches</span>
                <i data-lucide="bell" class="w-4 h-4 text-gray-400"></i>
            </div>
            <p class="text-2xl font-black text-[#25232A]">{{ number_format($metrics['total']) }}</p>
            <span class="text-[10px] text-gray-400">All recorded push events</span>
        </div>

        <!-- Sent Successfully -->
        <div class="bg-white p-5 rounded-2xl border border-[#F0E6EC] shadow-xs">
            <div class="flex items-center justify-between text-emerald-600 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Delivered</span>
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500"></i>
            </div>
            <p class="text-2xl font-black text-emerald-600">{{ number_format($metrics['sent']) }}</p>
            <span class="text-[10px] text-emerald-700">FCM confirmed deliveries</span>
        </div>

        <!-- Failed -->
        <div class="bg-white p-5 rounded-2xl border border-[#F0E6EC] shadow-xs">
            <div class="flex items-center justify-between text-rose-600 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Failed</span>
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-500"></i>
            </div>
            <p class="text-2xl font-black text-rose-600">{{ number_format($metrics['failed']) }}</p>
            <span class="text-[10px] text-rose-600">Errors, expired tokens, offline</span>
        </div>

        <!-- Pending / In-Queue -->
        <div class="bg-white p-5 rounded-2xl border border-[#F0E6EC] shadow-xs">
            <div class="flex items-center justify-between text-amber-600 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Pending</span>
                <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i>
            </div>
            <p class="text-2xl font-black text-amber-600">{{ number_format($metrics['pending']) }}</p>
            <span class="text-[10px] text-amber-600">Awaiting tokens or retrying</span>
        </div>

        <!-- Active Device Tokens -->
        <div class="bg-white p-5 rounded-2xl border border-[#F0E6EC] shadow-xs col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between text-indigo-600 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Active Devices</span>
                <i data-lucide="smartphone" class="w-4 h-4 text-indigo-500"></i>
            </div>
            <p class="text-2xl font-black text-indigo-600">{{ number_format($metrics['active_devices']) }}</p>
            <span class="text-[10px] text-indigo-600">Android & iOS registered</span>
        </div>
    </div>

    <!-- Live Toast/Alert Message Area -->
    <div x-show="alertMessage" x-cloak class="p-4 rounded-2xl text-xs font-bold flex items-center justify-between gap-3 shadow-xs"
         :class="alertType === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
        <div class="flex items-center gap-2">
            <span x-text="alertType === 'success' ? '✅' : '⚠️'"></span>
            <span x-text="alertMessage"></span>
        </div>
        <button type="button" @click="alertMessage = ''" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-[#F0E6EC] shadow-sm">
        <form action="{{ route('admin.notifications.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block font-bold text-gray-500 mb-1">Search</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search title, order #, user, token..." 
                           class="w-full pl-9 pr-3 py-2 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] text-xs">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block font-bold text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full p-2 rounded-xl border border-[#F0E6EC] bg-white text-xs">
                    <option value="all">All Statuses</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>✅ Delivered</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>❌ Failed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                </select>
            </div>

            <!-- Role Filter -->
            <div>
                <label class="block font-bold text-gray-500 mb-1">Target Audience</label>
                <select name="role" class="w-full p-2 rounded-xl border border-[#F0E6EC] bg-white text-xs">
                    <option value="all">All Roles</option>
                    <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>👤 Customer</option>
                    <option value="rider" {{ request('role') === 'rider' ? 'selected' : '' }}>🛵 Delivery Rider</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>🛡️ Restaurant Admin</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-[#25232A] text-white font-bold hover:bg-black transition-colors text-xs flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Apply</span>
                </button>
                <a href="{{ route('admin.notifications.index') }}" class="py-2 px-3 rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-100 transition-colors text-xs font-bold text-center">
                    Reset
                </a>
            </div>
        </form>

        <!-- Bulk Operations Bar -->
        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between flex-wrap gap-2 text-xs">
            <span class="text-gray-400">
                Showing <strong>{{ $logs->firstItem() ?? 0 }}</strong> to <strong>{{ $logs->lastItem() ?? 0 }}</strong> of <strong>{{ $logs->total() }}</strong> records
            </span>

            <div class="flex items-center gap-2">
                @if($metrics['failed'] > 0)
                    <form action="{{ route('admin.notifications.retry-all') }}" method="POST" onsubmit="return confirm('Retry all failed notifications with available tokens?');">
                        @csrf
                        <button type="submit" class="text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-3 py-1.5 rounded-xl transition-all flex items-center gap-1">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span>Retry All Failed ({{ $metrics['failed'] }})</span>
                        </button>
                    </form>
                @endif

                <form action="{{ route('admin.notifications.clear') }}" method="POST" onsubmit="return confirm('Clean delivered notification records older than 30 days?');">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-gray-500 hover:text-rose-600 px-2.5 py-1.5 rounded-xl border border-gray-200 hover:bg-gray-50 transition-all flex items-center gap-1">
                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                        <span>Clean Old Logs</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification History Table -->
    <div class="bg-white rounded-3xl border border-[#F0E6EC] shadow-sm overflow-hidden text-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-[#F0E6EC] text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                        <th class="p-4">Event & Time</th>
                        <th class="p-4">Recipient</th>
                        <th class="p-4">Notification Message</th>
                        <th class="p-4">Device Token</th>
                        <th class="p-4">Status & FCM ID</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F0E6EC]">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <!-- Event & Time -->
                            <td class="p-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-pink-50 text-[#F50070] border border-pink-100">
                                        {{ str_replace('_', ' ', $log->event_type) }}
                                    </span>
                                    <p class="text-[11px] text-gray-400">{{ $log->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</p>
                                </div>
                            </td>

                            <!-- Recipient -->
                            <td class="p-4">
                                <div class="space-y-0.5">
                                    @if($log->user)
                                        <p class="font-bold text-[#25232A]">{{ $log->user->name }}</p>
                                        <p class="text-[11px] text-gray-400">{{ $log->user->phone ?: $log->user->email }}</p>
                                    @else
                                        <p class="text-gray-400 italic">Broadcast / System</p>
                                    @endif
                                    <span class="inline-block text-[9px] font-bold px-1.5 py-0.2 rounded capitalize {{ $log->role === 'rider' ? 'bg-amber-50 text-amber-700' : ($log->role === 'admin' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700') }}">
                                        {{ $log->role ?? 'general' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Title & Body -->
                            <td class="p-4 max-w-xs sm:max-w-md">
                                <div class="space-y-0.5">
                                    <p class="font-bold text-[#25232A]">{{ $log->title }}</p>
                                    <p class="text-gray-500 line-clamp-2">{{ $log->body }}</p>
                                    @if(!empty($log->data))
                                        <div class="pt-1">
                                            <span class="text-[10px] text-gray-400 font-mono">
                                                Payload: {{ Str::limit(json_encode($log->data), 60) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Token -->
                            <td class="p-4 max-w-[160px]">
                                @if($log->device_token)
                                    <div class="font-mono text-[10px] text-gray-500 truncate" title="{{ $log->device_token }}">
                                        {{ substr($log->device_token, 0, 16) }}...
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-[10px]">None attached</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="p-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border {{ $log->status_badge }}">
                                        <span>{{ ucfirst($log->status) }}</span>
                                    </span>

                                    @if($log->fcm_message_id)
                                        <p class="text-[9px] font-mono text-gray-400 truncate max-w-[140px]" title="{{ $log->fcm_message_id }}">
                                            {{ basename($log->fcm_message_id) }}
                                        </p>
                                    @endif

                                    @if($log->error_message)
                                        <p class="text-[10px] text-rose-600 font-medium line-clamp-2 max-w-[200px]" title="{{ $log->error_message }}">
                                            {{ $log->error_message }}
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="p-4 text-right whitespace-nowrap">
                                @if($log->isFailed() && $log->device_token)
                                    <form action="{{ route('admin.notifications.retry', $log) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="px-3 py-1.5 rounded-xl bg-pink-50 text-[#F50070] hover:bg-[#F50070] hover:text-white font-bold text-[11px] transition-colors border border-pink-200 flex items-center gap-1 ml-auto">
                                            <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                            <span>Retry</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-gray-400">
                                <span class="text-4xl block mb-2">📭</span>
                                <p class="font-bold text-gray-600 text-sm">No notification records found</p>
                                <p class="text-xs text-gray-400 mt-1">Notifications dispatched for customer orders, rider assignments, and admin alerts will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-[#F0E6EC] bg-gray-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Live Send Test Modal -->
    <div x-show="testModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div @click.away="testModalOpen = false" class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full space-y-5 shadow-2xl border border-[#F0E6EC] text-xs">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🚀</span>
                    <h3 class="font-extrabold text-[#25232A] text-base">Send Test Push Notification</h3>
                </div>
                <button type="button" @click="testModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
            </div>

            <form @submit.prevent="dispatchTestPush" class="space-y-4">
                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Target FCM Device Token</label>
                    <input type="text" x-model="testForm.token" placeholder="Paste FCM registration token or select recent device below" 
                           class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070] font-mono text-[11px]">
                    <p class="text-[10px] text-gray-400 mt-1">Leave blank to automatically send to the latest active registered device.</p>
                </div>

                @if($recentTokens->isNotEmpty())
                    <div class="p-3 rounded-2xl bg-gray-50 border border-gray-200 space-y-1.5">
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Or pick a recent registered device:</span>
                        <div class="max-h-28 overflow-y-auto space-y-1">
                            @foreach($recentTokens as $token)
                                <button type="button" @click="testForm.token = '{{ $token->token }}'" 
                                        class="w-full text-left p-1.5 rounded-lg hover:bg-white text-[11px] flex items-center justify-between border border-transparent hover:border-gray-200 transition-colors">
                                    <span class="font-bold text-[#25232A] truncate max-w-[200px]">{{ $token->user->name ?? 'User' }} ({{ $token->device_type }})</span>
                                    <span class="text-[10px] text-gray-400 font-mono">{{ substr($token->token, 0, 10) }}...</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Notification Title <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="testForm.title" required 
                           class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]">
                </div>

                <div>
                    <label class="block font-bold text-[#25232A] mb-1">Notification Body <span class="text-rose-500">*</span></label>
                    <textarea x-model="testForm.body" rows="3" required 
                              class="w-full p-2.5 rounded-xl border border-[#F0E6EC] focus:border-[#F50070]"></textarea>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" @click="testModalOpen = false" class="px-4 py-2 rounded-full text-gray-500 font-bold hover:bg-gray-100">Cancel</button>
                    <button type="submit" :disabled="sendingTest" 
                            class="px-6 py-2 rounded-full bg-[#F50070] text-white font-bold hover:bg-[#D80063] shadow-md transition-all flex items-center gap-1.5">
                        <span x-show="sendingTest" class="inline-block w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-text="sendingTest ? 'Sending...' : 'Send Test Notification'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function adminPushNotifications() {
    return {
        testModalOpen: false,
        validating: false,
        sendingTest: false,
        alertMessage: '',
        alertType: 'success',
        testForm: {
            token: '',
            title: 'Test Notification from My 2nd Wife 🍕',
            body: 'Congratulations! Your Firebase Cloud Messaging connection is working perfectly on Android.',
        },

        async testConnection() {
            this.validating = true;
            this.alertMessage = '';
            try {
                const res = await fetch('{{ route('admin.notifications.validate') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.alertType = 'success';
                    this.alertMessage = data.message || 'Firebase connection validated successfully!';
                } else {
                    this.alertType = 'error';
                    this.alertMessage = data.error || 'Failed to validate Firebase credentials.';
                }
            } catch (err) {
                this.alertType = 'error';
                this.alertMessage = 'Network error while contacting validation endpoint: ' + err.message;
            } finally {
                this.validating = false;
            }
        },

        async dispatchTestPush() {
            this.sendingTest = true;
            this.alertMessage = '';
            try {
                const res = await fetch('{{ route('admin.notifications.test') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.testForm),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.testModalOpen = false;
                    this.alertType = 'success';
                    this.alertMessage = data.message || 'Push notification sent successfully!';
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.alertType = 'error';
                    this.alertMessage = data.error || 'Failed to dispatch push notification.';
                }
            } catch (err) {
                this.alertType = 'error';
                this.alertMessage = 'Error sending test push: ' + err.message;
            } finally {
                this.sendingTest = false;
            }
        }
    };
}
</script>
@endsection
