<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use App\Services\CartService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RazorpayPaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected Category $category;
    protected Product $product;
    protected string $testKeyId = 'rzp_test_IntegrationKey123';
    protected string $testKeySecret = 'testSecretXYZ1234567890';
    protected string $testWebhookSecret = 'testWebhookSecret999';

    protected function setUp(): void
    {
        parent::setUp();

        RestaurantSetting::set('is_restaurant_open', true);
        RestaurantSetting::set('is_accepting_orders', true);
        RestaurantSetting::set('minimum_order_amount', 100.00);
        RestaurantSetting::set('default_delivery_charge', 40.00);
        RestaurantSetting::set('packaging_charge', 20.00);
        RestaurantSetting::set('tax_enabled', false);
        RestaurantSetting::set('tax_rate_percentage', 0.00);
        RestaurantSetting::set('delivery_enabled', true);
        RestaurantSetting::set('takeaway_enabled', true);
        RestaurantSetting::set('cod_enabled', true);
        RestaurantSetting::set('razorpay_enabled', true);
        RestaurantSetting::set('payment_mode', 'test', 'payment');
        RestaurantSetting::set('razorpay_key_id', $this->testKeyId, 'payment');
        RestaurantSetting::set('razorpay_key_secret', $this->testKeySecret, 'payment');
        RestaurantSetting::set('razorpay_webhook_secret', $this->testWebhookSecret, 'payment');

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'name' => 'Rahul Sharma',
            'email' => 'rahul@example.com',
            'phone' => '9876543210',
        ]);

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin@my2ndwife.test',
        ]);

        $this->category = Category::create([
            'name' => 'Main Course',
            'slug' => 'main-course',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Chicken Biryani Special',
            'slug' => 'chicken-biryani-special',
            'regular_price' => 350.00,
            'discount_price' => 300.00,
            'is_veg' => false,
            'is_available' => true,
        ]);
    }

    protected function populateCart(int $qty = 1): void
    {
        $cartService = app(CartService::class);
        $cartService->add($this->product->id, $qty);
    }

    /** 1. Selecting Razorpay creates pending order and returns checkout configuration */
    public function test_customer_selects_razorpay_creates_pending_order_and_returns_checkout_config(): void
    {
        $this->populateCart();

        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_rzp_mock_111',
                'entity' => 'order',
                'amount' => 36000,
                'amount_paid' => 0,
                'amount_due' => 36000,
                'currency' => 'INR',
                'receipt' => 'M2W-REC-111',
                'status' => 'created',
            ], 200),
        ]);

        $response = $this->actingAs($this->customer)->postJson(route('checkout.process'), [
            'order_type' => 'pickup',
            'customer_name' => 'Rahul Sharma',
            'customer_phone' => '9876543210',
            'customer_email' => 'rahul@example.com',
            'order_schedule' => 'asap',
            'payment_method' => 'razorpay',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'requires_payment' => true,
            'razorpay_options' => [
                'key' => $this->testKeyId,
                'order_id' => 'order_rzp_mock_111',
                'amount' => 32000, // 300 item + 20 packaging (takeaway has free delivery) = 320 -> 32000 paise
                'currency' => 'INR',
            ],
        ]);

        $order = Order::where('customer_phone', '9876543210')->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(Order::STATUS_PENDING_PAYMENT, $order->order_status);
        $this->assertFalse($order->isPaid());

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'razorpay',
            'gateway_order_id' => 'order_rzp_mock_111',
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => Order::STATUS_PENDING_PAYMENT,
        ]);
    }

    /** 2. Failed Razorpay order creation does not confirm or place the order */
    public function test_razorpay_order_creation_failure_does_not_mark_order_placed_or_paid(): void
    {
        $this->populateCart();

        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'error' => [
                    'code' => 'BAD_REQUEST_ERROR',
                    'description' => 'Authentication failed or invalid credentials',
                ],
            ], 401),
        ]);

        $response = $this->actingAs($this->customer)->postJson(route('checkout.process'), [
            'order_type' => 'pickup',
            'customer_name' => 'Rahul Sharma',
            'customer_phone' => '9876543210',
            'order_schedule' => 'asap',
            'payment_method' => 'razorpay',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        // Order is NOT confirmed or placed
        $confirmedCount = Order::where('order_status', Order::STATUS_PLACED)->count();
        $this->assertEquals(0, $confirmedCount);
    }

    /** 3. Successful signature verification confirms the order and marks payment completed */
    public function test_successful_signature_verification_confirms_order_and_marks_payment_completed(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'customer_name' => 'Rahul Sharma',
            'customer_phone' => '9876543210',
            'order_type' => 'pickup',
            'order_schedule' => 'asap',
            'order_status' => Order::STATUS_PENDING_PAYMENT,
            'subtotal' => 300.00,
            'packaging_charge' => 20.00,
            'delivery_charge' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => 320.00,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'razorpay',
            'amount' => 320.00,
            'currency' => 'INR',
            'gateway_order_id' => 'order_rzp_mock_222',
            'status' => Payment::STATUS_PENDING,
        ]);

        $paymentId = 'pay_test_999888';
        $rzpOrderId = 'order_rzp_mock_222';
        $validSignature = hash_hmac('sha256', $rzpOrderId . '|' . $paymentId, $this->testKeySecret);

        $response = $this->actingAs($this->customer)->postJson(route('order.payment.verify', ['token' => $order->tracking_token]), [
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => $rzpOrderId,
            'razorpay_signature' => $validSignature,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $order->refresh();
        $this->assertEquals(Order::STATUS_PLACED, $order->order_status);
        $this->assertTrue($order->isPaid());
        $this->assertEquals(Payment::STATUS_COMPLETED, $order->payment->status);
        $this->assertEquals($paymentId, $order->payment->transaction_id);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => Order::STATUS_PENDING_PAYMENT,
            'to_status' => Order::STATUS_PLACED,
        ]);
    }

    /** 4. Invalid signature is rejected and does not confirm the order */
    public function test_invalid_signature_is_rejected_and_order_remains_unconfirmed(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'customer_name' => 'Rahul Sharma',
            'customer_phone' => '9876543210',
            'order_type' => 'pickup',
            'order_schedule' => 'asap',
            'order_status' => Order::STATUS_PENDING_PAYMENT,
            'subtotal' => 300.00,
            'total_amount' => 320.00,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'razorpay',
            'amount' => 320.00,
            'currency' => 'INR',
            'gateway_order_id' => 'order_rzp_mock_333',
            'status' => Payment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->customer)->postJson(route('order.payment.verify', ['token' => $order->tracking_token]), [
            'razorpay_payment_id' => 'pay_test_tampered',
            'razorpay_order_id' => 'order_rzp_mock_333',
            'razorpay_signature' => 'invalid_tampered_signature_xyz',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        $order->refresh();
        $this->assertEquals(Order::STATUS_PENDING_PAYMENT, $order->order_status);
        $this->assertEquals(Payment::STATUS_FAILED, $order->payment->status);
        $this->assertFalse($order->isPaid());
    }

    /** 5. Mismatched Razorpay Order ID is rejected */
    public function test_mismatched_razorpay_order_id_is_rejected(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'customer_name' => 'Rahul Sharma',
            'customer_phone' => '9876543210',
            'order_type' => 'pickup',
            'order_schedule' => 'asap',
            'order_status' => Order::STATUS_PENDING_PAYMENT,
            'subtotal' => 300.00,
            'total_amount' => 320.00,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'razorpay',
            'amount' => 320.00,
            'currency' => 'INR',
            'gateway_order_id' => 'order_expected_original',
            'status' => Payment::STATUS_PENDING,
        ]);

        $paymentId = 'pay_test_random';
        $wrongOrderId = 'order_wrong_mismatched';
        $sig = hash_hmac('sha256', $wrongOrderId . '|' . $paymentId, $this->testKeySecret);

        $response = $this->actingAs($this->customer)->postJson(route('order.payment.verify', ['token' => $order->tracking_token]), [
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => $wrongOrderId,
            'razorpay_signature' => $sig,
        ]);

        $response->assertStatus(422);
        $this->assertEquals(Order::STATUS_PENDING_PAYMENT, $order->fresh()->order_status);
    }

    /** 6. Duplicate callbacks are idempotent and do not duplicate order confirmation */
    public function test_duplicate_callbacks_are_idempotent(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'customer_name' => 'Rahul Sharma',
            'customer_phone' => '9876543210',
            'order_type' => 'pickup',
            'order_schedule' => 'asap',
            'order_status' => Order::STATUS_PENDING_PAYMENT,
            'subtotal' => 300.00,
            'total_amount' => 320.00,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'razorpay',
            'amount' => 320.00,
            'currency' => 'INR',
            'gateway_order_id' => 'order_rzp_mock_444',
            'status' => Payment::STATUS_PENDING,
        ]);

        $paymentId = 'pay_test_idem_444';
        $rzpOrderId = 'order_rzp_mock_444';
        $sig = hash_hmac('sha256', $rzpOrderId . '|' . $paymentId, $this->testKeySecret);

        // First verification
        $res1 = $this->actingAs($this->customer)->postJson(route('order.payment.verify', ['token' => $order->tracking_token]), [
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => $rzpOrderId,
            'razorpay_signature' => $sig,
        ]);
        $res1->assertStatus(200);

        $historyCount1 = OrderStatusHistory::where('order_id', $order->id)->where('to_status', Order::STATUS_PLACED)->count();
        $this->assertEquals(1, $historyCount1);

        // Second duplicate verification (e.g. double click or retry)
        $res2 = $this->actingAs($this->customer)->postJson(route('order.payment.verify', ['token' => $order->tracking_token]), [
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => $rzpOrderId,
            'razorpay_signature' => $sig,
        ]);
        $res2->assertStatus(200);

        // History count remains exactly 1
        $historyCount2 = OrderStatusHistory::where('order_id', $order->id)->where('to_status', Order::STATUS_PLACED)->count();
        $this->assertEquals(1, $historyCount2);
    }

    /** 7. Client payment failure marks payment failed without confirming order */
    public function test_client_payment_failure_records_failed_status_and_does_not_confirm_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'customer_name' => 'Rahul Sharma',
            'customer_phone' => '9876543210',
            'order_type' => 'pickup',
            'order_schedule' => 'asap',
            'order_status' => Order::STATUS_PENDING_PAYMENT,
            'subtotal' => 300.00,
            'total_amount' => 320.00,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'razorpay',
            'amount' => 320.00,
            'currency' => 'INR',
            'status' => Payment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->customer)->postJson(route('order.payment.failed', ['token' => $order->tracking_token]), [
            'reason' => 'Bank server unavailable',
            'error_code' => 'BAD_REQUEST_ERROR',
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(Order::STATUS_PENDING_PAYMENT, $order->order_status);
        $this->assertEquals(Payment::STATUS_FAILED, $order->payment->status);
    }

    /** 8. Webhook with valid signature confirms order when browser callback is missed */
    public function test_webhook_with_valid_signature_captures_payment_and_confirms_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'customer_name' => 'Rahul Sharma',
            'customer_phone' => '9876543210',
            'order_type' => 'pickup',
            'order_schedule' => 'asap',
            'order_status' => Order::STATUS_PENDING_PAYMENT,
            'subtotal' => 300.00,
            'total_amount' => 320.00,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'razorpay',
            'amount' => 320.00,
            'currency' => 'INR',
            'gateway_order_id' => 'order_webhook_live_555',
            'status' => Payment::STATUS_PENDING,
        ]);

        $webhookPayload = json_encode([
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_hook_555777',
                        'order_id' => 'order_webhook_live_555',
                        'amount' => 32000,
                        'currency' => 'INR',
                        'status' => 'captured',
                    ],
                ],
            ],
        ]);

        $webhookSignature = hash_hmac('sha256', $webhookPayload, $this->testWebhookSecret);

        $response = $this->call(
            'POST',
            route('payment.webhook'),
            [],
            [],
            [],
            [
                'HTTP_X_RAZORPAY_SIGNATURE' => $webhookSignature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $webhookPayload
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $order->refresh();
        $this->assertEquals(Order::STATUS_PLACED, $order->order_status);
        $this->assertEquals(Payment::STATUS_COMPLETED, $order->payment->status);
        $this->assertEquals('pay_hook_555777', $order->payment->transaction_id);
    }

    /** 9. Webhook with invalid or missing signature is rejected */
    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        $webhookPayload = json_encode([
            'event' => 'payment.captured',
            'payload' => [],
        ]);

        $response = $this->call(
            'POST',
            route('payment.webhook'),
            [],
            [],
            [],
            [
                'HTTP_X_RAZORPAY_SIGNATURE' => 'forged_fake_signature',
                'CONTENT_TYPE' => 'application/json',
            ],
            $webhookPayload
        );

        $response->assertStatus(400);
    }

    /** 10. Admin can update Razorpay credentials in database and sync runtime env */
    public function test_admin_can_update_razorpay_credentials(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            '_section' => 'payment',
            'razorpay_enabled' => '1',
            'payment_mode' => 'test',
            'razorpay_key_id' => 'rzp_test_NewAdminKey789',
            'razorpay_key_secret' => 'newSecretPass789',
            'razorpay_webhook_secret' => 'newWebhookSecret789',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('rzp_test_NewAdminKey789', RestaurantSetting::get('razorpay_key_id'));
        $this->assertEquals('newSecretPass789', RestaurantSetting::get('razorpay_key_secret'));
        $this->assertEquals('newWebhookSecret789', RestaurantSetting::get('razorpay_webhook_secret'));

        // Check masked secrets on settings page
        $pageResponse = $this->actingAs($this->admin)->get(route('admin.settings.index', ['tab' => 'payment']));
        $pageResponse->assertStatus(200);
        $pageResponse->assertDontSee('newSecretPass789'); // Plaintext secret must never be exposed
        $pageResponse->assertSee('••••••••s789'); // Masked value
    }

    /** 11. Mode validation rejects Live Key ID in Test mode */
    public function test_mode_validation_rejects_mismatched_keys(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            '_section' => 'payment',
            'payment_mode' => 'test',
            'razorpay_key_id' => 'rzp_live_WrongLiveKey',
            'razorpay_key_secret' => 'someSecret',
        ]);

        $response->assertSessionHasErrors(['razorpay_key_id']);
    }

    /** 12. Admin can update Google Maps API settings and key is masked */
    public function test_admin_can_update_google_maps_api_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            '_section' => 'google_maps',
            'google_maps_api_key' => 'AIzaSyTestGoogleMapsKey999',
            'restaurant_latitude' => '22.5535',
            'restaurant_longitude' => '88.3533',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('AIzaSyTestGoogleMapsKey999', RestaurantSetting::get('google_maps_api_key'));

        $pageResponse = $this->actingAs($this->admin)->get(route('admin.settings.index', ['tab' => 'google_maps']));
        $pageResponse->assertStatus(200);
        $pageResponse->assertDontSee('AIzaSyTestGoogleMapsKey999');
        $pageResponse->assertSee('••••••••y999');
    }

    /** 13. Cash on Delivery checkout continues to work properly */
    public function test_cod_checkout_continues_to_work_as_before(): void
    {
        $this->populateCart();

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'order_type' => 'pickup',
            'customer_name' => 'Rahul Sharma',
            'customer_phone' => '9876543210',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
        ]);

        $order = Order::where('customer_phone', '9876543210')->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(Order::STATUS_PLACED, $order->order_status);
        $this->assertEquals('cash_on_delivery', $order->payment->payment_method);
        $this->assertEquals(Payment::STATUS_PENDING, $order->payment->status);

        $response->assertRedirect(route('order.track', ['token' => $order->tracking_token]));
    }
}
