<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {}

    public function profile(): View
    {
        $user = Auth::user();
        $recentOrders = $user->orders()
            ->with(['items', 'payment'])
            ->latest()
            ->limit(5)
            ->get();
        $addresses = $user->addresses()
            ->orderBy('is_default', 'desc')
            ->get();
        $totalOrdersCount = $user->orders()->count();

        return view('customer.account.profile', compact('user', 'recentOrders', 'addresses', 'totalOrdersCount'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:25'],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $phoneDigits = User::extractIndianDigits($validated['phone']);
        if (!$phoneDigits || !preg_match('/^[6-9]\d{9}$/', $phoneDigits)) {
            return back()->withErrors([
                'phone' => 'Please enter a valid 10-digit Indian mobile number.',
            ])->withInput();
        }

        $existingPhoneUser = User::findByPhone($phoneDigits);
        if ($existingPhoneUser && $existingPhoneUser->id !== $user->id) {
            return back()->withErrors([
                'phone' => 'This mobile number is already registered to another account.',
            ])->withInput();
        }

        $user->name = trim($validated['name']);
        $user->phone = '+91 ' . $phoneDigits;

        if (!empty($validated['email'])) {
            $newEmail = strtolower(trim($validated['email']));
            if ($user->email !== $newEmail) {
                $user->email = $newEmail;
                // Invalidate verification timestamp upon email address change
                $user->email_verified_at = null;
            }
        }

        if (!empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function addresses(): View
    {
        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->get();
        return view('customer.account.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:home,work,other'],
            'contact_name' => ['required', 'string', 'max:100'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'delivery_instructions' => ['nullable', 'string', 'max:300'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $isDefault = $request->boolean('is_default') || $user->addresses()->count() === 0;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create(array_merge($validated, ['is_default' => $isDefault]));

        return back()->with('success', 'Address added successfully.');
    }

    public function destroyAddress(Address $address): RedirectResponse
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', 'Address removed successfully.');
    }

    public function orders(): View
    {
        $orders = Auth::user()->orders()
            ->with(['items', 'payment'])
            ->latest()
            ->paginate(10);

        return view('customer.account.orders', compact('orders'));
    }

    public function orderDetails(string $orderNumber): View
    {
        $order = Auth::user()->orders()
            ->where('order_number', $orderNumber)
            ->with(['items.addons', 'statusHistories', 'payment', 'deliveryPerson'])
            ->firstOrFail();

        return view('customer.account.order-details', compact('order'));
    }

    public function cancelOrder(Request $request, string $orderNumber): RedirectResponse
    {
        $order = Auth::user()->orders()
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if ($order->order_status !== Order::STATUS_PLACED) {
            return back()->with('error', 'Orders can only be cancelled while in "Placed" state before the kitchen accepts them.');
        }

        $reason = $request->input('reason', 'Customer requested cancellation.');

        try {
            $this->orderService->cancelOrder($order, $reason, Auth::user());
            return back()->with('success', 'Your order has been cancelled.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reorder(string $orderNumber): RedirectResponse
    {
        $order = Auth::user()->orders()
            ->where('order_number', $orderNumber)
            ->with(['items.addons'])
            ->firstOrFail();

        $addedCount = 0;
        foreach ($order->items as $item) {
            if ($item->product_id) {
                $addonIds = $item->addons->pluck('addon_id')->filter()->toArray();
                $res = $this->cartService->add(
                    productId: $item->product_id,
                    quantity: $item->quantity,
                    variationId: $item->variation_id,
                    addonIds: $addonIds
                );
                if ($res['success']) {
                    $addedCount++;
                }
            }
        }

        if ($addedCount > 0) {
            return redirect()->route('cart.index')->with('success', "Added {$addedCount} items from order #{$order->order_number} to your cart!");
        }

        return back()->with('error', 'Sorry, items from this past order are no longer available in the active menu.');
    }
}
