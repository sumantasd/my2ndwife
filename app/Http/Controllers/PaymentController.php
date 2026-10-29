<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantSetting;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function showPaymentPage(string $token): View|RedirectResponse
    {
        $order = Order::where('tracking_token', $token)->with(['items', 'payment'])->firstOrFail();

        if ($order->isPaid() || $order->order_status !== Order::STATUS_PENDING_PAYMENT) {
            return redirect()->route('order.track', ['token' => $token])
                ->with('info', "Order #{$order->order_number} is already confirmed.");
        }

        if ($order->order_status === Order::STATUS_CANCELLED) {
            return redirect()->route('order.track', ['token' => $token])
                ->with('error', "Order #{$order->order_number} has been cancelled.");
        }

        $razorpayData = $this->paymentService->createRazorpayOrder($order);

        if (!$razorpayData['success']) {
            return redirect()->route('order.track', ['token' => $token])
                ->with('error', $razorpayData['message'] ?? 'Unable to connect to payment gateway.');
        }

        return view('customer.payment', compact('order', 'razorpayData'));
    }

    public function verifyRazorpay(Request $request, string $token): RedirectResponse|JsonResponse
    {
        $order = Order::where('tracking_token', $token)->firstOrFail();

        $validated = $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $result = $this->paymentService->verifyRazorpayPayment(
            $order,
            $validated['razorpay_payment_id'],
            $validated['razorpay_order_id'],
            $validated['razorpay_signature']
        );

        if ($request->wantsJson() || $request->ajax()) {
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'redirect_url' => route('order.track', ['token' => $order->tracking_token]),
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        if ($result['success']) {
            return redirect()->route('order.track', ['token' => $order->tracking_token])
                ->with('success', 'Payment successful! Your order has been confirmed and sent to kitchen.');
        }

        return redirect()->route('order.payment', ['token' => $order->tracking_token])
            ->with('error', $result['message']);
    }

    public function paymentFailed(Request $request, string $token): JsonResponse
    {
        $order = Order::where('tracking_token', $token)->firstOrFail();

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
            'error_code' => ['nullable', 'string', 'max:100'],
            'razorpay_order_id' => ['nullable', 'string', 'max:100'],
            'razorpay_payment_id' => ['nullable', 'string', 'max:100'],
        ]);

        $reason = $validated['reason'] ?? 'Payment attempt failed or was declined by bank.';
        $this->paymentService->markPaymentFailed(
            $order,
            $reason,
            $validated['error_code'] ?? 'CLIENT_PAYMENT_FAILURE',
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment failure recorded.',
            'retry_url' => route('order.payment', ['token' => $order->tracking_token]),
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $webhookSecret = $this->paymentService->getWebhookSecret();
        $signature = $request->header('X-Razorpay-Signature');

        // Security: Do not allow unverified webhooks to process payments
        if (empty($webhookSecret)) {
            Log::warning('Razorpay webhook received but no webhook secret is configured in Restaurant Settings or .env.');
            return response()->json(['error' => 'Webhook secret is not configured.'], 400);
        }

        if (empty($signature)) {
            Log::warning('Razorpay webhook received without X-Razorpay-Signature header.');
            return response()->json(['error' => 'Signature header missing.'], 400);
        }

        // Verify HMAC-SHA256 signature using raw request body
        $rawPayload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $rawPayload, $webhookSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Razorpay webhook signature mismatch.');
            return response()->json(['error' => 'Invalid signature.'], 400);
        }

        $payload = json_decode($rawPayload, true);
        if (!$payload) {
            return response()->json(['error' => 'Invalid JSON payload.'], 400);
        }

        $event = $payload['event'] ?? null;
        Log::info("Razorpay verified webhook event received: {$event}");

        if ($event === 'payment.captured') {
            $paymentEntity = $payload['payload']['payment']['entity'] ?? [];
            $gatewayOrderId = $paymentEntity['order_id'] ?? null;
            $paymentId = $paymentEntity['id'] ?? null;

            if ($gatewayOrderId && $paymentId) {
                $payment = Payment::where('gateway_order_id', $gatewayOrderId)->first();

                // Fallback: check internal order id in notes
                if (!$payment && !empty($paymentEntity['notes']['internal_order_id'])) {
                    $payment = Payment::where('order_id', $paymentEntity['notes']['internal_order_id'])->first();
                }

                if ($payment && $payment->order) {
                    $this->paymentService->confirmPaidOrder(
                        $payment->order,
                        $paymentId,
                        $gatewayOrderId,
                        null,
                        [
                            'webhook_event' => $event,
                            'webhook_captured_at' => now()->toIso8601String(),
                            'payment_entity' => $paymentEntity,
                        ]
                    );
                }
            }
        } elseif ($event === 'payment.failed') {
            $paymentEntity = $payload['payload']['payment']['entity'] ?? [];
            $gatewayOrderId = $paymentEntity['order_id'] ?? null;
            $errorDesc = $paymentEntity['error_description'] ?? 'Payment failed according to Razorpay webhook.';
            $errorCode = $paymentEntity['error_code'] ?? 'WEBHOOK_PAYMENT_FAILED';

            if ($gatewayOrderId) {
                $payment = Payment::where('gateway_order_id', $gatewayOrderId)->first();
                if ($payment && $payment->order && $payment->status !== Payment::STATUS_COMPLETED) {
                    $this->paymentService->markPaymentFailed(
                        $payment->order,
                        $errorDesc,
                        $errorCode,
                        $paymentEntity
                    );
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
