<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\RestaurantSetting;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function createOrder(array $input, ?User $user = null): Order
    {
        $deliveryService = app(DeliveryService::class);
        $orderType = $input['order_type'] ?? 'delivery';
        $schedule = $input['order_schedule'] ?? 'asap';

        // 1. Check Restaurant Open Status
        if ($schedule === 'asap') {
            $openStatus = $deliveryService->checkRestaurantOpenStatus();
            if (!$openStatus['can_order']) {
                throw ValidationException::withMessages([
                    'restaurant' => $openStatus['message']
                ]);
            }
        }

        // 2. Validate Order Type Capability & Delivery Radius
        $distanceKm = null;
        $customerLat = isset($input['delivery_latitude']) ? (float) $input['delivery_latitude'] : null;
        $customerLon = isset($input['delivery_longitude']) ? (float) $input['delivery_longitude'] : null;

        if ($orderType === 'delivery') {
            $deliveryEnabled = (bool) RestaurantSetting::get('delivery_enabled', true);
            if (!$deliveryEnabled) {
                throw ValidationException::withMessages([
                    'order_type' => 'Home delivery is currently paused. Please choose Takeaway instead.'
                ]);
            }

            // Check serviceability
            if ($customerLat !== null && $customerLon !== null) {
                $check = $deliveryService->checkServiceability($customerLat, $customerLon);
                if (!$check['serviceable']) {
                    throw ValidationException::withMessages([
                        'delivery_address' => $check['message']
                    ]);
                }
                $distanceKm = $check['distance_km'];
            }
        } else {
            // Takeaway
            $takeawayEnabled = (bool) RestaurantSetting::get('takeaway_enabled', true);
            if (!$takeawayEnabled) {
                throw ValidationException::withMessages([
                    'order_type' => 'Takeaway pickup is currently paused.'
                ]);
            }
        }

        $couponCode = $input['coupon_code'] ?? null;
        if (!$user && !empty($input['customer_phone'])) {
            $user = User::findByPhone($input['customer_phone']);
        }
        $userId = $user?->id;

        // 3. Fetch fresh cart calculations (server-side only)
        $totals = $this->cartService->calculateTotals($couponCode, $orderType, $userId, $distanceKm);

        if (empty($totals['items'])) {
            throw ValidationException::withMessages([
                'cart' => 'Your shopping cart is empty.'
            ]);
        }

        if (!$totals['is_min_order_met']) {
            throw ValidationException::withMessages([
                'cart' => "Minimum order amount of ₹" . number_format($totals['min_order_amount'], 2) . " is required."
            ]);
        }

        // 4. Database Transaction
        $order = DB::transaction(function () use ($input, $totals, $user, $orderType, $customerLat, $customerLon, $distanceKm) {
            $paymentMethod = $input['payment_method'] ?? 'cash_on_delivery';
            $isRazorpay = ($paymentMethod === 'razorpay');
            $initialStatus = $isRazorpay ? Order::STATUS_PENDING_PAYMENT : Order::STATUS_PLACED;

            $order = Order::create([
                'user_id' => $user?->id,
                'customer_name' => $input['customer_name'],
                'customer_email' => $input['customer_email'] ?? $user?->email,
                'customer_phone' => $input['customer_phone'],
                'order_type' => $orderType,
                'order_schedule' => $input['order_schedule'] ?? 'asap',
                'scheduled_at' => !empty($input['scheduled_at']) ? Carbon::parse($input['scheduled_at']) : null,
                'delivery_address_id' => $input['delivery_address_id'] ?? null,
                'delivery_latitude' => $customerLat,
                'delivery_longitude' => $customerLon,
                'delivery_distance_km' => $distanceKm,
                'delivery_address_text' => $input['delivery_address_text'] ?? ($orderType === 'pickup' ? 'Pickup from Restaurant: ' . RestaurantSetting::get('restaurant_address') : null),
                'delivery_instructions' => $input['delivery_instructions'] ?? null,
                'order_status' => $initialStatus,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'packaging_charge' => $totals['packaging_charge'],
                'delivery_charge' => $totals['delivery_charge'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total_amount'],
                'coupon_id' => $totals['coupon']?->id,
                'coupon_code' => $totals['coupon_code'],
                'special_instructions' => $input['special_instructions'] ?? null,
            ]);

            // Save order items & addons
            foreach ($totals['items'] as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['sku'],
                    'variation_id' => $item['variation_id'],
                    'variation_name' => $item['variation_name'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'line_subtotal' => $item['line_subtotal'],
                    'line_tax' => 0.00,
                    'line_total' => $item['line_subtotal'],
                    'special_instructions' => $item['instructions'],
                ]);

                if (!empty($item['addons'])) {
                    foreach ($item['addons'] as $addon) {
                        OrderItemAddon::create([
                            'order_item_id' => $orderItem->id,
                            'addon_id' => $addon['id'],
                            'addon_name' => $addon['name'],
                            'unit_price' => $addon['price'],
                            'line_total' => $addon['price'] * $item['quantity'],
                        ]);
                    }
                }
            }

            // Record initial status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => $initialStatus,
                'comment' => $isRazorpay ? 'Order initialized. Awaiting Razorpay payment completion.' : 'Order placed successfully by customer.',
                'changed_by_user_id' => $user?->id,
                'created_at' => now(),
            ]);

            // Coupon Usage record
            if ($totals['coupon']) {
                CouponUsage::create([
                    'coupon_id' => $totals['coupon']->id,
                    'user_id' => $user?->id,
                    'order_id' => $order->id,
                    'discount_applied' => $totals['discount_amount'],
                ]);
            }

            // Create Payment record
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'amount' => $totals['total_amount'],
                'currency' => RestaurantSetting::get('currency_code', 'INR'),
                'status' => Payment::STATUS_PENDING,
            ]);

            // Audit log
            AuditLog::record(
                action: 'order_created',
                targetType: 'Order',
                targetId: $order->id,
                details: [
                    'order_number' => $order->order_number,
                    'total' => $order->total_amount,
                    'customer' => $order->customer_name,
                    'payment_method' => $paymentMethod,
                    'status' => $initialStatus,
                ]
            );

            // Clear the cart only for COD; Razorpay cart is cleared after payment is verified
            if (!$isRazorpay) {
                $this->cartService->clear();
            }

            return $order;
        });

        // Trigger notifications for completed placement (COD only; Razorpay triggers after verification)
        if ($order->order_status === Order::STATUS_PLACED) {
            app(NotificationDispatcherService::class)->dispatchOrderPlaced($order);
        }

        return $order;
    }

    public function updateOrderStatus(Order $order, string $newStatus, ?string $comment = null, ?User $actor = null): Order
    {
        if (!$order->canTransitionTo($newStatus)) {
            throw new Exception("Invalid order status transition from '{$order->order_status}' to '{$newStatus}'.");
        }

        $oldStatus = $order->order_status;

        $order->order_status = $newStatus;
        if ($newStatus === Order::STATUS_DELIVERED) {
            // If COD, mark payment as completed upon delivery confirmation
            if ($order->payment && $order->payment->payment_method === 'cash_on_delivery' && $order->payment->status !== Payment::STATUS_COMPLETED) {
                $order->payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'paid_at' => now(),
                ]);
            }
        }
        $order->save();

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'comment' => $comment ?: "Order status changed from {$oldStatus} to {$newStatus}.",
            'changed_by_user_id' => $actor?->id,
            'created_at' => now(),
        ]);

        AuditLog::record(
            action: 'order_status_updated',
            targetType: 'Order',
            targetId: $order->id,
            details: [
                'order_number' => $order->order_number,
                'from' => $oldStatus,
                'to' => $newStatus,
                'comment' => $comment,
            ]
        );

        app(NotificationDispatcherService::class)->dispatchOrderStatusChanged($order, $newStatus, $oldStatus);

        return $order;
    }

    public function cancelOrder(Order $order, string $reason, ?User $actor = null): Order
    {
        if ($order->order_status === Order::STATUS_DELIVERED) {
            throw new Exception("A delivered order cannot be cancelled.");
        }

        $oldStatus = $order->order_status;
        $order->order_status = Order::STATUS_CANCELLED;
        $order->cancellation_reason = $reason;
        $order->save();

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $oldStatus,
            'to_status' => Order::STATUS_CANCELLED,
            'comment' => "Order cancelled: {$reason}",
            'changed_by_user_id' => $actor?->id,
            'created_at' => now(),
        ]);

        AuditLog::record(
            action: 'order_cancelled',
            targetType: 'Order',
            targetId: $order->id,
            details: [
                'order_number' => $order->order_number,
                'reason' => $reason,
            ]
        );

        app(NotificationDispatcherService::class)->dispatchOrderStatusChanged($order, Order::STATUS_CANCELLED, $oldStatus);

        return $order;
    }

    public function assignDeliveryPerson(Order $order, int $deliveryPersonId, ?User $actor = null): Order
    {
        $rider = User::where('role', 'delivery_person')->where('is_active', true)->findOrFail($deliveryPersonId);

        $order->delivery_person_id = $rider->id;
        $order->save();

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $order->order_status,
            'to_status' => $order->order_status,
            'comment' => "Assigned to delivery partner {$rider->name} ({$rider->phone}).",
            'changed_by_user_id' => $actor?->id,
            'created_at' => now(),
        ]);

        app(NotificationDispatcherService::class)->dispatchRiderAssigned($order, $rider);

        return $order;
    }
}
