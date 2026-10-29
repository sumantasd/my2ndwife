<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminDeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', 'delivery_person')
            ->withCount(['assignedDeliveries as active_deliveries_count' => function ($q) {
                $q->whereIn('order_status', [Order::STATUS_READY, Order::STATUS_OUT_FOR_DELIVERY]);
            }])
            ->withCount(['assignedDeliveries as completed_deliveries_count' => function ($q) {
                $q->where('order_status', Order::STATUS_DELIVERED);
            }]);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        $riders = $query->latest()->get();

        return view('admin.delivery.index', compact('riders'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:25'],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
            'vehicle_number' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $rider = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'vehicle_type' => $validated['vehicle_type'] ?? null,
            'vehicle_number' => $validated['vehicle_number'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'delivery_person',
            'is_active' => true,
            'is_online' => true,
        ]);

        AuditLog::record('delivery_person_created', 'User', $rider->id, ['name' => $rider->name]);

        app(\App\Services\NotificationDispatcherService::class)->dispatchRiderRegistered($rider);

        return back()->with('success', "Delivery partner '{$rider->name}' added successfully.");
    }

    public function update(Request $request, User $rider): RedirectResponse
    {
        if ($rider->role !== 'delivery_person') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email,' . $rider->id],
            'phone' => ['required', 'string', 'max:25'],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
            'vehicle_number' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $rider->name = $validated['name'];
        $rider->email = $validated['email'];
        $rider->phone = $validated['phone'];
        $rider->vehicle_type = $validated['vehicle_type'] ?? null;
        $rider->vehicle_number = $validated['vehicle_number'] ?? null;

        if ($request->has('is_active')) {
            $rider->is_active = $request->boolean('is_active');
        }

        if (!empty($validated['password'])) {
            $rider->password = Hash::make($validated['password']);
        }

        $rider->save();

        AuditLog::record('delivery_person_updated', 'User', $rider->id, ['name' => $rider->name]);

        return back()->with('success', "Delivery partner '{$rider->name}' updated successfully.");
    }

    public function toggleStatus(User $rider): RedirectResponse
    {
        if ($rider->role !== 'delivery_person') {
            abort(403);
        }

        $rider->is_active = !$rider->is_active;
        $rider->save();

        AuditLog::record('delivery_person_status_toggled', 'User', $rider->id, ['is_active' => $rider->is_active]);

        return back()->with('success', "Delivery partner status updated to " . ($rider->is_active ? 'Active' : 'Offline') . ".");
    }
}
