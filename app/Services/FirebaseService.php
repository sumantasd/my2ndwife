<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\NotificationLog;
use App\Models\RestaurantSetting;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected const TOKEN_CACHE_KEY = 'firebase_fcm_access_token';
    protected const OAUTH_URL = 'https://oauth2.googleapis.com/token';

    /**
     * Default event templates
     */
    public const DEFAULT_TEMPLATES = [
        // Customer Events
        'order_placed' => [
            'title' => 'Order Received! 🛍️',
            'body' => 'Your order #{order_number} has been received and sent to the kitchen.',
        ],
        'order_confirmed' => [
            'title' => 'Order Confirmed! 👨‍🍳',
            'body' => 'Your order #{order_number} has been accepted and is being prepared.',
        ],
        'payment_successful' => [
            'title' => 'Payment Successful! 💳',
            'body' => 'Payment of ₹{amount} for order #{order_number} was successfully verified.',
        ],
        'payment_failed' => [
            'title' => 'Payment Failed ⚠️',
            'body' => 'Payment for order #{order_number} could not be completed. Please try again.',
        ],
        'order_preparing' => [
            'title' => 'Cooking in Progress 🍳',
            'body' => 'Our chefs have started preparing your meal for order #{order_number}.',
        ],
        'food_packed' => [
            'title' => 'Order Packed & Ready 📦',
            'body' => 'Your food for order #{order_number} is fresh, packed, and awaiting delivery pickup.',
        ],
        'rider_assigned' => [
            'title' => 'Rider Assigned 🛵',
            'body' => '{rider_name} ({rider_phone}) has been assigned to deliver your order #{order_number}.',
        ],
        'out_for_delivery' => [
            'title' => 'Out for Delivery 🚀',
            'body' => 'Your order #{order_number} is on the way! Our delivery partner will reach you shortly.',
        ],
        'order_delivered' => [
            'title' => 'Order Delivered 🎉',
            'body' => 'Enjoy your meal! Order #{order_number} was successfully delivered. Thank you for choosing My 2nd Wife.',
        ],
        'order_cancelled' => [
            'title' => 'Order Cancelled ❌',
            'body' => 'Order #{order_number} has been cancelled. Reason: {reason}.',
        ],
        'refund_updated' => [
            'title' => 'Refund Status Updated 💰',
            'body' => 'Refund update for order #{order_number}: {refund_status}.',
        ],
        'promotional' => [
            'title' => '{promo_title}',
            'body' => '{promo_body}',
        ],

        // Rider Events
        'rider_new_delivery' => [
            'title' => 'New Delivery Available! 🛵',
            'body' => 'Order #{order_number} is ready for pickup at the restaurant kitchen.',
        ],
        'rider_order_assigned' => [
            'title' => 'Order Assigned to You 📋',
            'body' => 'You have been assigned order #{order_number} for delivery to {customer_name}.',
        ],
        'rider_assignment_cancelled' => [
            'title' => 'Assignment Cancelled ℹ️',
            'body' => 'Assignment for order #{order_number} was cancelled or reallocated.',
        ],
        'rider_pickup_reminder' => [
            'title' => 'Pickup Reminder ⏰',
            'body' => 'Order #{order_number} is hot & packed! Please proceed to the kitchen for pickup.',
        ],
        'rider_delivery_completed' => [
            'title' => 'Delivery Completed 🏆',
            'body' => 'Great job! Order #{order_number} was marked delivered successfully.',
        ],

        // Admin Events
        'admin_new_order' => [
            'title' => 'New Order Received! 🔔',
            'body' => 'New order #{order_number} received from {customer_name} (₹{amount}).',
        ],
        'admin_payment_verified' => [
            'title' => 'Verified Payment 💵',
            'body' => 'Verified online payment of ₹{amount} received for order #{order_number}.',
        ],
        'admin_payment_failed' => [
            'title' => 'Payment Failed ⚠️',
            'body' => 'Online payment verification failed for order #{order_number}.',
        ],
        'admin_rider_registered' => [
            'title' => 'New Rider Registered 🛵',
            'body' => 'Delivery partner {rider_name} has registered and is pending approval.',
        ],
        'admin_rider_assignment_updated' => [
            'title' => 'Rider Assignment Updated 🏍️',
            'body' => 'Order #{order_number} assigned to delivery rider {rider_name}.',
        ],
        'admin_order_cancelled' => [
            'title' => 'Order Cancelled ⚠️',
            'body' => 'Order #{order_number} has been cancelled: {reason}.',
        ],
        'admin_low_stock_alert' => [
            'title' => 'Low Stock Alert 📉',
            'body' => 'Item {product_name} is running low on stock.',
        ],
    ];

    /**
     * Get Firebase configuration from DB or env fallback
     */
    public function getCredentials(): array
    {
        $settings = RestaurantSetting::getAll();

        $projectId = $settings['firebase_project_id'] ?? config('services.firebase.project_id') ?: env('FIREBASE_PROJECT_ID');
        $clientEmail = $settings['firebase_client_email'] ?? config('services.firebase.client_email') ?: env('FIREBASE_CLIENT_EMAIL');
        $privateKey = $settings['firebase_private_key'] ?? config('services.firebase.private_key') ?: env('FIREBASE_PRIVATE_KEY');
        $isEnabled = (bool) ($settings['firebase_notifications_enabled'] ?? false);

        // Check if service account JSON file exists on server
        $filePath = storage_path('app/firebase/service-account.json');
        if (File::exists($filePath)) {
            try {
                $json = json_decode(File::get($filePath), true);
                if (is_array($json)) {
                    $projectId = $projectId ?: ($json['project_id'] ?? null);
                    $clientEmail = $clientEmail ?: ($json['client_email'] ?? null);
                    $privateKey = $privateKey ?: ($json['private_key'] ?? null);
                }
            } catch (Exception $e) {
                Log::warning('FirebaseService: Failed reading service-account.json: ' . $e->getMessage());
            }
        }

        // Normalize private key line breaks
        if ($privateKey) {
            $privateKey = str_replace('\\n', "\n", $privateKey);
        }

        return [
            'enabled' => $isEnabled,
            'project_id' => $projectId,
            'client_email' => $clientEmail,
            'private_key' => $privateKey,
        ];
    }

    /**
     * Inspect Firebase configuration status
     */
    public function getConfigurationStatus(): array
    {
        $creds = $this->getCredentials();
        $isConfigured = !empty($creds['project_id']) && !empty($creds['client_email']) && !empty($creds['private_key']);

        $filePath = storage_path('app/firebase/service-account.json');
        $fileLoaded = File::exists($filePath);

        $statusText = 'Not Configured';
        if ($isConfigured) {
            $statusText = $creds['enabled'] ? 'Active & Ready' : 'Configured (Disabled)';
        }

        return [
            'enabled' => $creds['enabled'],
            'configured' => $isConfigured,
            'file_loaded' => $fileLoaded,
            'project_id' => $creds['project_id'],
            'client_email' => $creds['client_email'],
            'has_private_key' => !empty($creds['private_key']),
            'status_label' => $statusText,
        ];
    }

    /**
     * Check if a specific event type is enabled for customer, rider, or admin
     */
    public function isEventEnabled(string $eventType, string $role = 'customer'): bool
    {
        $creds = $this->getCredentials();
        if (!$creds['enabled']) {
            return false;
        }

        $settingKey = match ($role) {
            'rider', 'delivery_person' => 'firebase_rider_events',
            'admin', 'super_admin' => 'firebase_admin_events',
            default => 'firebase_customer_events',
        };

        $enabledEvents = RestaurantSetting::get($settingKey, null);

        // If not set yet, enable all by default
        if ($enabledEvents === null) {
            return true;
        }

        if (is_array($enabledEvents)) {
            return in_array($eventType, $enabledEvents, true);
        }

        return true;
    }

    /**
     * Validate credentials by requesting an OAuth2 token from Google
     */
    public function validateCredentials(?array $customCreds = null): array
    {
        $creds = $customCreds ?: $this->getCredentials();

        if (empty($creds['project_id'])) {
            return ['success' => false, 'error' => 'Firebase Project ID is required.'];
        }
        if (empty($creds['client_email'])) {
            return ['success' => false, 'error' => 'Firebase Client Email is required.'];
        }
        if (empty($creds['private_key'])) {
            return ['success' => false, 'error' => 'Firebase Private Key is required.'];
        }

        try {
            $token = $this->generateAccessToken($creds);
            if ($token) {
                return [
                    'success' => true,
                    'message' => "Successfully connected to Google OAuth2 and validated Project ID: {$creds['project_id']}.",
                ];
            }
            return ['success' => false, 'error' => 'Unable to obtain Google OAuth2 access token.'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Generate or retrieve cached OAuth2 access token for FCM HTTP v1
     */
    public function getAccessToken(): ?string
    {
        $creds = $this->getCredentials();
        if (empty($creds['project_id']) || empty($creds['client_email']) || empty($creds['private_key'])) {
            return null;
        }

        return Cache::remember(self::TOKEN_CACHE_KEY, 3300, function () use ($creds) {
            return $this->generateAccessToken($creds);
        });
    }

    /**
     * Exchange signed JWT assertion for Google OAuth2 Bearer token
     */
    protected function generateAccessToken(array $creds): string
    {
        $clientEmail = $creds['client_email'];
        $privateKey = $creds['private_key'];

        $now = time();
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $claims = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => self::OAUTH_URL,
            'iat' => $now,
            'exp' => $now + 3600,
        ]);

        $base64Header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64Claims = rtrim(strtr(base64_encode($claims), '+/', '-_'), '=');
        $payload = "{$base64Header}.{$base64Claims}";

        $signature = '';
        $binaryKey = openssl_pkey_get_private($privateKey);
        if (!$binaryKey) {
            throw new Exception('Invalid Firebase private key format: OpenSSL could not parse the RSA private key.');
        }

        $signed = openssl_sign($payload, $signature, $binaryKey, OPENSSL_ALGO_SHA256);
        if (!$signed) {
            throw new Exception('Failed to cryptographically sign JWT assertion with private key.');
        }

        $base64Signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        $assertion = "{$payload}.{$base64Signature}";

        $response = Http::asForm()->timeout(10)->post(self::OAUTH_URL, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $assertion,
        ]);

        if (!$response->successful()) {
            $err = $response->json('error_description') ?: $response->json('error') ?: $response->body();
            throw new Exception("Google OAuth2 token exchange rejected: {$err}");
        }

        $token = $response->json('access_token');
        if (empty($token)) {
            throw new Exception('Google OAuth2 response did not contain an access_token.');
        }

        return $token;
    }

    /**
     * Dispatch push notification to a specific FCM device token
     */
    public function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data = [],
        ?string $role = null,
        ?int $userId = null,
        string $eventType = 'custom'
    ): array {
        $creds = $this->getCredentials();

        // Ensure string values in FCM data payload
        $formattedData = [];
        foreach ($data as $k => $v) {
            $formattedData[(string) $k] = is_scalar($v) ? (string) $v : json_encode($v);
        }
        $formattedData['event_type'] = $eventType;

        $log = NotificationLog::create([
            'user_id' => $userId,
            'role' => $role,
            'event_type' => $eventType,
            'title' => $title,
            'body' => $body,
            'data' => $formattedData,
            'device_token' => $token,
            'status' => NotificationLog::STATUS_PENDING,
        ]);

        if (!$creds['enabled']) {
            $log->update([
                'status' => NotificationLog::STATUS_FAILED,
                'error_message' => 'Firebase Push Notifications are globally disabled in Admin Settings.',
            ]);
            return [
                'success' => false,
                'log_id' => $log->id,
                'error' => 'Firebase notifications are disabled.',
            ];
        }

        if (empty($creds['project_id']) || empty($creds['client_email']) || empty($creds['private_key'])) {
            $log->update([
                'status' => NotificationLog::STATUS_FAILED,
                'error_message' => 'Firebase credentials are incomplete or missing on the server.',
            ]);
            return [
                'success' => false,
                'log_id' => $log->id,
                'error' => 'Incomplete Firebase credentials.',
            ];
        }

        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                throw new Exception('Failed to obtain FCM access token.');
            }

            $endpoint = "https://fcm.googleapis.com/v1/projects/{$creds['project_id']}/messages:send";

            $fcmPayload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $formattedData,
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'channel_id' => 'm2w_orders',
                            'default_vibrate_timings' => true,
                            'default_light_settings' => true,
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post($endpoint, $fcmPayload);

            if ($response->successful()) {
                $fcmMessageId = $response->json('name');
                $log->update([
                    'status' => NotificationLog::STATUS_SENT,
                    'fcm_message_id' => $fcmMessageId,
                    'sent_at' => now(),
                    'error_message' => null,
                ]);

                // Update token last used
                DeviceToken::where('token', $token)->update(['last_used_at' => now()]);

                return [
                    'success' => true,
                    'log_id' => $log->id,
                    'fcm_message_id' => $fcmMessageId,
                ];
            }

            // Error response from FCM
            $errorData = $response->json('error') ?? [];
            $errorCode = $errorData['status'] ?? 'UNKNOWN';
            $errorMessage = $errorData['message'] ?? $response->body();

            // Handle invalid or expired tokens (UNREGISTERED, NOT_FOUND)
            if (in_array($errorCode, ['UNREGISTERED', 'NOT_FOUND', 'INVALID_ARGUMENT']) || str_contains($errorMessage, 'Requested entity was not found')) {
                DeviceToken::where('token', $token)->update(['is_active' => false]);
                Log::info("FirebaseService: Deactivated invalid FCM device token: " . substr($token, 0, 15) . "...");
            }

            $log->update([
                'status' => NotificationLog::STATUS_FAILED,
                'error_message' => "FCM [{$errorCode}]: {$errorMessage}",
            ]);

            return [
                'success' => false,
                'log_id' => $log->id,
                'error' => $errorMessage,
            ];

        } catch (Exception $e) {
            $log->update([
                'status' => NotificationLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'log_id' => $log->id,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send notification to all active device tokens of a user
     */
    public function sendToUser(User $user, string $eventType, array $placeholders = [], array $extraData = []): array
    {
        $role = $user->isDeliveryBoy() ? 'rider' : ($user->isAdmin() ? 'admin' : 'customer');

        if (!$this->isEventEnabled($eventType, $role)) {
            return ['success' => false, 'message' => "Event {$eventType} is disabled for {$role}."];
        }

        $template = $this->resolveTemplate($eventType, $placeholders);
        $title = $template['title'];
        $body = $template['body'];

        $activeTokens = $user->activeDeviceTokens()->pluck('token')->toArray();

        if (empty($activeTokens)) {
            // Record pending log so Admin can see notification was triggered
            NotificationLog::create([
                'user_id' => $user->id,
                'role' => $role,
                'event_type' => $eventType,
                'title' => $title,
                'body' => $body,
                'data' => $extraData,
                'status' => NotificationLog::STATUS_PENDING,
                'error_message' => 'User has no registered active device tokens.',
            ]);

            return [
                'success' => false,
                'message' => 'User has no active device tokens registered.',
            ];
        }

        $results = [];
        foreach ($activeTokens as $token) {
            $results[] = $this->sendToToken($token, $title, $body, $extraData, $role, $user->id, $eventType);
        }

        return [
            'success' => true,
            'tokens_count' => count($activeTokens),
            'results' => $results,
        ];
    }

    /**
     * Broadcast notification to all users of a specific role (e.g. All Admins or All Online Riders)
     */
    public function sendToRole(string $role, string $eventType, array $placeholders = [], array $extraData = []): int
    {
        if (!$this->isEventEnabled($eventType, $role)) {
            return 0;
        }

        $query = User::where('is_active', true);

        if ($role === 'rider') {
            $query->where('role', 'delivery_person')->where('is_online', true);
        } elseif ($role === 'admin') {
            $query->whereIn('role', ['super_admin', 'admin', 'order_manager']);
        } else {
            $query->where('role', $role);
        }

        $users = $query->get();
        $dispatched = 0;

        foreach ($users as $user) {
            $res = $this->sendToUser($user, $eventType, $placeholders, $extraData);
            if ($res['success']) {
                $dispatched++;
            }
        }

        return $dispatched;
    }

    /**
     * Retry a previously failed notification
     */
    public function retryNotification(NotificationLog $log): array
    {
        if (empty($log->device_token)) {
            return ['success' => false, 'error' => 'No device token associated with this notification log.'];
        }

        $log->increment('retry_count');
        $log->update(['status' => NotificationLog::STATUS_PENDING]);

        return $this->sendToToken(
            $log->device_token,
            $log->title,
            $log->body,
            $log->data ?: [],
            $log->role,
            $log->user_id,
            $log->event_type
        );
    }

    /**
     * Resolve title and body from configured templates or defaults
     */
    public function resolveTemplate(string $eventType, array $placeholders = []): array
    {
        $customTemplates = RestaurantSetting::get('firebase_templates', []);
        $defaults = self::DEFAULT_TEMPLATES[$eventType] ?? [
            'title' => 'My 2nd Wife Alert',
            'body' => 'You have a new update regarding your order.',
        ];

        $title = $customTemplates[$eventType]['title'] ?? $defaults['title'];
        $body = $customTemplates[$eventType]['body'] ?? $defaults['body'];

        // Replace placeholders
        foreach ($placeholders as $key => $val) {
            $placeholder = '{' . $key . '}';
            $replacement = (string) $val;
            $title = str_replace($placeholder, $replacement, $title);
            $body = str_replace($placeholder, $replacement, $body);
        }

        return [
            'title' => $title,
            'body' => $body,
        ];
    }
}
