<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\RestaurantSetting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DeliveryDashboardController extends Controller
{
    /**
     * Delivery Boy Dashboard
     */
    public function dashboard(): View
    {
        $rider = Auth::user();
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Metric Counts (from real database records)
        $activeDeliveriesCount = Order::where('delivery_person_id', $rider->id)
            ->whereIn('order_status', [Order::STATUS_READY, Order::STATUS_OUT_FOR_DELIVERY])
            ->count();

        $inTransitCount = Order::where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_OUT_FOR_DELIVERY)
            ->count();

        $todayCompletedCount = Order::where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_DELIVERED)
            ->whereDate('updated_at', $today)
            ->count();

        $monthCompletedCount = Order::where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_DELIVERED)
            ->where('updated_at', '>=', $startOfMonth)
            ->count();

        $totalCompletedCount = Order::where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_DELIVERED)
            ->count();

        // 2. Earnings Calculation
        $commissionRate = (float) RestaurantSetting::get('delivery_boy_commission_amount', 35.00);
        $todayEarnings = $todayCompletedCount * $commissionRate;
        $monthEarnings = $monthCompletedCount * $commissionRate;

        // 3. Available unassigned orders (if configured or for awareness)
        $availableOrdersCount = Order::where('order_type', Order::TYPE_DELIVERY)
            ->whereNull('delivery_person_id')
            ->whereIn('order_status', [Order::STATUS_READY, Order::STATUS_ACCEPTED, Order::STATUS_PREPARING])
            ->count();

        // 4. Current Spotlight Active Order
        $activeOrder = Order::with(['items.addons', 'payment'])
            ->where('delivery_person_id', $rider->id)
            ->whereIn('order_status', [Order::STATUS_READY, Order::STATUS_OUT_FOR_DELIVERY])
            ->latest()
            ->first();

        // 5. Recent Assignments (latest 5)
        $recentAssignments = Order::with(['items', 'payment'])
            ->where('delivery_person_id', $rider->id)
            ->latest()
            ->take(6)
            ->get();

        return view('delivery.dashboard', compact(
            'rider',
            'activeDeliveriesCount',
            'inTransitCount',
            'todayCompletedCount',
            'monthCompletedCount',
            'totalCompletedCount',
            'todayEarnings',
            'monthEarnings',
            'availableOrdersCount',
            'activeOrder',
            'recentAssignments',
            'commissionRate'
        ));
    }

    /**
     * My Assigned Orders List
     */
    public function orders(Request $request): View
    {
        $rider = Auth::user();
        $tab = $request->query('tab', 'active'); // 'active', 'delivered', 'all'

        $query = Order::with(['items', 'payment'])
            ->where('delivery_person_id', $rider->id);

        if ($tab === 'active') {
            $query->whereIn('order_status', [Order::STATUS_READY, Order::STATUS_OUT_FOR_DELIVERY]);
        } elseif ($tab === 'delivered') {
            $query->where('order_status', Order::STATUS_DELIVERED);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('delivery_address_text', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        $activeCount = Order::where('delivery_person_id', $rider->id)
            ->whereIn('order_status', [Order::STATUS_READY, Order::STATUS_OUT_FOR_DELIVERY])
            ->count();

        $deliveredCount = Order::where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_DELIVERED)
            ->count();

        return view('delivery.orders.index', compact('orders', 'tab', 'activeCount', 'deliveredCount'));
    }

    /**
     * Available Orders to Claim (Self-Claiming)
     */
    public function available(Request $request): View
    {
        $allowClaim = (bool) RestaurantSetting::get('delivery_boy_self_claim_enabled', true);

        $query = Order::with(['items', 'payment'])
            ->where('order_type', Order::TYPE_DELIVERY)
            ->whereNull('delivery_person_id')
            ->whereIn('order_status', [Order::STATUS_READY, Order::STATUS_ACCEPTED, Order::STATUS_PREPARING]);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('delivery_address_text', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        $commissionRate = (float) RestaurantSetting::get('delivery_boy_commission_amount', 35.00);

        return view('delivery.orders.available', compact('orders', 'allowClaim', 'commissionRate'));
    }

    /**
     * Claim an Available Order
     */
    public function claim(Order $order): RedirectResponse
    {
        $rider = Auth::user();

        if ($order->isTakeaway()) {
            return back()->with('error', 'Takeaway orders cannot be claimed for delivery.');
        }

        return DB::transaction(function () use ($order, $rider) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if (!empty($lockedOrder->delivery_person_id)) {
                return back()->with('error', 'This order has already been assigned to another delivery partner.');
            }

            if (in_array($lockedOrder->order_status, [Order::STATUS_DELIVERED, Order::STATUS_CANCELLED])) {
                return back()->with('error', 'This order is no longer eligible for claiming.');
            }

            $lockedOrder->delivery_person_id = $rider->id;
            $lockedOrder->save();

            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'from_status' => $lockedOrder->order_status,
                'to_status' => $lockedOrder->order_status,
                'comment' => "Order claimed by delivery partner {$rider->name} ({$rider->phone}).",
                'changed_by_user_id' => $rider->id,
                'created_at' => now(),
            ]);

            AuditLog::record(
                action: 'delivery_order_claimed',
                targetType: 'Order',
                targetId: $lockedOrder->id,
                details: ['rider_id' => $rider->id, 'order_number' => $lockedOrder->order_number]
            );

            app(\App\Services\NotificationDispatcherService::class)->dispatchRiderAssigned($lockedOrder, $rider);

            return redirect()->route('delivery.orders.show', $lockedOrder)
                ->with('success', "Order #{$lockedOrder->order_number} claimed successfully!");
        });
    }

    /**
     * Single Order Details
     */
    public function show(Order $order): View
    {
        $rider = Auth::user();

        // Security check: Order must belong to this rider, or be unassigned and available
        if ($order->delivery_person_id !== $rider->id && !empty($order->delivery_person_id)) {
            abort(403, 'Unauthorized access: You are not authorized to view another delivery partner\'s order.');
        }

        $order->load(['items.addons', 'payment', 'statusHistories.changedBy']);

        // Google Maps Direction URL
        $mapUrl = null;
        if (!empty($order->delivery_latitude) && !empty($order->delivery_longitude)) {
            $lat = (float) $order->delivery_latitude;
            $lon = (float) $order->delivery_longitude;
            $mapUrl = "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lon}";
        } elseif (!empty($order->delivery_address_text)) {
            $encodedAddress = urlencode($order->delivery_address_text);
            $mapUrl = "https://www.google.com/maps/search/?api=1&query={$encodedAddress}";
        }

        $commissionRate = (float) RestaurantSetting::get('delivery_boy_commission_amount', 35.00);

        return view('delivery.orders.show', compact('order', 'rider', 'mapUrl', 'commissionRate'));
    }

    /**
     * Advance Delivery Status (Accept / Out For Delivery / Delivered)
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $rider = Auth::user();

        // Enforce ownership authorization
        if ($order->delivery_person_id !== $rider->id) {
            abort(403, 'Unauthorized access: You cannot modify delivery status for an order not assigned to you.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string'],
            'delivery_proof_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = $validated['status'];
        $currentStatus = $order->order_status;

        // Allowed transitions for delivery boy:
        // 1. ready -> out_for_delivery ("Pick Up & Start Delivery")
        // 2. out_for_delivery -> delivered ("Confirm Delivery")
        $allowedTransitions = [
            Order::STATUS_READY => [Order::STATUS_OUT_FOR_DELIVERY],
            Order::STATUS_OUT_FOR_DELIVERY => [Order::STATUS_DELIVERED],
        ];

        if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return back()->with('error', "Invalid delivery transition from '{$order->status_label}' to '" . ucfirst(str_replace('_', ' ', $newStatus)) . "'.");
        }

        DB::transaction(function () use ($order, $newStatus, $validated, $rider, $currentStatus) {
            $order->order_status = $newStatus;

            if ($newStatus === Order::STATUS_DELIVERED) {
                $order->delivered_at = now();
                if (!empty($validated['delivery_proof_notes'])) {
                    $order->delivery_proof_notes = trim($validated['delivery_proof_notes']);
                }

                // If COD order, mark payment completed
                if ($order->payment && $order->payment->payment_method === 'cash_on_delivery' && $order->payment->status !== Payment::STATUS_COMPLETED) {
                    $order->payment->update([
                        'status' => Payment::STATUS_COMPLETED,
                        'paid_at' => now(),
                    ]);
                }
            }

            $order->save();

            $comment = match ($newStatus) {
                Order::STATUS_OUT_FOR_DELIVERY => "Order picked up from kitchen and is out for delivery with {$rider->name}.",
                Order::STATUS_DELIVERED => "Order safely delivered to customer by {$rider->name}." . (!empty($validated['delivery_proof_notes']) ? " Note: {$validated['delivery_proof_notes']}" : ""),
                default => "Delivery status updated to {$newStatus} by {$rider->name}.",
            };

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $currentStatus,
                'to_status' => $newStatus,
                'comment' => $comment,
                'changed_by_user_id' => $rider->id,
                'created_at' => now(),
            ]);

            AuditLog::record(
                action: 'delivery_status_updated',
                targetType: 'Order',
                targetId: $order->id,
                details: [
                    'order_number' => $order->order_number,
                    'from' => $currentStatus,
                    'to' => $newStatus,
                    'rider_id' => $rider->id,
                ]
            );
        });

        // Dispatch status transition notification
        app(\App\Services\NotificationDispatcherService::class)->dispatchOrderStatusChanged($order, $newStatus, $currentStatus);

        $statusMessage = match ($newStatus) {
            Order::STATUS_OUT_FOR_DELIVERY => "Order picked up! Drive safely to customer address.",
            Order::STATUS_DELIVERED => "Great job! Order #{$order->order_number} marked as Delivered.",
            default => "Order status updated successfully.",
        };

        return redirect()->route('delivery.orders.show', $order)->with('success', $statusMessage);
    }

    /**
     * Delivery History & Performance
     */
    public function history(Request $request): View
    {
        $rider = Auth::user();
        $dateRange = $request->query('date_range', 'this_month');

        $query = Order::with(['items', 'payment'])
            ->where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_DELIVERED);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('delivery_address_text', 'like', "%{$search}%");
            });
        }

        match ($dateRange) {
            'today' => $query->whereDate('updated_at', Carbon::today()),
            'yesterday' => $query->whereDate('updated_at', Carbon::yesterday()),
            'this_week' => $query->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]),
            'this_month' => $query->whereMonth('updated_at', Carbon::now()->month)->whereYear('updated_at', Carbon::now()->year),
            default => null,
        };

        $orders = $query->latest('updated_at')->paginate(15)->withQueryString();
        $commissionRate = (float) RestaurantSetting::get('delivery_boy_commission_amount', 35.00);

        // Performance Statistics
        $totalDeliveredCount = Order::where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_DELIVERED)
            ->count();

        $filteredEarnings = $orders->total() * $commissionRate;

        return view('delivery.history', compact('orders', 'dateRange', 'commissionRate', 'totalDeliveredCount', 'filteredEarnings'));
    }

    /**
     * Delivery Earnings Overview
     */
    public function earnings(Request $request): View
    {
        $rider = Auth::user();
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $commissionRate = (float) RestaurantSetting::get('delivery_boy_commission_amount', 35.00);

        // Today
        $todayDeliveries = Order::where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_DELIVERED)
            ->whereDate('updated_at', $today)
            ->count();
        $todayEarnings = $todayDeliveries * $commissionRate;

        // This Week
        $weekDeliveries = Order::where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_DELIVERED)
            ->whereBetween('updated_at', [$startOfWeek, Carbon::now()->endOfWeek()])
            ->count();
        $weekEarnings = $weekDeliveries * $commissionRate;

        // This Month
        $monthDeliveries = Order::where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_DELIVERED)
            ->where('updated_at', '>=', $startOfMonth)
            ->count();
        $monthEarnings = $monthDeliveries * $commissionRate;

        // Lifetime
        $lifetimeDeliveries = Order::where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_DELIVERED)
            ->count();
        $lifetimeEarnings = $lifetimeDeliveries * $commissionRate;

        // Breakdown List
        $recentDeliveries = Order::with(['payment'])
            ->where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_DELIVERED)
            ->latest('updated_at')
            ->paginate(15);

        return view('delivery.earnings', compact(
            'rider',
            'commissionRate',
            'todayDeliveries',
            'todayEarnings',
            'weekDeliveries',
            'weekEarnings',
            'monthDeliveries',
            'monthEarnings',
            'lifetimeDeliveries',
            'lifetimeEarnings',
            'recentDeliveries'
        ));
    }

    /**
     * Rider Profile
     */
    public function profile(): View
    {
        $rider = Auth::user();
        $completedCount = Order::where('delivery_person_id', $rider->id)
            ->where('order_status', Order::STATUS_DELIVERED)
            ->count();

        return view('delivery.profile', compact('rider', 'completedCount'));
    }

    /**
     * Update Rider Profile
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $rider = Auth::user();

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:25'],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
            'vehicle_number' => ['nullable', 'string', 'max:30'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $rider->phone = $validated['phone'];
        $rider->vehicle_type = $validated['vehicle_type'] ?? null;
        $rider->vehicle_number = $validated['vehicle_number'] ?? null;

        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], $rider->password)) {
                return back()->withErrors(['current_password' => 'Current password does not match our records.'])->withInput();
            }
            $rider->password = Hash::make($validated['new_password']);
        }

        $rider->save();

        return back()->with('success', 'Profile and vehicle details updated successfully.');
    }

    /**
     * Toggle Online / Duty Status
     */
    public function toggleDuty(): RedirectResponse
    {
        $rider = Auth::user();
        $rider->is_online = !$rider->is_online;
        $rider->save();

        $statusText = $rider->is_online ? 'Online & Ready for Deliveries' : 'Offline (On Break)';
        return back()->with('success', "Status set to: {$statusText}");
    }
}
