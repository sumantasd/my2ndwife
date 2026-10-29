<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(): View
    {
        $userId = auth()->id();
        $totals = $this->cartService->calculateTotals(null, 'delivery', $userId);

        return view('customer.cart', compact('totals'));
    }

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'variation_id' => ['nullable', 'integer'],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['integer'],
            'instructions' => ['nullable', 'string', 'max:250'],
        ]);

        $quantity = $validated['quantity'] ?? 1;
        $variationId = $validated['variation_id'] ?? null;
        $addons = $validated['addons'] ?? [];
        $instructions = $validated['instructions'] ?? null;

        $result = $this->cartService->add(
            productId: (int) $validated['product_id'],
            quantity: (int) $quantity,
            variationId: $variationId ? (int) $variationId : null,
            addonIds: $addons,
            instructions: $instructions
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result);
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'item_key' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $result = $this->cartService->updateQuantity($validated['item_key'], (int) $validated['quantity']);

        if ($request->wantsJson() || $request->ajax()) {
            $totals = $this->cartService->calculateTotals(null, 'delivery', auth()->id());
            return response()->json(array_merge($result, ['totals' => $totals]));
        }

        return back()->with('success', 'Cart updated successfully.');
    }

    public function remove(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'item_key' => ['required', 'string'],
        ]);

        $result = $this->cartService->removeItem($validated['item_key']);

        if ($request->wantsJson() || $request->ajax()) {
            $totals = $this->cartService->calculateTotals(null, 'delivery', auth()->id());
            return response()->json(array_merge($result, ['totals' => $totals]));
        }

        return back()->with('success', 'Item removed from your cart.');
    }

    public function applyCoupon(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
        ]);

        $result = $this->cartService->applyCoupon($validated['coupon_code'], auth()->id());

        if ($request->wantsJson() || $request->ajax()) {
            $totals = $this->cartService->calculateTotals(null, 'delivery', auth()->id());
            return response()->json(array_merge($result, ['totals' => $totals]));
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    public function removeCoupon(Request $request): JsonResponse|RedirectResponse
    {
        $this->cartService->removeCoupon();

        if ($request->wantsJson() || $request->ajax()) {
            $totals = $this->cartService->calculateTotals(null, 'delivery', auth()->id());
            return response()->json([
                'success' => true,
                'message' => 'Coupon removed.',
                'totals' => $totals,
            ]);
        }

        return back()->with('info', 'Promo coupon removed.');
    }

    public function summary(): JsonResponse
    {
        $totals = $this->cartService->calculateTotals(null, 'delivery', auth()->id());
        return response()->json([
            'success' => true,
            'count' => $totals['items_count'],
            'subtotal' => $totals['subtotal'],
            'total' => $totals['total_amount'],
            'items' => array_values($totals['items']),
        ]);
    }
}
