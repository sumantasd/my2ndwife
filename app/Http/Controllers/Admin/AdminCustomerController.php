<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer): View
    {
        if ($customer->role !== 'customer') {
            abort(404);
        }

        $customer->load(['addresses']);
        $orders = $customer->orders()->with(['items', 'payment'])->latest()->paginate(10);

        return view('admin.customers.show', compact('customer', 'orders'));
    }

    public function toggleStatus(User $customer): RedirectResponse
    {
        if ($customer->role !== 'customer') {
            abort(403);
        }

        $customer->is_active = !$customer->is_active;
        $customer->save();

        AuditLog::record(
            action: 'customer_status_toggled',
            targetType: 'User',
            targetId: $customer->id,
            details: ['is_active' => $customer->is_active]
        );

        $state = $customer->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Customer account {$state} successfully.");
    }
}
