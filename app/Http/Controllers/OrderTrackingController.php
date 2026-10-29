<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function show(string $token): View
    {
        $order = Order::where('tracking_token', $token)
            ->with(['items.addons', 'statusHistories', 'payment', 'deliveryPerson'])
            ->firstOrFail();

        return view('customer.track-order', compact('order'));
    }

    public function statusJson(string $token): JsonResponse
    {
        $order = Order::where('tracking_token', $token)
            ->with(['payment', 'deliveryPerson'])
            ->firstOrFail();

        return response()->json([
            'order_number' => $order->order_number,
            'status' => $order->order_status,
            'status_label' => $order->status_label,
            'progress_step' => $order->progress_step,
            'badge_color' => $order->status_badge_color,
            'is_paid' => $order->isPaid(),
            'payment_status' => $order->payment?->status,
            'delivery_partner' => $order->deliveryPerson ? [
                'name' => $order->deliveryPerson->name,
                'phone' => $order->deliveryPerson->phone,
            ] : null,
            'updated_at' => $order->updated_at->toIso8601String(),
        ]);
    }
}
