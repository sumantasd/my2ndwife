@extends('layouts.app')

@section('title', 'Account Deletion Request — My 2nd Wife')

@section('meta_description', 'Request deletion of your My 2nd Wife account and associated personal data in accordance with Google Play safety and privacy standards.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#FF006B] transition-colors">Home</a>
        <span>/</span>
        <span class="text-gray-900 font-semibold">Account Deletion Request</span>
    </nav>

    <!-- Header Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-[#EFF0F6] shadow-xs mb-8">
        <div class="max-w-2xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#FFF0F6] text-[#FF006B] mb-3">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                <span>Data Privacy & Safety</span>
            </span>
            <h1 class="text-2xl sm:text-4xl font-black font-rubik text-[#1F1F39] tracking-tight">
                Account Deletion Request — My 2nd Wife
            </h1>
            <p class="text-xs sm:text-sm text-[#6E7191] mt-3 leading-relaxed">
                Users of My 2nd Wife can request deletion of their account and associated personal data through this page.
            </p>
        </div>

        <!-- Quick Switcher Tabs -->
        <div class="flex items-center gap-2 mt-6 pt-6 border-t border-gray-100 overflow-x-auto no-scrollbar">
            <a href="{{ route('page.privacy') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 bg-gray-100 text-gray-700 hover:bg-gray-200">
                Privacy Policy
            </a>
            <a href="{{ route('page.terms') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 bg-gray-100 text-gray-700 hover:bg-gray-200">
                Terms & Conditions
            </a>
            <a href="{{ route('page.contact') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 bg-gray-100 text-gray-700 hover:bg-gray-200">
                Contact Support
            </a>
        </div>
    </div>

    <!-- Submission Feedback (Success / Reference Notification) -->
    @if(session('reference_number'))
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border-2 border-emerald-300 rounded-3xl p-6 sm:p-8 mb-8 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md">
                    <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-base sm:text-lg font-black text-emerald-950 font-rubik">
                        {{ session('message') }}
                    </h3>
                    <p class="text-xs sm:text-sm text-emerald-800 mt-1">
                        Please save your reference number for tracking and verification purposes:
                    </p>
                    <div class="mt-4 inline-flex items-center gap-3 bg-white px-4 py-2.5 rounded-2xl border border-emerald-300 shadow-xs">
                        <span class="text-xs uppercase tracking-wider text-gray-500 font-bold">Reference:</span>
                        <code class="text-sm sm:text-base font-extrabold text-[#1F1F39] tracking-wider" id="refNumber">
                            {{ session('reference_number') }}
                        </code>
                        <button type="button"
                                onclick="navigator.clipboard.writeText('{{ session('reference_number') }}'); this.innerText='Copied!';"
                                class="text-[11px] font-bold text-[#FF006B] hover:underline cursor-pointer ml-2">
                            Copy
                        </button>
                    </div>
                    <div class="mt-4 p-3 bg-white/70 rounded-xl text-[11px] text-gray-700 leading-relaxed border border-emerald-200/50">
                        <strong>Important Notice:</strong> Submitting this request does not immediately delete your account. Our support team will review the details and verify your identity to protect your account against unauthorized access or deletion.
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Information & Policy Notice Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFF0F6] shadow-xs mb-8 space-y-4">
        <h2 class="text-base sm:text-lg font-black font-rubik text-[#1F1F39] flex items-center gap-2">
            <i data-lucide="info" class="w-5 h-5 text-[#FF006B]"></i>
            <span>How Account Deletion Works</span>
        </h2>
        <div class="text-xs sm:text-sm text-gray-600 space-y-3 leading-relaxed">
            <p>
                Users of My 2nd Wife can request deletion of their account and associated personal data through this page.
            </p>
            <p>
                After receiving a request, our team will verify the requester's identity and process the request in accordance with our <a href="{{ route('page.privacy') }}" class="text-[#FF006B] font-bold hover:underline">Privacy Policy</a> and applicable legal requirements.
            </p>
            <p>
                Some records may need to be retained where required by law, for security, fraud prevention, or legitimate business obligations. Any retained information will be handled according to our <a href="{{ route('page.privacy') }}" class="text-[#FF006B] font-bold hover:underline">Privacy Policy</a>.
            </p>
        </div>

        <!-- Retention Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="flex items-center gap-2 text-rose-600 font-bold text-xs mb-1">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    <span>Data Deleted / Anonymized</span>
                </div>
                <ul class="text-[11px] text-gray-600 space-y-1 list-disc list-inside">
                    <li>Personal profile (name, registered email, profile photo)</li>
                    <li>Saved delivery addresses and GPS locations</li>
                    <li>Active authentication logins & security tokens</li>
                    <li>Device tokens used for push notifications</li>
                </ul>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="flex items-center gap-2 text-amber-700 font-bold text-xs mb-1">
                    <i data-lucide="archive" class="w-4 h-4"></i>
                    <span>Retained for Legal & Tax Compliance</span>
                </div>
                <ul class="text-[11px] text-gray-600 space-y-1 list-disc list-inside">
                    <li>Past financial transactions & payment receipts</li>
                    <li>Order invoices required under Indian GST / accounting laws</li>
                    <li>Anonymized sales reporting data</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Request Form Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-[#EFF0F6] shadow-xs">
        <h2 class="text-lg sm:text-xl font-black font-rubik text-[#1F1F39] mb-2">
            Submit Your Account Deletion Request
        </h2>
        <p class="text-xs text-[#6E7191] mb-6">
            Please fill in your registered credentials so our support team can locate and verify your account.
        </p>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <span>Please correct the errors below:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('account-deletion.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Full Name -->
            <div>
                <label for="full_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Full Name <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       id="full_name"
                       name="full_name"
                       value="{{ $defaultName }}"
                       required
                       placeholder="Enter your full registered name"
                       class="w-full px-4 py-3 rounded-2xl border {{ $errors->has('full_name') ? 'border-rose-400 bg-rose-50/30' : 'border-[#EFF0F6]' }} focus:border-[#FF006B] focus:ring-2 focus:ring-[#FF006B]/20 text-xs sm:text-sm text-[#1F1F39] transition-all">
                @error('full_name')
                    <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Mobile & Email in 2 columns -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Registered Mobile Number -->
                <div>
                    <label for="mobile_number" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Registered Mobile Number <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="tel"
                               id="mobile_number"
                               name="mobile_number"
                               value="{{ $defaultMobile }}"
                               required
                               placeholder="e.g. 98300 12345 or +91 9830012345"
                               class="w-full px-4 py-3 rounded-2xl border {{ $errors->has('mobile_number') ? 'border-rose-400 bg-rose-50/30' : 'border-[#EFF0F6]' }} focus:border-[#FF006B] focus:ring-2 focus:ring-[#FF006B]/20 text-xs sm:text-sm text-[#1F1F39] transition-all">
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">10-digit Indian mobile number linked to your account.</p>
                    @error('mobile_number')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Registered Email Address (Optional) -->
                <div>
                    <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Registered Email Address <span class="text-gray-400 text-[10px] font-normal">(Optional)</span>
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ $defaultEmail }}"
                           placeholder="name@example.com"
                           class="w-full px-4 py-3 rounded-2xl border {{ $errors->has('email') ? 'border-rose-400 bg-rose-50/30' : 'border-[#EFF0F6]' }} focus:border-[#FF006B] focus:ring-2 focus:ring-[#FF006B]/20 text-xs sm:text-sm text-[#1F1F39] transition-all">
                    <p class="text-[11px] text-gray-400 mt-1">Provide if your account has an email address associated.</p>
                    @error('email')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Reason for Account Deletion -->
            <div>
                <label for="reason" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Reason for Account Deletion <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select id="reason"
                            name="reason"
                            required
                            class="w-full px-4 py-3 rounded-2xl border {{ $errors->has('reason') ? 'border-rose-400 bg-rose-50/30' : 'border-[#EFF0F6]' }} focus:border-[#FF006B] focus:ring-2 focus:ring-[#FF006B]/20 text-xs sm:text-sm text-[#1F1F39] transition-all appearance-none bg-white pr-10">
                        <option value="" disabled {{ old('reason') ? '' : 'selected' }}>Select a reason...</option>
                        @foreach($reasons as $value => $label)
                            <option value="{{ $value }}" {{ old('reason') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>
                @error('reason')
                    <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Additional Message -->
            <div>
                <label for="message" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Additional Message <span class="text-gray-400 text-[10px] font-normal">(Optional)</span>
                </label>
                <textarea id="message"
                          name="message"
                          rows="4"
                          placeholder="Provide any additional details or feedback regarding your account..."
                          class="w-full px-4 py-3 rounded-2xl border {{ $errors->has('message') ? 'border-rose-400 bg-rose-50/30' : 'border-[#EFF0F6]' }} focus:border-[#FF006B] focus:ring-2 focus:ring-[#FF006B]/20 text-xs sm:text-sm text-[#1F1F39] transition-all">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirmation Checkbox -->
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox"
                           name="confirmation"
                           value="1"
                           {{ old('confirmation') ? 'checked' : '' }}
                           required
                           class="mt-1 w-4 h-4 rounded text-[#FF006B] focus:ring-[#FF006B] border-gray-300">
                    <span class="text-xs text-gray-700 leading-relaxed font-medium">
                        I confirm that I am requesting deletion of my My 2nd Wife account and understand that this request will be reviewed before processing.
                    </span>
                </label>
                @error('confirmation')
                    <p class="text-[11px] text-rose-600 mt-2 font-medium pl-7">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
                <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full bg-[#FF006B] hover:bg-[#E60060] text-white font-bold text-sm shadow-md shadow-pink-500/20 hover:shadow-lg hover:shadow-pink-500/30 transition-all cursor-pointer">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Submit Deletion Request</span>
                </button>
                <a href="{{ route('page.privacy') }}" class="text-xs text-gray-500 hover:text-[#FF006B] transition-colors">
                    Read our Privacy Policy &rarr;
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
