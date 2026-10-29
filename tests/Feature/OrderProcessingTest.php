<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        RestaurantSetting::set('is_restaurant_open', true);
        RestaurantSetting::set('is_accepting_orders', true);
        RestaurantSetting::set('minimum_order_amount', 199.00);
        RestaurantSetting::set('default_delivery_charge', 40.00);
        RestaurantSetting::set('free_delivery_threshold', 499.00);
        RestaurantSetting::set('packaging_charge', 25.00);
        RestaurantSetting::set('tax_rate_percentage', 5.00);

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->category = Category::create([
            'name' => 'Signature Curries',
            'slug' => 'signature-curries',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Butter Chicken Deluxe',
            'slug' => 'butter-chicken-deluxe',
            'regular_price' => 350.00,
            'discount_price' => 299.00,
            'is_veg' => false,
            'is_available' => true,
        ]);
    }

    public function test_checkout_fails_when_cart_is_empty(): void
    {
        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'order_type' => 'delivery',
            'customer_name' => 'Test Customer',
            'customer_phone' => '9876543210',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
            'delivery_address_selection' => 'new',
            'address_line_1' => 'Flat 101, Pink Towers',
            'city' => 'Kolkata',
            'postal_code' => '700001',
        ]);

        $response->assertSessionHasErrors(['cart']);
    }

    public function test_checkout_fails_when_subtotal_is_below_minimum_order_amount(): void
    {
        RestaurantSetting::set('minimum_order_amount', 500.00);

        $cartService = app(CartService::class);
        $cartService->add(productId: $this->product->id, quantity: 1);

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'order_type' => 'delivery',
            'customer_name' => 'Test Customer',
            'customer_phone' => '9876543210',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
            'delivery_address_selection' => 'new',
            'address_line_1' => 'Flat 101, Pink Towers',
            'city' => 'Kolkata',
            'postal_code' => '700001',
        ]);

        $response->assertSessionHasErrors(['cart']);
    }

    public function test_valid_checkout_creates_order_with_tracking_token_and_clears_cart(): void
    {
        $cartService = app(CartService::class);
        $cartService->add(productId: $this->product->id, quantity: 1);

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'order_type' => 'delivery',
            'customer_name' => 'Alice Smith',
            'customer_phone' => '9123456789',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
            'delivery_address_selection' => 'new',
            'address_line_1' => '42 Park Street',
            'city' => 'Kolkata',
            'postal_code' => '700016',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('Alice Smith', $order->customer_name);
        $this->assertEquals(Order::STATUS_PLACED, $order->order_status);
        $this->assertNotEmpty($order->tracking_token);
        $this->assertStringStartsWith('M2W-', $order->order_number);

        // Redirects to order tracking page
        $response->assertRedirect(route('order.track', ['token' => $order->tracking_token]));

        // Order items are created with price snapshots
        $this->assertEquals(1, $order->items()->count());
        $item = $order->items()->first();
        $this->assertEquals('Butter Chicken Deluxe', $item->product_name);
        $this->assertEquals(299.00, (float) $item->unit_price);

        // Payment record created in pending status for COD
        $this->assertNotNull($order->payment);
        $this->assertEquals('cash_on_delivery', $order->payment->payment_method);
        $this->assertEquals(Payment::STATUS_PENDING, $order->payment->status);

        // Initial status history recorded
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => Order::STATUS_PLACED,
        ]);

        // Cart is cleared
        $this->assertEmpty($cartService->getRawCart());
    }

    public function test_public_order_tracking_page_displays_order_details(): void
    {
        $cartService = app(CartService::class);
        $cartService->add(productId: $this->product->id, quantity: 1);

        $orderService = app(OrderService::class);
        $order = $orderService->createOrder([
            'order_type' => 'delivery',
            'customer_name' => 'Alice Smith',
            'customer_phone' => '9123456789',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
            'delivery_address_text' => '42 Park Street, Kolkata',
        ], $this->customer);

        // Guest or customer can view order tracking via token
        $response = $this->get(route('order.track', ['token' => $order->tracking_token]));

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee('Butter Chicken Deluxe');
        $response->assertSee('Order Placed');
    }

    public function test_order_status_transitions_follow_valid_workflow(): void
    {
        $orderService = app(OrderService::class);

        $cartService = app(CartService::class);
        $cartService->add(productId: $this->product->id, quantity: 1);

        $order = $orderService->createOrder([
            'order_type' => 'delivery',
            'customer_name' => 'Bob Brown',
            'customer_phone' => '9888877777',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
            'delivery_address_text' => 'Salt Lake Sector 5, Kolkata',
        ], $this->customer);

        // 1. Placed -> Accepted
        $order = $orderService->updateOrderStatus($order, Order::STATUS_ACCEPTED, 'Kitchen accepted order');
        $this->assertEquals(Order::STATUS_ACCEPTED, $order->order_status);

        // 2. Accepted -> Preparing
        $order = $orderService->updateOrderStatus($order, Order::STATUS_PREPARING, 'Cooking in progress');
        $this->assertEquals(Order::STATUS_PREPARING, $order->order_status);

        // 3. Preparing -> Ready
        $order = $orderService->updateOrderStatus($order, Order::STATUS_READY, 'Packed and ready');
        $this->assertEquals(Order::STATUS_READY, $order->order_status);

        // 4. Ready -> Out for Delivery
        $order = $orderService->updateOrderStatus($order, Order::STATUS_OUT_FOR_DELIVERY, 'Rider picked up');
        $this->assertEquals(Order::STATUS_OUT_FOR_DELIVERY, $order->order_status);

        // 5. Out for Delivery -> Delivered (marks COD payment completed)
        $order = $orderService->updateOrderStatus($order, Order::STATUS_DELIVERED, 'Customer received order');
        $this->assertEquals(Order::STATUS_DELIVERED, $order->order_status);
        $this->assertEquals(Payment::STATUS_COMPLETED, $order->fresh()->payment->status);
    }

    public function test_invalid_order_status_transition_is_rejected(): void
    {
        $orderService = app(OrderService::class);

        $cartService = app(CartService::class);
        $cartService->add(productId: $this->product->id, quantity: 1);

        $order = $orderService->createOrder([
            'order_type' => 'delivery',
            'customer_name' => 'Charlie',
            'customer_phone' => '9777766666',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
            'delivery_address_text' => 'New Town, Kolkata',
        ], $this->customer);

        // Cannot jump directly from 'placed' to 'delivered'
        $this->expectException(Exception::class);
        $orderService->updateOrderStatus($order, Order::STATUS_DELIVERED);
    }

    public function test_customer_can_cancel_order_in_early_stages(): void
    {
        $orderService = app(OrderService::class);

        $cartService = app(CartService::class);
        $cartService->add(productId: $this->product->id, quantity: 1);

        $order = $orderService->createOrder([
            'order_type' => 'delivery',
            'customer_name' => 'David',
            'customer_phone' => '9666655555',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
            'delivery_address_text' => 'Ballygunge, Kolkata',
        ], $this->customer);

        $response = $this->actingAs($this->customer)->post(route('account.orders.cancel', $order->order_number), [
            'reason' => 'Changed my mind',
        ]);

        $response->assertRedirect();
        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->order_status);
    }
}
