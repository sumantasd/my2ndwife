<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\RestaurantSetting;
use App\Models\User;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function liveAlerts(Request $request): JsonResponse
    {
        $sinceId = (int) $request->input('since_id', 0);
        $initial = filter_var($request->input('initial', false), FILTER_VALIDATE_BOOLEAN);

        $latestOrder = Order::latest('id')->first(['id']);
        $maxId = $latestOrder?->id ?? 0;

        $newOrders = [];
        if (!$initial && $request->has('since_id')) {
            $orders = Order::with(['payment', 'deliveryPerson'])
                ->where('id', '>', $sinceId)
                ->where('order_status', Order::STATUS_PLACED)
                ->latest('id')
                ->limit(10)
                ->get();

            foreach ($orders as $ord) {
                $newOrders[] = [
                    'id' => $ord->id,
                    'order_number' => $ord->order_number,
                    'customer_name' => $ord->customer_name,
                    'customer_phone' => $ord->customer_phone,
                    'order_type' => $ord->order_type,
                    'order_type_label' => $ord->isTakeaway() ? 'Takeaway' : 'Delivery',
                    'order_status' => $ord->order_status,
                    'status_label' => $ord->status_label,
                    'total_formatted' => '₹' . number_format($ord->total_amount, 2),
                    'created_at_human' => $ord->created_at->diffForHumans(),
                    'view_url' => route('admin.orders.show', $ord),
                    'accept_url' => route('admin.orders.status', $ord),
                ];
            }
        }

        // Recent alerts list for header dropdown
        $recentOrders = Order::latest('id')->limit(8)->get(['id', 'order_number', 'customer_name', 'order_type', 'order_status', 'total_amount', 'created_at']);
        $recentAlerts = $recentOrders->map(function ($ord) {
            return [
                'id' => $ord->id,
                'order_number' => $ord->order_number,
                'customer_name' => $ord->customer_name,
                'order_type' => $ord->order_type,
                'order_type_label' => $ord->order_type === 'pickup' ? 'Takeaway' : 'Delivery',
                'order_status' => $ord->order_status,
                'status_label' => $ord->status_label,
                'total_formatted' => '₹' . number_format($ord->total_amount, 2),
                'time' => $ord->created_at->format('h:i A'),
                'view_url' => route('admin.orders.show', $ord),
            ];
        });

        $placedCount = Order::where('order_status', Order::STATUS_PLACED)->count();

        return response()->json([
            'success' => true,
            'latest_id' => $maxId,
            'new_orders' => $newOrders,
            'placed_count' => $placedCount,
            'recent_alerts' => $recentAlerts,
        ]);
    }

    public function index(Request $request): View
    {
        $query = Order::with(['items', 'payment', 'deliveryPerson'])->latest();

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('order_status', $request->status);
        }

        // Payment Method Filter
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->whereHas('payment', function ($q) use ($request) {
                $q->where('payment_method', $request->payment_method);
            });
        }

        // Date Filter
        if ($request->filled('date_range')) {
            match ($request->date_range) {
                'today' => $query->whereDate('created_at', Carbon::today()),
                'yesterday' => $query->whereDate('created_at', Carbon::yesterday()),
                'this_week' => $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]),
                'this_month' => $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year),
                default => null,
            };
        }

        $orders = $query->paginate(20)->withQueryString();

        $statusCounts = [
            'all' => Order::count(),
            Order::STATUS_PLACED => Order::where('order_status', Order::STATUS_PLACED)->count(),
            Order::STATUS_ACCEPTED => Order::where('order_status', Order::STATUS_ACCEPTED)->count(),
            Order::STATUS_PREPARING => Order::where('order_status', Order::STATUS_PREPARING)->count(),
            Order::STATUS_READY => Order::where('order_status', Order::STATUS_READY)->count(),
            Order::STATUS_OUT_FOR_DELIVERY => Order::where('order_status', Order::STATUS_OUT_FOR_DELIVERY)->count(),
            Order::STATUS_DELIVERED => Order::where('order_status', Order::STATUS_DELIVERED)->count(),
            Order::STATUS_CANCELLED => Order::where('order_status', Order::STATUS_CANCELLED)->count(),
        ];

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    public function show(Order $order): View
    {
        $order->load(['items.addons', 'statusHistories.changedBy', 'payment', 'deliveryPerson', 'user']);

        $deliveryRiders = User::where('role', 'delivery_person')->where('is_active', true)->get();

        return view('admin.orders.show', compact('order', 'deliveryRiders'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = $validated['status'];

        // Enforce rider assignment before dispatching delivery order
        if ($order->isDelivery() && $newStatus === Order::STATUS_OUT_FOR_DELIVERY && empty($order->delivery_person_id)) {
            $msg = 'Cannot dispatch delivery order: Please assign a delivery rider before marking Out for Delivery.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }
            return back()->with('error', $msg);
        }

        try {
            $this->orderService->updateOrderStatus(
                $order,
                $newStatus,
                $validated['comment'] ?? null,
                Auth::user()
            );

            $order->refresh();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Order status updated to '{$order->status_label}' successfully.",
                    'order' => [
                        'id' => $order->id,
                        'order_status' => $order->order_status,
                        'status_label' => $order->status_label,
                        'status_badge_color' => $order->status_badge_color,
                        'next_statuses' => $order->getNextAvailableStatuses(),
                    ],
                ]);
            }

            return back()->with('success', "Order status updated to '{$order->status_label}' successfully.");
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancelOrder(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->orderService->cancelOrder($order, $validated['cancellation_reason'], Auth::user());
            $order->refresh();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Order #{$order->order_number} has been cancelled.",
                    'order' => [
                        'id' => $order->id,
                        'order_status' => $order->order_status,
                        'status_label' => $order->status_label,
                        'status_badge_color' => $order->status_badge_color,
                        'next_statuses' => [],
                    ],
                ]);
            }

            return back()->with('success', "Order #{$order->order_number} has been cancelled.");
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function assignRider(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        if ($order->isTakeaway()) {
            $msg = 'Takeaway orders do not require a delivery rider.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'delivery_person_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {
            $this->orderService->assignDeliveryPerson($order, $validated['delivery_person_id'], Auth::user());
            $order->load('deliveryPerson');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Assigned to delivery rider {$order->deliveryPerson->name} successfully.",
                    'rider' => [
                        'id' => $order->deliveryPerson->id,
                        'name' => $order->deliveryPerson->name,
                        'phone' => $order->deliveryPerson->phone,
                    ]
                ]);
            }

            return back()->with('success', "Order assigned to delivery rider successfully.");
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateInternalNotes(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'internal_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->update(['internal_notes' => $validated['internal_notes']]);

        return back()->with('success', 'Internal notes updated.');
    }

    public function printReceipt(Order $order): View
    {
        $order->load(['items.addons', 'payment', 'deliveryPerson']);
        $settings = RestaurantSetting::getAll();

        return view('admin.orders.receipt', compact('order', 'settings'));
    }

    public function printInvoice(Order $order): View
    {
        $order->load(['items.addons', 'payment', 'deliveryPerson']);
        $settings = RestaurantSetting::getAll();

        return view('admin.orders.invoice', compact('order', 'settings'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $fileName = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';

        $query = Order::with(['items', 'payment', 'deliveryPerson'])->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('order_status', $request->status);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return Response::stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Order Number',
                'Date',
                'Customer Name',
                'Customer Phone',
                'Customer Email',
                'Order Type',
                'Status',
                'Payment Method',
                'Payment Status',
                'Subtotal (INR)',
                'Discount (INR)',
                'Delivery Charge (INR)',
                'Packaging Charge (INR)',
                'Tax (INR)',
                'Total Amount (INR)',
                'Delivery Person',
            ]);

            $query->chunk(100, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->order_number,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->customer_name,
                        $order->customer_phone,
                        $order->customer_email,
                        $order->order_type,
                        $order->order_status,
                        $order->payment?->payment_method ?? 'N/A',
                        $order->payment?->status ?? 'pending',
                        $order->subtotal,
                        $order->discount_amount,
                        $order->delivery_charge,
                        $order->packaging_charge,
                        $order->tax_amount,
                        $order->total_amount,
                        $order->deliveryPerson?->name ?? 'Unassigned',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
