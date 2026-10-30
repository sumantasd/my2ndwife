<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountDeletionFormRequest;
use App\Models\AccountDeletionRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountDeletionController extends Controller
{
    /**
     * Display the public Account Deletion Request page.
     * URL: https://my2ndwife.com/page/play-store-app-delete
     */
    public function show(Request $request): View
    {
        $user = Auth::user();

        // Pre-fill defaults from authenticated user if available
        $defaultName = old('full_name', $user?->name ?? '');
        $defaultMobile = old('mobile_number', $user?->phone ?? '');
        $defaultEmail = old('email', $user?->email ?? '');

        return view('customer.account-deletion', [
            'pageTitle' => 'Account Deletion Request — My 2nd Wife',
            'metaDescription' => 'Request deletion of your My 2nd Wife account and associated personal data in accordance with Google Play safety and privacy standards.',
            'defaultName' => $defaultName,
            'defaultMobile' => $defaultMobile,
            'defaultEmail' => $defaultEmail,
            'reasons' => AccountDeletionRequest::REASONS,
        ]);
    }

    /**
     * Handle submission of an account deletion request.
     */
    public function store(AccountDeletionFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $normalizedPhone = User::normalizePhoneNumber($validated['mobile_number']);

        // Determine associated user account safely without leaking existence to public visitors
        $matchedUserId = null;
        if (Auth::check()) {
            $matchedUserId = Auth::id();
        } else {
            // Phone-first matching to prevent account takeover/enumeration by email spoofing
            $matchedUser = User::findByPhone($validated['mobile_number']);
            if ($matchedUser) {
                // If an email was also submitted, verify it does not belong to a different account
                if (!empty($validated['email']) && !empty($matchedUser->email)) {
                    if (strtolower(trim($matchedUser->email)) === strtolower(trim($validated['email']))) {
                        $matchedUserId = $matchedUser->id;
                    }
                    // If email conflicts with phone owner's email, do not auto-link; leave null for admin verification
                } else {
                    $matchedUserId = $matchedUser->id;
                }
            }
        }

        // Prevent spam / duplicate submissions within 24 hours
        $recentRequest = AccountDeletionRequest::where('mobile_number', $normalizedPhone)
            ->whereIn('status', [AccountDeletionRequest::STATUS_PENDING, AccountDeletionRequest::STATUS_UNDER_REVIEW])
            ->where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->first();

        if ($recentRequest) {
            return redirect()->route('account-deletion.show')
                ->with('status_type', 'info')
                ->with('reference_number', $recentRequest->reference_number)
                ->with('message', 'A deletion request for this mobile number is already currently pending and queued for identity review.');
        }

        $referenceNumber = AccountDeletionRequest::generateReferenceNumber();

        $deletionRequest = AccountDeletionRequest::create([
            'reference_number' => $referenceNumber,
            'user_id' => $matchedUserId,
            'full_name' => trim($validated['full_name']),
            'mobile_number' => $normalizedPhone,
            'email' => !empty($validated['email']) ? strtolower(trim($validated['email'])) : null,
            'reason' => $validated['reason'],
            'message' => !empty($validated['message']) ? trim($validated['message']) : null,
            'status' => AccountDeletionRequest::STATUS_PENDING,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        AuditLog::record(
            action: 'account_deletion_requested',
            targetType: 'AccountDeletionRequest',
            targetId: $deletionRequest->id,
            details: [
                'reference' => $referenceNumber,
                'phone' => $normalizedPhone,
                'matched_user_id' => $matchedUserId,
            ]
        );

        return redirect()->route('account-deletion.show')
            ->with('status_type', 'success')
            ->with('reference_number', $referenceNumber)
            ->with('message', 'Your account deletion request has been submitted successfully and is queued for verification.');
    }
}
