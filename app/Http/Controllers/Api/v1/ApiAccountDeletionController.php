<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\AccountDeletionRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApiAccountDeletionController extends Controller
{
    /**
     * Submit an account deletion request from the mobile application.
     * Route: POST /api/v1/account/delete-request
     */
    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'reason' => ['required', 'string', Rule::in(array_keys(AccountDeletionRequest::REASONS))],
            'message' => ['nullable', 'string', 'max:1000'],
            'confirmation' => ['nullable', 'boolean'],
        ]);

        // Check if an open request already exists for this user
        $existing = AccountDeletionRequest::where('user_id', $user->id)
            ->whereIn('status', [AccountDeletionRequest::STATUS_PENDING, AccountDeletionRequest::STATUS_UNDER_REVIEW])
            ->latest()
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'An account deletion request is already pending review for your account.',
                'reference_number' => $existing->reference_number,
                'status' => $existing->status,
                'status_label' => $existing->status_label,
                'web_url' => url('/page/play-store-app-delete'),
            ], 200);
        }

        $referenceNumber = AccountDeletionRequest::generateReferenceNumber();

        $deletionRequest = AccountDeletionRequest::create([
            'reference_number' => $referenceNumber,
            'user_id' => $user->id,
            'full_name' => $user->name,
            'mobile_number' => User::normalizePhoneNumber($user->phone) ?? $user->phone ?? '',
            'email' => $user->email,
            'reason' => $validated['reason'],
            'message' => $validated['message'] ?? null,
            'status' => AccountDeletionRequest::STATUS_PENDING,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        AuditLog::record(
            action: 'api_account_deletion_requested',
            targetType: 'AccountDeletionRequest',
            targetId: $deletionRequest->id,
            details: [
                'reference' => $referenceNumber,
                'user_id' => $user->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Your account deletion request has been submitted successfully and is queued for verification.',
            'reference_number' => $referenceNumber,
            'status' => AccountDeletionRequest::STATUS_PENDING,
            'status_label' => 'Pending',
            'web_url' => url('/page/play-store-app-delete'),
        ], 201);
    }

    /**
     * Check current deletion request status for the authenticated user.
     * Route: GET /api/v1/account/delete-request
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        $latestRequest = AccountDeletionRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'has_pending_request' => $latestRequest && in_array($latestRequest->status, [AccountDeletionRequest::STATUS_PENDING, AccountDeletionRequest::STATUS_UNDER_REVIEW]),
            'latest_request' => $latestRequest ? [
                'reference_number' => $latestRequest->reference_number,
                'status' => $latestRequest->status,
                'status_label' => $latestRequest->status_label,
                'reason' => $latestRequest->reason,
                'reason_label' => $latestRequest->reason_label,
                'submitted_at' => $latestRequest->created_at->toIso8601String(),
                'identity_verified' => $latestRequest->isIdentityVerified(),
            ] : null,
            'web_url' => url('/page/play-store-app-delete'),
        ]);
    }
}
