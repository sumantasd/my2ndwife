<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingWorkTasksTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        RestaurantSetting::set('is_restaurant_open', true);
        RestaurantSetting::set('is_accepting_orders', true);
        RestaurantSetting::set('minimum_order_amount', 100.00);
        RestaurantSetting::set('default_delivery_charge', 40.00);
        RestaurantSetting::set('free_delivery_threshold', 500.00);
        RestaurantSetting::set('packaging_charge', 20.00);
        RestaurantSetting::set('tax_rate_percentage', 5.00);
        RestaurantSetting::set('scheduled_orders_available', true);

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->category = Category::create([
            'name' => 'Signature Biryani',
            'slug' => 'signature-biryani',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Royal Mutton Biryani',
            'slug' => 'royal-mutton-biryani',
            'regular_price' => 450.00,
            'discount_price' => 399.00,
            'is_veg' => false,
            'is_available' => true,
        ]);
    }

    /**
     * TASK 1: Add to Cart Toast Notification payload & error handling
     */
    public function test_add_to_cart_ajax_returns_toast_payload_and_updates_subtotal(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson(route('cart.add'), [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'cart_count' => 2,
            'product_name' => 'Royal Mutton Biryani',
        ]);
        $this->assertStringContainsString('Royal Mutton Biryani added to cart', $response->json('message'));
        $this->assertNotEmpty($response->json('cart_subtotal'));
    }

    public function test_add_to_cart_fails_gracefully_when_product_is_unavailable(): void
    {
        $unavailableProduct = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Out of Stock Special',
            'slug' => 'out-of-stock-special',
            'regular_price' => 250.00,
            'is_available' => false,
        ]);

        $response = $this->actingAs($this->customer)
            ->postJson(route('cart.add'), [
                'product_id' => $unavailableProduct->id,
                'quantity' => 1,
            ]);

        $response->assertJson([
            'success' => false,
            'message' => 'This food item is currently unavailable.',
        ]);
    }

    /**
     * TASK 2: Reverse geocode endpoint handles coordinates and returns clean address
     */
    public function test_reverse_geocode_endpoint_handles_coordinates_and_returns_clean_address(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson(route('checkout.reverse-geocode'), [
                'latitude' => 22.5726,
                'longitude' => 88.3639,
            ]);

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        $this->assertEquals(22.5726, $response->json('latitude'));
        $this->assertEquals(88.3639, $response->json('longitude'));
        $this->assertArrayHasKey('formatted_address', $response->json());
    }

    public function test_reverse_geocode_validates_coordinate_bounds(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson(route('checkout.reverse-geocode'), [
                'latitude' => 120.00, // Invalid latitude (> 90)
                'longitude' => 88.3639,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['latitude']);
    }

    /**
     * TASK 3: Save Location with the Order
     */
    public function test_checkout_validates_and_stores_gps_coordinates_with_order(): void
    {
        $cartService = app(CartService::class);
        $cartService->add(productId: $this->product->id, quantity: 1);

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'order_type' => 'delivery',
            'customer_name' => 'Suman Roy',
            'customer_phone' => '9830098300',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
            'delivery_address_selection' => 'new',
            'address_line_1' => 'Plot 45, Sector V, Salt Lake',
            'city' => 'Kolkata',
            'postal_code' => '700091',
            'delivery_latitude' => 22.572646,
            'delivery_longitude' => 88.363895,
        ]);

        $response->assertRedirect();
        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(22.572646, (float) $order->delivery_latitude);
        $this->assertEquals(88.363895, (float) $order->delivery_longitude);
        $this->assertStringContainsString('Sector V', $order->delivery_address_text);
    }

    public function test_checkout_rejects_out_of_bound_gps_coordinates(): void
    {
        $cartService = app(CartService::class);
        $cartService->add(productId: $this->product->id, quantity: 1);

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'order_type' => 'delivery',
            'customer_name' => 'Suman Roy',
            'customer_phone' => '9830098300',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
            'delivery_address_selection' => 'new',
            'address_line_1' => 'Plot 45, Sector V',
            'city' => 'Kolkata',
            'postal_code' => '700091',
            'delivery_latitude' => 195.0, // Invalid: exceeds 90
            'delivery_longitude' => 88.36,
        ]);

        $response->assertSessionHasErrors(['delivery_latitude']);
        $this->assertNull(Order::first());
    }

    /**
     * TASK 4: Show Customer Location in Admin Order Details
     */
    public function test_admin_order_details_displays_location_and_google_maps_link(): void
    {
        $order = Order::create([
            'order_number' => 'M2W-TEST-GPS-01',
            'user_id' => $this->customer->id,
            'customer_name' => 'Rajesh Sharma',
            'customer_phone' => '9876543210',
            'order_type' => Order::TYPE_DELIVERY,
            'order_status' => Order::STATUS_PLACED,
            'order_schedule' => 'asap',
            'delivery_address_text' => '12B Camac Street, Kolkata 700017',
            'delivery_latitude' => 22.5448,
            'delivery_longitude' => 88.3533,
            'delivery_distance_km' => 4.25,
            'subtotal' => 399.00,
            'total_amount' => 460.00,
            'tracking_token' => 'token-gps-test-123',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertSee('GPS Delivery Coordinates');
        $response->assertSee('22.544800');
        $response->assertSee('88.353300');
        $response->assertSee('https://www.google.com/maps?q=22.5448,88.3533', false);
        $response->assertSee('View on Google Maps');
    }

    public function test_admin_order_details_displays_fallback_when_coordinates_absent(): void
    {
        $order = Order::create([
            'order_number' => 'M2W-TEST-NOGPS-02',
            'user_id' => $this->customer->id,
            'customer_name' => 'Meera Sen',
            'customer_phone' => '9876543211',
            'order_type' => Order::TYPE_DELIVERY,
            'order_status' => Order::STATUS_PLACED,
            'order_schedule' => 'asap',
            'delivery_address_text' => 'Manual Address Only, Kolkata',
            'delivery_latitude' => null,
            'delivery_longitude' => null,
            'subtotal' => 399.00,
            'total_amount' => 460.00,
            'tracking_token' => 'token-nogps-test-456',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertSee('No GPS coordinates recorded for this order');
    }

    /**
     * TASK 5: Replace Customer-Facing "ASAP" with "Instant"
     */
    public function test_checkout_instant_order_placement_works_and_renders_instant_badge(): void
    {
        $cartService = app(CartService::class);
        $cartService->add(productId: $this->product->id, quantity: 1);

        $response = $this->actingAs($this->customer)->get(route('checkout.index'));
        $response->assertOk();
        $response->assertSee('Instant Delivery');
        $response->assertSee('Schedule Order');

        // Place Instant order (uses 'asap' backend value)
        $process = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'order_type' => 'delivery',
            'customer_name' => 'Instant Customer',
            'customer_phone' => '9888877777',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
            'delivery_address_selection' => 'new',
            'address_line_1' => 'Instant Delivery Lane 1',
            'city' => 'Kolkata',
            'postal_code' => '700020',
        ]);

        $process->assertRedirect();
        $order = Order::latest()->first();
        $this->assertEquals('asap', $order->order_schedule);

        // Verify tracking page displays Instant Delivery badge
        $track = $this->get(route('order.track', ['token' => $order->tracking_token]));
        $track->assertOk();
        $track->assertSee('Instant Delivery');
    }

    /**
     * TASK 6: Preserve and Verify Scheduled Orders
     */
    public function test_checkout_scheduled_order_validates_future_date_and_preserves_schedule(): void
    {
        $cartService = app(CartService::class);
        $cartService->add(productId: $this->product->id, quantity: 1);

        $futureTime = now()->addHours(3)->format('Y-m-d\TH:i');

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'order_type' => 'delivery',
            'customer_name' => 'Scheduled Customer',
            'customer_phone' => '9999988888',
            'order_schedule' => 'scheduled',
            'scheduled_at' => $futureTime,
            'payment_method' => 'cash_on_delivery',
            'delivery_address_selection' => 'new',
            'address_line_1' => 'Future Delivery Boulevard',
            'city' => 'Kolkata',
            'postal_code' => '700001',
        ]);

        $response->assertRedirect();
        $order = Order::latest()->first();
        $this->assertEquals('scheduled', $order->order_schedule);
        $this->assertNotNull($order->scheduled_at);

        // Verify Admin details show Scheduled badge and timestamp
        $adminView = $this->actingAs($this->admin)->get(route('admin.orders.show', $order));
        $adminView->assertOk();
        $adminView->assertSee('Scheduled:');

        // Verify Customer tracking view shows Scheduled badge
        $trackView = $this->get(route('order.track', ['token' => $order->tracking_token]));
        $trackView->assertOk();
        $trackView->assertSee('Scheduled:');
    }

    public function test_checkout_scheduled_order_fails_when_past_date_provided(): void
    {
        $cartService = app(CartService::class);
        $cartService->add(productId: $this->product->id, quantity: 1);

        $pastTime = now()->subHour()->format('Y-m-d\TH:i');

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'order_type' => 'delivery',
            'customer_name' => 'Past Date Customer',
            'customer_phone' => '9999988888',
            'order_schedule' => 'scheduled',
            'scheduled_at' => $pastTime,
            'payment_method' => 'cash_on_delivery',
            'delivery_address_selection' => 'new',
            'address_line_1' => 'Past Street',
            'city' => 'Kolkata',
            'postal_code' => '700001',
        ]);

        $response->assertSessionHasErrors(['scheduled_at']);
        $this->assertNull(Order::first());
    }

    public function test_checkout_scheduled_order_fails_when_scheduled_at_missing(): void
    {
        $cartService = app(CartService::class);
        $cartService->add(productId: $this->product->id, quantity: 1);

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'order_type' => 'delivery',
            'customer_name' => 'Missing Schedule Time Customer',
            'customer_phone' => '9999988888',
            'order_schedule' => 'scheduled',
            'scheduled_at' => '',
            'payment_method' => 'cash_on_delivery',
            'delivery_address_selection' => 'new',
            'address_line_1' => 'Future Lane',
            'city' => 'Kolkata',
            'postal_code' => '700001',
        ]);

        $response->assertSessionHasErrors(['scheduled_at']);
        $this->assertNull(Order::first());
    }
}
