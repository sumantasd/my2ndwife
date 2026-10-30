<?php

namespace App\Services;

use App\Jobs\SendFirebasePushNotificationJob;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NotificationDispatcherService
{
    /**
     * Deduplication helper using atomic cache lock to prevent duplicate notifications
     * from concurrent callbacks, model listeners, or rapid status transitions.
     */
    protected function shouldDispatch(int $orderId, string $eventType): bool
    {
        $cacheKey = "fcm_dedup_{$orderId}_{$eventType}";
        return Cache::add($cacheKey, true, 45);
    }

    /**
     * Resolve user ID for customer push, falling back to matching phone number
     */
    protected function resolveCustomerUserId(Order $order): ?int
    {
        if ($order->user_id) {
            return $order->user_id;
        }

        if (!empty($order->customer_phone)) {
            $matchedUser = User::findByPhone($order->customer_phone);
            if ($matchedUser) {
                return $matchedUser->id;
            }
        }

        return null;
    }

    /**
     * Dispatch notifications when an order is placed (COD or verified Online)
     */
    public function dispatchOrderPlaced(Order $order): void
    {
        if (!$this->shouldDispatch($order->id, 'order_placed')) {
            Log::info("NotificationDispatcherService: Duplicate order_placed suppressed for order #{$order->order_number}");
            return;
        }

        $placeholders = [
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'amount' => number_format((float) $order->total_amount, 2),
        ];

        $data = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'deep_link' => route('order.track', $order->tracking_token),
            'type' => 'order_placed',
        ];

        // 1. Notify Customer (if registered user or matched phone)
        $customerId = $this->resolveCustomerUserId($order);
        if ($customerId) {
            SendFirebasePushNotificationJob::dispatch('user', $customerId, 'order_placed', $placeholders, $data);
        }

        // 2. Notify Admin
        SendFirebasePushNotificationJob::dispatch('role', 'admin', 'admin_new_order', $placeholders, $data);
    }

    /**
     * Dispatch notifications when online payment succeeds
     */
    public function dispatchPaymentSuccessful(Order $order, float $amount): void
    {
        if (!$this->shouldDispatch($order->id, 'payment_successful')) {
            Log::info("NotificationDispatcherService: Duplicate payment_successful suppressed for order #{$order->order_number}");
            return;
        }

        $placeholders = [
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'amount' => number_format($amount, 2),
        ];

        $data = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'deep_link' => route('order.track', $order->tracking_token),
            'type' => 'payment_successful',
        ];

        $customerId = $this->resolveCustomerUserId($order);
        if ($customerId) {
            SendFirebasePushNotificationJob::dispatch('user', $customerId, 'payment_successful', $placeholders, $data);
        }

        SendFirebasePushNotificationJob::dispatch('role', 'admin', 'admin_payment_verified', $placeholders, $data);
    }

    /**
     * Dispatch notification when payment fails
     */
    public function dispatchPaymentFailed(Order $order, ?string $reason = null): void
    {
        if (!$this->shouldDispatch($order->id, 'payment_failed')) {
            return;
        }

        $placeholders = [
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'reason' => $reason ?: 'Transaction declined or interrupted',
        ];

        $data = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'type' => 'payment_failed',
        ];

        $customerId = $this->resolveCustomerUserId($order);
        if ($customerId) {
            SendFirebasePushNotificationJob::dispatch('user', $customerId, 'payment_failed', $placeholders, $data);
        }

        SendFirebasePushNotificationJob::dispatch('role', 'admin', 'admin_payment_failed', $placeholders, $data);
    }

    /**
     * Dispatch notifications on order status transition
     */
    public function dispatchOrderStatusChanged(Order $order, string $newStatus, ?string $oldStatus = null): void
    {
        if ($newStatus === $oldStatus) {
            return;
        }

        if (!$this->shouldDispatch($order->id, "status_{$newStatus}")) {
            Log::info("NotificationDispatcherService: Duplicate status transition ({$newStatus}) suppressed for order #{$order->order_number}");
            return;
        }

        $placeholders = [
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'amount' => number_format((float) $order->total_amount, 2),
            'rider_name' => $order->deliveryPerson?->name ?? 'Delivery Partner',
            'rider_phone' => $order->deliveryPerson?->phone ?? '',
            'reason' => $order->cancellation_reason ?? 'Standard cancellation',
        ];

        $data = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $newStatus,
            'deep_link' => route('order.track', $order->tracking_token),
        ];

        $customerId = $this->resolveCustomerUserId($order);

        switch ($newStatus) {
            case Order::STATUS_ACCEPTED:
                if ($customerId) {
                    SendFirebasePushNotificationJob::dispatch('user', $customerId, 'order_confirmed', $placeholders, $data);
                }
                break;

            case Order::STATUS_PREPARING:
                if ($customerId) {
                    SendFirebasePushNotificationJob::dispatch('user', $customerId, 'order_preparing', $placeholders, $data);
                }
                break;

            case Order::STATUS_READY:
                if ($customerId) {
                    SendFirebasePushNotificationJob::dispatch('user', $customerId, 'food_packed', $placeholders, $data);
                }
                // If delivery order and rider is assigned, send pickup reminder
                if ($order->isDelivery() && $order->delivery_person_id) {
                    SendFirebasePushNotificationJob::dispatch('user', $order->delivery_person_id, 'rider_pickup_reminder', $placeholders, $data);
                } elseif ($order->isDelivery()) {
                    // Broadcast new delivery available to online riders
                    SendFirebasePushNotificationJob::dispatch('role', 'rider', 'rider_new_delivery', $placeholders, $data);
                }
                break;

            case Order::STATUS_OUT_FOR_DELIVERY:
                if ($customerId) {
                    SendFirebasePushNotificationJob::dispatch('user', $customerId, 'out_for_delivery', $placeholders, $data);
                }
                break;

            case Order::STATUS_DELIVERED:
                if ($customerId) {
                    SendFirebasePushNotificationJob::dispatch('user', $customerId, 'order_delivered', $placeholders, $data);
                }
                if ($order->delivery_person_id) {
                    SendFirebasePushNotificationJob::dispatch('user', $order->delivery_person_id, 'rider_delivery_completed', $placeholders, $data);
                }
                break;

            case Order::STATUS_CANCELLED:
                if ($customerId) {
                    SendFirebasePushNotificationJob::dispatch('user', $customerId, 'order_cancelled', $placeholders, $data);
                }
                if ($order->delivery_person_id) {
                    SendFirebasePushNotificationJob::dispatch('user', $order->delivery_person_id, 'rider_assignment_cancelled', $placeholders, $data);
                }
                SendFirebasePushNotificationJob::dispatch('role', 'admin', 'admin_order_cancelled', $placeholders, $data);
                break;
        }
    }

    /**
     * Dispatch notifications when a rider is assigned to an order
     */
    public function dispatchRiderAssigned(Order $order, User $rider): void
    {
        $placeholders = [
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'rider_name' => $rider->name,
            'rider_phone' => $rider->phone ?? '',
            'customer_address' => $order->delivery_address_text ?? 'Customer Address',
        ];

        $customerData = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'deep_link' => route('order.track', $order->tracking_token),
            'type' => 'rider_assigned',
        ];

        $riderData = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'deep_link' => route('delivery.orders.show', $order->id),
            'type' => 'rider_order_assigned',
        ];

        // Notify customer
        if ($order->user_id) {
            SendFirebasePushNotificationJob::dispatch('user', $order->user_id, 'rider_assigned', $placeholders, $customerData);
        }

        // Notify rider
        SendFirebasePushNotificationJob::dispatch('user', $rider->id, 'rider_order_assigned', $placeholders, $riderData);

        // Notify admin
        SendFirebasePushNotificationJob::dispatch('role', 'admin', 'admin_rider_assignment_updated', $placeholders, $customerData);
    }

    /**
     * Dispatch notification when new rider registers
     */
    public function dispatchRiderRegistered(User $rider): void
    {
        $placeholders = [
            'rider_name' => $rider->name,
            'rider_phone' => $rider->phone ?? '',
        ];

        SendFirebasePushNotificationJob::dispatch('role', 'admin', 'admin_rider_registered', $placeholders, [
            'rider_id' => $rider->id,
            'type' => 'rider_registered',
        ]);
    }
}
