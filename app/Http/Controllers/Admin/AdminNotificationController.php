<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function __construct(
        protected FirebaseService $firebaseService
    ) {}

    /**
     * Display Notification History, Delivery Status, and Failed Logs
     */
    public function index(Request $request): View
    {
        $query = NotificationLog::with('user')->latest();

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Role Filter
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Event Type Filter
        if ($request->filled('event_type') && $request->event_type !== 'all') {
            $query->where('event_type', $request->event_type);
        }

        // Search Filter (Order Number, Title, Token, User Name)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhere('device_token', 'like', "%{$search}%")
                    ->orWhere('fcm_message_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Metrics Summary
        $metrics = [
            'total' => NotificationLog::count(),
            'sent' => NotificationLog::where('status', NotificationLog::STATUS_SENT)->count(),
            'failed' => NotificationLog::where('status', NotificationLog::STATUS_FAILED)->count(),
            'pending' => NotificationLog::where('status', NotificationLog::STATUS_PENDING)->count(),
            'active_devices' => DeviceToken::where('is_active', true)->count(),
        ];

        $configStatus = $this->firebaseService->getConfigurationStatus();
        $recentTokens = DeviceToken::with('user')->where('is_active', true)->latest()->take(10)->get();

        return view('admin.notifications.index', compact('logs', 'metrics', 'configStatus', 'recentTokens'));
    }

    /**
     * Send Test Push Notification
     */
    public function sendTest(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['nullable', 'string'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:500'],
        ]);

        $title = $validated['title'];
        $body = $validated['body'];
        $token = $validated['token'] ? trim($validated['token']) : null;
        $userId = $validated['user_id'] ? (int) $validated['user_id'] : null;

        // If no token provided, try to find active token for user, or first active device
        if (empty($token)) {
            if ($userId) {
                $token = DeviceToken::where('user_id', $userId)->where('is_active', true)->latest()->value('token');
            } else {
                $token = DeviceToken::where('is_active', true)->latest()->value('token');
            }
        }

        if (empty($token)) {
            $msg = 'No active FCM device token available. Please register a mobile app device token first or paste an FCM token manually.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'error' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $result = $this->firebaseService->sendToToken(
            $token,
            $title,
            $body,
            ['type' => 'test_notification', 'timestamp' => (string) now()->timestamp],
            'admin',
            $userId,
            'test_notification'
        );

        if ($result['success']) {
            $msg = "Test push notification successfully dispatched! (FCM ID: {$result['fcm_message_id']})";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg, 'fcm_message_id' => $result['fcm_message_id']]);
            }
            return back()->with('success', $msg);
        }

        $errorMsg = 'Failed to send test push notification: ' . ($result['error'] ?? 'Unknown error');
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'error' => $errorMsg], 422);
        }
        return back()->with('error', $errorMsg);
    }

    /**
     * Validate Firebase Connection / Service Account
     */
    public function validateConnection(Request $request): JsonResponse
    {
        $validation = $this->firebaseService->validateCredentials();

        if ($validation['success']) {
            return response()->json([
                'success' => true,
                'message' => $validation['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $validation['error'],
        ], 422);
    }

    /**
     * Retry a single failed notification
     */
    public function retry(NotificationLog $log): RedirectResponse|JsonResponse
    {
        $result = $this->firebaseService->retryNotification($log);

        if ($result['success']) {
            $msg = "Notification #{$log->id} retried successfully.";
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        }

        $errMsg = "Retry failed for Notification #{$log->id}: " . ($result['error'] ?? 'Unknown error');
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => false, 'error' => $errMsg], 422);
        }
        return back()->with('error', $errMsg);
    }

    /**
     * Retry all failed notifications that have a device token
     */
    public function retryAllFailed(): RedirectResponse
    {
        $failedLogs = NotificationLog::where('status', NotificationLog::STATUS_FAILED)
            ->whereNotNull('device_token')
            ->where('retry_count', '<', 3)
            ->take(50)
            ->get();

        if ($failedLogs->isEmpty()) {
            return back()->with('info', 'No eligible failed notifications to retry.');
        }

        $succeeded = 0;
        foreach ($failedLogs as $log) {
            $res = $this->firebaseService->retryNotification($log);
            if ($res['success']) {
                $succeeded++;
            }
        }

        return back()->with('success', "Retried {$failedLogs->count()} notifications. {$succeeded} succeeded.");
    }

    /**
     * Clean old notification logs
     */
    public function clearOldLogs(): RedirectResponse
    {
        $deleted = NotificationLog::where('status', NotificationLog::STATUS_SENT)
            ->where('created_at', '<', now()->subDays(30))
            ->delete();

        return back()->with('success', "Cleaned {$deleted} old notification records.");
    }
}
