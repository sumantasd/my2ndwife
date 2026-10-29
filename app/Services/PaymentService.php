<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\RestaurantSetting;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Get Razorpay Public Key ID (from DB setting, config, or env).
     */
    public function getKeyId(): ?string
    {
        $key = RestaurantSetting::get('razorpay_key_id');
        if (empty($key)) {
            $key = config('services.razorpay.key') ?: env('RAZORPAY_KEY_ID');
        }
        return !empty($key) ? trim((string) $key) : null;
    }

    /**
     * Get Razorpay Key Secret (from DB setting, config, or env).
     */
    public function getKeySecret(): ?string
    {
        $secret = RestaurantSetting::get('razorpay_key_secret');
        if (empty($secret)) {
            $secret = config('services.razorpay.secret') ?: env('RAZORPAY_KEY_SECRET');
        }
        return !empty($secret) ? trim((string) $secret) : null;
    }

    /**
     * Get Razorpay Webhook Secret (from DB setting, config, or env).
     */
    public function getWebhookSecret(): ?string
    {
        $secret = RestaurantSetting::get('razorpay_webhook_secret');
        if (empty($secret)) {
            $secret = config('services.razorpay.webhook_secret') ?: env('RAZORPAY_WEBHOOK_SECRET');
        }
        return !empty($secret) ? trim((string) $secret) : null;
    }

    /**
     * Get configured payment mode ('test' or 'live').
     */
    public function getPaymentMode(): string
    {
        return (string) RestaurantSetting::get('payment_mode', 'test');
    }

    /**
     * Check if Razorpay credentials are valid and configured.
     */
    public function isConfigured(): bool
    {
        $keyId = $this->getKeyId();
        $keySecret = $this->getKeySecret();

        if (empty($keyId) || empty($keySecret)) {
            return false;
        }

        if (str_contains($keyId, 'placeholder') || str_contains($keySecret, 'placeholder')) {
            return false;
        }

        return true;
    }

    /**
     * Create an official Razorpay Order via REST API.
     *
     * @return array{success: bool, message?: string, key_id?: string, order_id?: string, amount?: int, currency?: string, name?: string, description?: string, prefill?: array, theme?: array}
     */
    public function createRazorpayOrder(Order $order): array
    {
        $keyId = $this->getKeyId();
        $keySecret = $this->getKeySecret();
        $mode = $this->getPaymentMode();

        if (empty($keyId) || empty($keySecret) || str_contains($keyId, 'placeholder')) {
            Log::error('Razorpay order creation blocked: credentials are not configured or contain placeholder values.');
            return [
                'success' => false,
                'message' => 'Online payment is currently unavailable. Please choose Cash on Delivery or contact restaurant support.',
            ];
        }

        // Validate mode matching
        if ($mode === 'test' && !str_starts_with($keyId, 'rzp_test_')) {
            Log::warning("Razorpay credentials warning: mode is test but Key ID is not a test key ({$keyId}).");
        } elseif ($mode === 'live' && !str_starts_with($keyId, 'rzp_live_')) {
            Log::warning("Razorpay credentials warning: mode is live but Key ID is not a live key ({$keyId}).");
        }

        // Amount in paise (1 INR = 100 paise)
        $amountInPaise = (int) round($order->total_amount * 100);

        if ($amountInPaise <= 0) {
            return [
                'success' => false,
                'message' => 'Order total must be greater than zero to initiate online payment.',
            ];
        }

        try {
            $response = Http::withBasicAuth($keyId, $keySecret)
                ->timeout(15)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => $amountInPaise,
                    'currency' => 'INR',
                    'receipt' => substr((string) $order->order_number, 0, 40),
                    'payment_capture' => 1,
                    'notes' => [
                        'internal_order_id' => (string) $order->id,
                        'order_number' => (string) $order->order_number,
                        'customer_name' => (string) $order->customer_name,
                        'customer_phone' => (string) $order->customer_phone,
                    ],
                ]);

            $data = $response->json();
            $status = $response->status();

            if (($status === 200 || $status === 201) && isset($data['id'])) {
                // Find or create pending payment record
                $payment = $order->payment ?? Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'razorpay',
                    'amount' => $order->total_amount,
                    'currency' => 'INR',
                    'status' => Payment::STATUS_PENDING,
                ]);

                $payment->update([
                    'gateway_order_id' => $data['id'],
                    'gateway_response' => $data,
                ]);

                return [
                    'success' => true,
                    'key_id' => $keyId,
                    'order_id' => $data['id'],
                    'amount' => $amountInPaise,
                    'currency' => 'INR',
                    'name' => RestaurantSetting::get('restaurant_name', 'My 2nd Wife'),
                    'description' => "Order #{$order->order_number}",
                    'prefill' => [
                        'name' => $order->customer_name,
                        'email' => $order->customer_email ?: '',
                        'contact' => $order->customer_phone,
                    ],
                    'theme' => [
                        'color' => '#FF006B',
                    ],
                ];
            }

            $errorMsg = $data['error']['description'] ?? 'Unable to initialize Razorpay checkout order.';
            Log::error('Razorpay order creation API failure: ' . json_encode($data));

            return [
                'success' => false,
                'message' => "Payment gateway error: {$errorMsg}",
            ];
        } catch (Exception $e) {
            Log::error('Razorpay exception during order creation: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to connect to payment gateway: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment signature and confirm the order securely.
     *
     * @return array{success: bool, message: string}
     */
    public function verifyRazorpayPayment(
        Order $order,
        string $razorpayPaymentId,
        string $razorpayOrderId,
        string $razorpaySignature
    ): array {
        $keySecret = $this->getKeySecret();

        if (empty($keySecret)) {
            Log::error("Razorpay verification failed: Key Secret is missing.");
            return [
                'success' => false,
                'message' => 'Payment gateway secret key is not configured.',
            ];
        }

        // Verify that the order ID matches the pending payment
        $payment = $order->payment;
        if ($payment && !empty($payment->gateway_order_id) && $payment->gateway_order_id !== $razorpayOrderId) {
            Log::warning("Razorpay verification mismatch for Order #{$order->order_number}: expected {$payment->gateway_order_id}, got {$razorpayOrderId}");
            return [
                'success' => false,
                'message' => 'Payment verification failed: Razorpay order ID does not match this internal order.',
            ];
        }

        // Calculate HMAC SHA256 Signature
        $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $keySecret);

        if (!hash_equals($expectedSignature, $razorpaySignature)) {
            Log::warning("Razorpay signature mismatch for Order #{$order->order_number}");

            if ($payment) {
                $payment->update([
                    'status' => Payment::STATUS_FAILED,
                    'gateway_response' => array_merge($payment->gateway_response ?? [], [
                        'error' => 'Signature mismatch',
                        'failed_payment_id' => $razorpayPaymentId,
                        'failed_at' => now()->toIso8601String(),
                    ]),
                ]);
            }

            return [
                'success' => false,
                'message' => 'Payment signature verification failed. Your payment could not be confirmed.',
            ];
        }

        // Signature is valid! Confirm the order atomically
        $this->confirmPaidOrder($order, $razorpayPaymentId, $razorpayOrderId, $razorpaySignature, [
            'verified_via' => 'browser_callback',
            'verified_at' => now()->toIso8601String(),
            'signature_verified' => true,
        ]);

        return [
            'success' => true,
            'message' => 'Payment verified successfully! Your order is now confirmed.',
        ];
    }

    /**
     * Atomically confirm a paid order and mark payment as completed.
     * Safe, idempotent, and protected with row locking.
     */
    public function confirmPaidOrder(
        Order $order,
        string $txnId,
        string $gatewayOrderId,
        ?string $signature = null,
        ?array $rawResponse = null
    ): void {
        $justConfirmed = false;

        DB::transaction(function () use ($order, $txnId, $gatewayOrderId, $signature, $rawResponse, &$justConfirmed) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();
            $payment = $lockedOrder->payment()->lockForUpdate()->first();

            if (!$payment) {
                $payment = Payment::create([
                    'order_id' => $lockedOrder->id,
                    'payment_method' => 'razorpay',
                    'amount' => $lockedOrder->total_amount,
                    'currency' => 'INR',
                    'status' => Payment::STATUS_PENDING,
                ]);
            }

            // Idempotency: if already completed and order confirmed, do not re-process
            if ($payment->status === Payment::STATUS_COMPLETED && $lockedOrder->order_status !== Order::STATUS_PENDING_PAYMENT) {
                Log::info("Payment for Order #{$lockedOrder->order_number} was already confirmed. Skipping redundant processing.");
                return;
            }

            // Update payment record to COMPLETED
            $payment->update([
                'payment_method' => 'razorpay',
                'transaction_id' => $txnId,
                'gateway_order_id' => $gatewayOrderId,
                'gateway_signature' => $signature ?: $payment->gateway_signature,
                'status' => Payment::STATUS_COMPLETED,
                'paid_at' => $payment->paid_at ?: now(),
                'gateway_response' => array_merge($payment->gateway_response ?? [], $rawResponse ?? [], [
                    'confirmed_at' => now()->toIso8601String(),
                ]),
            ]);

            // Transition order from pending_payment to placed
            if ($lockedOrder->order_status === Order::STATUS_PENDING_PAYMENT) {
                $lockedOrder->order_status = Order::STATUS_PLACED;
                $lockedOrder->save();

                OrderStatusHistory::create([
                    'order_id' => $lockedOrder->id,
                    'from_status' => Order::STATUS_PENDING_PAYMENT,
                    'to_status' => Order::STATUS_PLACED,
                    'comment' => "Razorpay online payment verified (Payment ID: {$txnId}). Order confirmed.",
                    'changed_by_user_id' => $lockedOrder->user_id,
                    'created_at' => now(),
                ]);

                $justConfirmed = true;
            }

            // Clear customer cart upon verified payment
            try {
                app(CartService::class)->clear();
            } catch (Exception $e) {
                Log::warning('Cart clear error upon payment completion: ' . $e->getMessage());
            }

            AuditLog::record(
                action: 'payment_verified',
                targetType: 'Order',
                targetId: $lockedOrder->id,
                details: [
                    'order_number' => $lockedOrder->order_number,
                    'amount' => $lockedOrder->total_amount,
                    'txn_id' => $txnId,
                    'gateway_order_id' => $gatewayOrderId,
                ]
            );
        });

        // Trigger notifications for payment success and confirmed order placement ONLY if this execution transitioned the order
        if ($justConfirmed) {
            try {
                $refreshedOrder = $order->fresh(['user', 'payment']);
                if ($refreshedOrder && $refreshedOrder->order_status === Order::STATUS_PLACED) {
                    app(NotificationDispatcherService::class)->dispatchPaymentSuccessful($refreshedOrder, (float) $refreshedOrder->total_amount);
                    app(NotificationDispatcherService::class)->dispatchOrderPlaced($refreshedOrder);
                }
            } catch (Exception $e) {
                Log::warning('Failed dispatching payment success notification: ' . $e->getMessage());
            }
        }
    }

    /**
     * Mark payment as failed without placing or confirming the order.
     */
    public function markPaymentFailed(Order $order, string $reason, ?string $errorCode = null, ?array $details = null): void
    {
        $payment = $order->payment;
        if ($payment && $payment->status !== Payment::STATUS_COMPLETED) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'failed_reason' => $reason,
                    'error_code' => $errorCode,
                    'details' => $details,
                    'failed_at' => now()->toIso8601String(),
                ]),
            ]);

            AuditLog::record(
                action: 'payment_failed',
                targetType: 'Order',
                targetId: $order->id,
                details: [
                    'order_number' => $order->order_number,
                    'reason' => $reason,
                    'error_code' => $errorCode,
                ]
            );

            // Notify customer and admin about failed payment
            try {
                app(NotificationDispatcherService::class)->dispatchPaymentFailed($order, $reason);
            } catch (Exception $e) {
                Log::warning('Failed dispatching payment failure notification: ' . $e->getMessage());
            }
        }
    }
}
