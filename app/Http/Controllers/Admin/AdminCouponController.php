<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCouponController extends Controller
{
    public function index(Request $request): View
    {
        $query = Coupon::withCount('usages')->latest();

        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . trim($request->search) . '%');
        }

        $coupons = $query->paginate(15)->withQueryString();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'type' => ['required', 'in:percentage,fixed'],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['required', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $coupon = Coupon::create([
            'code' => strtoupper(trim($validated['code'])),
            'type' => $validated['type'],
            'discount_amount' => $validated['discount_amount'],
            'min_order_amount' => $validated['min_order_amount'],
            'max_discount_amount' => $validated['max_discount_amount'] ?? null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'per_customer_limit' => $validated['per_customer_limit'],
            'start_date' => $validated['start_date'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record('coupon_created', 'Coupon', $coupon->id, ['code' => $coupon->code]);

        return redirect()->route('admin.coupons.index')->with('success', "Coupon '{$coupon->code}' created successfully.");
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code,' . $coupon->id],
            'type' => ['required', 'in:percentage,fixed'],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['required', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active');

        $coupon->update($validated);

        AuditLog::record('coupon_updated', 'Coupon', $coupon->id, ['code' => $coupon->code]);

        return redirect()->route('admin.coupons.index')->with('success', "Coupon '{$coupon->code}' updated successfully.");
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $code = $coupon->code;
        $coupon->delete();

        AuditLog::record('coupon_deleted', 'Coupon', $coupon->id, ['code' => $code]);

        return redirect()->route('admin.coupons.index')->with('success', "Coupon '{$code}' deleted successfully.");
    }
}
