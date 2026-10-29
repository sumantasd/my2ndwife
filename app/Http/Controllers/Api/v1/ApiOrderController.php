<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RestaurantSetting;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ApiOrderController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {}

    public function track(string $token): JsonResponse
    {
        $order = Order::where('tracking_token', $token)
            ->with(['items.addons', 'statusHistories', 'payment', 'deliveryPerson'])
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->order_status,
                'status_label' => $order->status_label,
                'progress_step' => $order->progress_step,
                'customer_name' => $order->customer_name,
                'order_type' => $order->order_type,
                'total_amount' => $order->total_amount,
                'delivery_address' => $order->delivery_address_text,
                'delivery_partner' => $order->deliveryPerson ? [
                    'name' => $order->deliveryPerson->name,
                    'phone' => $order->deliveryPerson->phone,
                ] : null,
                'items' => $order->items->map(fn($item) => [
                    'name' => $item->product_name,
                    'variation' => $item->variation_name,
                    'quantity' => $item->quantity,
                    'price' => $item->line_total,
                ]),
                'history' => $order->statusHistories,
            ]
        ]);
    }

    public function myOrders(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = $user->orders()->with(['items', 'payment'])->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'current_page' => $orders->currentPage(),
            'total_pages' => $orders->lastPage(),
        ]);
    }
}
