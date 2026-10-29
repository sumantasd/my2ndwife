<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\RestaurantSetting;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
        protected PaymentService $paymentService
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $allowGuest = (bool) RestaurantSetting::get('allow_guest_checkout', true);

        if (!$user && !$allowGuest) {
            return redirect()->route('login')->with('info', 'Please sign in or create an account to proceed to checkout.');
        }

        $totals = $this->cartService->calculateTotals(null, 'delivery', $user?->id);

        if (empty($totals['items'])) {
            return redirect()->route('menu.index')->with('info', 'Your cart is empty. Please add delicious food items before checkout.');
        }

        $savedAddresses = $user ? $user->addresses()->get() : collect();

        return view('customer.checkout', compact('totals', 'savedAddresses', 'user'));
    }

    public function process(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $orderType = $request->input('order_type', 'delivery');

        // Validation rules
        $rules = [
            'order_type' => ['required', 'in:delivery,pickup'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'order_schedule' => ['required', 'in:asap,scheduled'],
            'scheduled_at' => ['nullable', 'required_if:order_schedule,scheduled', 'date', 'after:now'],
            'payment_method' => ['required', 'in:cash_on_delivery,razorpay'],
            'special_instructions' => ['nullable', 'string', 'max:300'],
        ];

        if ($orderType === 'delivery') {
            $rules['delivery_latitude'] = ['nullable', 'numeric', 'between:-90,90'];
            $rules['delivery_longitude'] = ['nullable', 'numeric', 'between:-180,180'];
            $rules['delivery_address_selection'] = ['required', 'in:saved,new'];
            if ($request->input('delivery_address_selection') === 'saved') {
                $rules['saved_address_id'] = ['required', 'integer', 'exists:addresses,id'];
            } else {
                $rules['address_line_1'] = ['required', 'string', 'max:255'];
                $rules['address_line_2'] = ['nullable', 'string', 'max:255'];
                $rules['city'] = ['required', 'string', 'max:100'];
                $rules['postal_code'] = ['required', 'string', 'max:20'];
                $rules['delivery_instructions'] = ['nullable', 'string', 'max:300'];
            }
        }

        $validated = $request->validate($rules);

        // Verify scheduled order capability
        if ($validated['order_schedule'] === 'scheduled') {
            $scheduledAvailable = (bool) RestaurantSetting::get('scheduled_orders_available', true);
            if (!$scheduledAvailable) {
                throw ValidationException::withMessages([
                    'order_schedule' => 'Scheduled orders are currently unavailable. Please choose Instant delivery.',
                ]);
            }
        }

        // Prepare Order Data
        $orderData = [
            'order_type' => $orderType,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'] ?? $user?->email,
            'order_schedule' => $validated['order_schedule'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'payment_method' => $validated['payment_method'],
            'special_instructions' => $validated['special_instructions'] ?? null,
            'coupon_code' => $this->cartService->getAppliedCoupon()?->code,
        ];

        if ($orderType === 'delivery') {
            if ($validated['delivery_address_selection'] === 'saved') {
                $address = Address::where('user_id', $user?->id)->findOrFail($validated['saved_address_id']);
                $orderData['delivery_address_id'] = $address->id;
                $orderData['delivery_address_text'] = $address->full_address;
                $orderData['delivery_instructions'] = $address->delivery_instructions;
                $orderData['delivery_latitude'] = $request->filled('delivery_latitude') ? (float) $request->delivery_latitude : $address->latitude;
                $orderData['delivery_longitude'] = $request->filled('delivery_longitude') ? (float) $request->delivery_longitude : $address->longitude;
            } else {
                $address2 = !empty($validated['address_line_2']) ? ', ' . $validated['address_line_2'] : '';
                $addressText = $validated['address_line_1'] . $address2 . ', ' . $validated['city'] . ' - ' . $validated['postal_code'];
                $orderData['delivery_address_text'] = $addressText;
                $orderData['delivery_instructions'] = $validated['delivery_instructions'] ?? null;
                $orderData['delivery_latitude'] = $request->filled('delivery_latitude') ? (float) $request->delivery_latitude : null;
                $orderData['delivery_longitude'] = $request->filled('delivery_longitude') ? (float) $request->delivery_longitude : null;

                // Optionally save this address if user is logged in
                if ($user && $request->boolean('save_address_to_profile')) {
                    $newAddr = Address::create([
                        'user_id' => $user->id,
                        'type' => 'home',
                        'contact_name' => $validated['customer_name'],
                        'contact_phone' => $validated['customer_phone'],
                        'address_line_1' => $validated['address_line_1'],
                        'address_line_2' => $validated['address_line_2'] ?? null,
                        'city' => $validated['city'],
                        'postal_code' => $validated['postal_code'],
                        'latitude' => $orderData['delivery_latitude'],
                        'longitude' => $orderData['delivery_longitude'],
                        'delivery_instructions' => $validated['delivery_instructions'] ?? null,
                        'is_default' => $user->addresses()->count() === 0,
                    ]);
                    $orderData['delivery_address_id'] = $newAddr->id;
                }
            }
        }

        try {
            $order = $this->orderService->createOrder($orderData, $user);

            if ($order->payment && $order->payment->payment_method === 'razorpay') {
                $razorpayData = $this->paymentService->createRazorpayOrder($order);

                if (!$razorpayData['success']) {
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $razorpayData['message'] ?? 'Unable to initialize online payment.',
                        ], 422);
                    }
                    return back()->with('error', $razorpayData['message'] ?? 'Unable to initialize online payment.')->withInput();
                }

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'requires_payment' => true,
                        'order_token' => $order->tracking_token,
                        'order_number' => $order->order_number,
                        'payment_url' => route('order.payment', ['token' => $order->tracking_token]),
                        'razorpay_options' => [
                            'key' => $razorpayData['key_id'],
                            'amount' => $razorpayData['amount'],
                            'currency' => $razorpayData['currency'],
                            'name' => $razorpayData['name'],
                            'description' => $razorpayData['description'],
                            'order_id' => $razorpayData['order_id'],
                            'prefill' => $razorpayData['prefill'],
                            'theme' => $razorpayData['theme'] ?? ['color' => '#FF006B'],
                        ],
                    ]);
                }

                return redirect()->route('order.payment', ['token' => $order->tracking_token]);
            }

            // Cash on Delivery
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'requires_payment' => false,
                    'order_token' => $order->tracking_token,
                    'order_number' => $order->order_number,
                    'redirect_url' => route('order.track', ['token' => $order->tracking_token]),
                    'message' => "Order placed successfully! Your order number is #{$order->order_number}.",
                ]);
            }

            return redirect()->route('order.track', ['token' => $order->tracking_token])
                ->with('success', "Order placed successfully! Your order number is #{$order->order_number}.");
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please correct validation errors.',
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to place your order: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Failed to place your order: ' . $e->getMessage())->withInput();
        }
    }

    public function checkDelivery(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'reverse_geocode' => ['nullable', 'boolean'],
        ]);

        $lat = $request->filled('latitude') ? (float) $request->latitude : null;
        $lon = $request->filled('longitude') ? (float) $request->longitude : null;

        $deliveryService = app(\App\Services\DeliveryService::class);
        $check = $deliveryService->checkServiceability($lat, $lon);

        $totals = $this->cartService->calculateTotals(null, 'delivery', Auth::id(), $check['distance_km']);

        $response = [
            'serviceable' => $check['serviceable'],
            'distance_km' => $check['distance_km'],
            'max_radius_km' => $check['max_radius_km'] ?? null,
            'message' => $check['message'],
            'delivery_fee' => $totals['delivery_charge'],
            'total_amount' => $totals['total_amount'],
            'formatted_delivery_fee' => '₹' . number_format($totals['delivery_charge'], 2),
            'formatted_total_amount' => '₹' . number_format($totals['total_amount'], 2),
        ];

        if ($request->boolean('reverse_geocode') && $lat !== null && $lon !== null) {
            $response['address'] = $deliveryService->reverseGeocode($lat, $lon);
        }

        return response()->json($response);
    }

    public function reverseGeocode(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $lat = (float) $request->latitude;
        $lon = (float) $request->longitude;

        $deliveryService = app(\App\Services\DeliveryService::class);
        $result = $deliveryService->reverseGeocode($lat, $lon);

        return response()->json($result);
    }
}
