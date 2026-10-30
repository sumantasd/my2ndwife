<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountDeletionRequest;
use App\Models\AuditLog;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAccountDeletionController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role:super_admin,admin'),
        ];
    }

    /**
     * Display a listing of account deletion requests.
     */
    public function index(Request $request): View
    {
        $query = AccountDeletionRequest::with(['user', 'reviewer'])->latest();

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $requests = $query->paginate(15)->withQueryString();

        // Statistics Cards
        $counts = [
            'total' => AccountDeletionRequest::count(),
            'pending' => AccountDeletionRequest::where('status', AccountDeletionRequest::STATUS_PENDING)->count(),
            'under_review' => AccountDeletionRequest::where('status', AccountDeletionRequest::STATUS_UNDER_REVIEW)->count(),
            'approved' => AccountDeletionRequest::where('status', AccountDeletionRequest::STATUS_APPROVED)->count(),
            'completed' => AccountDeletionRequest::where('status', AccountDeletionRequest::STATUS_COMPLETED)->count(),
            'rejected' => AccountDeletionRequest::where('status', AccountDeletionRequest::STATUS_REJECTED)->count(),
        ];

        return view('admin.account-deletions.index', compact('requests', 'counts'));
    }

    /**
     * Display detailed information of a specific request.
     */
    public function show(AccountDeletionRequest $accountDeletion): View
    {
        $accountDeletion->load(['user.orders', 'user.addresses', 'user.deviceTokens', 'reviewer']);

        // Check if there are active in-flight orders for the matched user
        $activeOrders = collect();
        if ($accountDeletion->user) {
            $activeOrders = $accountDeletion->user->orders()
                ->whereNotIn('order_status', [Order::STATUS_DELIVERED, Order::STATUS_CANCELLED])
                ->latest()
                ->get();
        } elseif (!empty($accountDeletion->mobile_number)) {
            $foundUser = User::findByPhone($accountDeletion->mobile_number);
            if ($foundUser) {
                $activeOrders = $foundUser->orders()
                    ->whereNotIn('order_status', [Order::STATUS_DELIVERED, Order::STATUS_CANCELLED])
                    ->latest()
                    ->get();
            }
        }

        // Recent audit logs related to this deletion request
        $auditLogs = AuditLog::where('target_type', 'AccountDeletionRequest')
            ->where('target_id', $accountDeletion->id)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.account-deletions.show', [
            'accountDeletion' => $accountDeletion,
            'activeOrders' => $activeOrders,
            'auditLogs' => $auditLogs,
            'verificationMethods' => AccountDeletionRequest::VERIFICATION_METHODS,
        ]);
    }

    /**
     * Update status and admin notes for a request.
     */
    public function updateStatus(Request $request, AccountDeletionRequest $accountDeletion): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,under_review,approved,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $accountDeletion->status;
        $accountDeletion->status = $validated['status'];
        $accountDeletion->admin_notes = $validated['admin_notes'];
        $accountDeletion->reviewed_by = Auth::id();
        $accountDeletion->reviewed_at = now();
        $accountDeletion->save();

        AuditLog::record(
            action: 'account_deletion_status_updated',
            targetType: 'AccountDeletionRequest',
            targetId: $accountDeletion->id,
            details: [
                'reference' => $accountDeletion->reference_number,
                'old_status' => $oldStatus,
                'new_status' => $accountDeletion->status,
                'notes' => $validated['admin_notes'],
                'admin_id' => Auth::id(),
            ]
        );

        return back()->with('success', "Request {$accountDeletion->reference_number} status updated to {$accountDeletion->status_label}.");
    }

    /**
     * Complete and document trustworthy identity verification.
     */
    public function verifyIdentity(Request $request, AccountDeletionRequest $accountDeletion): RedirectResponse
    {
        $validated = $request->validate([
            'verification_method' => ['required', 'string', Rule::in(array_keys(AccountDeletionRequest::VERIFICATION_METHODS))],
            'verification_notes' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'verification_method.required' => 'Please select the verification method used.',
            'verification_notes.required' => 'Please record specific details of how identity was verified.',
            'verification_notes.min' => 'Verification notes must contain at least 10 characters explaining proof of identity.',
        ]);

        $methodLabel = AccountDeletionRequest::VERIFICATION_METHODS[$validated['verification_method']];
        $verificationEntry = "[Identity Verified on " . now()->format('Y-m-d H:i:s') . " by " . Auth::user()->name . "]\n" .
                             "Method: {$methodLabel}\n" .
                             "Proof / Notes: " . trim($validated['verification_notes']);

        $accountDeletion->identity_verified_at = now();
        $accountDeletion->identity_verification_method = $validated['verification_method'];
        $accountDeletion->reviewed_by = Auth::id();
        $accountDeletion->reviewed_at = now();
        $accountDeletion->admin_notes = $verificationEntry . "\n\n" . ($accountDeletion->admin_notes ?? '');
        $accountDeletion->save();

        AuditLog::record(
            action: 'account_deletion_identity_verified',
            targetType: 'AccountDeletionRequest',
            targetId: $accountDeletion->id,
            details: [
                'reference' => $accountDeletion->reference_number,
                'method' => $validated['verification_method'],
                'notes' => $validated['verification_notes'],
                'admin_id' => Auth::id(),
            ]
        );

        return back()->with('success', "Identity successfully verified and documented for {$accountDeletion->reference_number}.");
    }

    /**
     * Clear / revoke identity verification if doubts arise.
     */
    public function clearVerification(Request $request, AccountDeletionRequest $accountDeletion): RedirectResponse
    {
        $accountDeletion->identity_verified_at = null;
        $accountDeletion->identity_verification_method = null;
        $accountDeletion->reviewed_by = Auth::id();
        $accountDeletion->reviewed_at = now();
        $accountDeletion->admin_notes = "[Identity Verification Revoked on " . now()->format('Y-m-d H:i:s') . " by " . Auth::user()->name . "]\n\n" . ($accountDeletion->admin_notes ?? '');
        $accountDeletion->save();

        AuditLog::record(
            action: 'account_deletion_verification_cleared',
            targetType: 'AccountDeletionRequest',
            targetId: $accountDeletion->id,
            details: [
                'reference' => $accountDeletion->reference_number,
                'admin_id' => Auth::id(),
            ]
        );

        return back()->with('info', "Identity verification cleared for {$accountDeletion->reference_number}.");
    }

    /**
     * Execute safe account deletion / anonymization.
     */
    public function processDeletion(Request $request, AccountDeletionRequest $accountDeletion): RedirectResponse
    {
        // Guard 1: Prevent duplicate processing
        if ($accountDeletion->isCompleted()) {
            return back()->with('error', 'This account deletion request has already been completed.');
        }

        // Guard 2: Require identity verification before any permanent deletion
        if (!$accountDeletion->isIdentityVerified()) {
            return back()->with('error', 'Cannot process deletion: Requester identity has not been verified yet. An administrator must complete and document identity verification before permanent deletion can be executed.');
        }

        // Guard 3: Cannot execute rejected requests
        if ($accountDeletion->status === AccountDeletionRequest::STATUS_REJECTED) {
            return back()->with('error', 'Cannot process deletion: This request has been marked as Rejected. It must be re-evaluated and approved before execution.');
        }

        // Determine user
        $user = $accountDeletion->user;
        if (!$user && !empty($accountDeletion->mobile_number)) {
            $user = User::findByPhone($accountDeletion->mobile_number);
            if ($user) {
                $accountDeletion->user_id = $user->id;
            }
        }

        // If user exists, enforce safety checks
        if ($user) {
            // Guard 4: Never delete admin or staff accounts via this flow
            if ($user->isAdmin() || $user->isStaff()) {
                return back()->with('error', 'Administrative or staff accounts cannot be processed for customer deletion.');
            }

            // Guard 5: Check for active in-flight orders
            $activeOrdersCount = $user->orders()
                ->whereNotIn('order_status', [Order::STATUS_DELIVERED, Order::STATUS_CANCELLED])
                ->count();

            if ($activeOrdersCount > 0) {
                return back()->with('error', "Cannot complete deletion: Customer #{$user->id} currently has {$activeOrdersCount} active in-flight order(s). Active orders must be delivered or cancelled first.");
            }

            DB::transaction(function () use ($user, $accountDeletion, $request) {
                $originalPhone = $user->phone;
                $originalEmail = $user->email;

                // 1. Revoke Sanctum API tokens
                $user->tokens()->delete();

                // 2. Delete FCM push notification device tokens
                $user->deviceTokens()->delete();

                // 3. Delete saved delivery addresses
                $user->addresses()->delete();

                // 4. Invalidate web sessions if sessions table exists
                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                }

                // 5. Unsubscribe from marketing newsletters if subscribed
                if (!empty($originalEmail)) {
                    NewsletterSubscriber::where('email', strtolower($originalEmail))->delete();
                }

                // 6. Anonymize user record while keeping financial order integrity
                $user->name = 'Deleted Customer #' . $user->id;
                $user->email = "deleted_user_{$user->id}@my2ndwife.local";
                $user->phone = "deleted_{$user->id}";
                $user->avatar = null;
                $user->is_active = false;
                $user->is_online = false;
                $user->save();

                // 7. Complete the deletion request record
                $accountDeletion->status = AccountDeletionRequest::STATUS_COMPLETED;
                $accountDeletion->completed_at = now();
                $accountDeletion->reviewed_by = Auth::id();
                $accountDeletion->reviewed_at = now();
                if ($request->filled('admin_notes')) {
                    $accountDeletion->admin_notes = trim($request->admin_notes) . "\n\n" . ($accountDeletion->admin_notes ?? '');
                }
                $accountDeletion->save();

                // 8. Audit log
                AuditLog::record(
                    action: 'account_deletion_completed',
                    targetType: 'User',
                    targetId: $user->id,
                    details: [
                        'reference' => $accountDeletion->reference_number,
                        'original_phone' => $originalPhone,
                        'original_email' => $originalEmail,
                        'admin_id' => Auth::id(),
                    ]
                );
            });

            return back()->with('success', "Customer #{$user->id} account has been successfully anonymized, security tokens revoked, and request marked as Completed.");
        }

        // If no registered user account was found matching credentials
        $accountDeletion->status = AccountDeletionRequest::STATUS_COMPLETED;
        $accountDeletion->completed_at = now();
        $accountDeletion->reviewed_by = Auth::id();
        $accountDeletion->reviewed_at = now();
        $accountDeletion->admin_notes = ($accountDeletion->admin_notes ? $accountDeletion->admin_notes . "\n" : '') .
            '[Processed: No registered user account was found matching the provided mobile/email. Request marked as completed.]';
        $accountDeletion->save();

        AuditLog::record(
            action: 'account_deletion_completed_no_user',
            targetType: 'AccountDeletionRequest',
            targetId: $accountDeletion->id,
            details: [
                'reference' => $accountDeletion->reference_number,
                'phone' => $accountDeletion->mobile_number,
                'admin_id' => Auth::id(),
            ]
        );

        return back()->with('success', "Request {$accountDeletion->reference_number} marked as Completed (no registered user account was found).");
    }
}
