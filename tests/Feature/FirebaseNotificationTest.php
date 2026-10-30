<?php

namespace Tests\Feature;

use App\Jobs\SendFirebasePushNotificationJob;
use App\Models\Category;
use App\Models\DeviceToken;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use App\Services\CartService;
use App\Services\FirebaseService;
use App\Services\NotificationDispatcherService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FirebaseNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected User $rider;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        RestaurantSetting::set('is_restaurant_open', true, 'general', 'boolean');
        RestaurantSetting::set('delivery_enabled', true, 'delivery', 'boolean');
        RestaurantSetting::set('minimum_order_amount', 100.00, 'general', 'float');

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin@my2ndwife.test',
            'phone' => '+91 9876543210',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'email' => 'customer@my2ndwife.test',
            'phone' => '+91 9876543211',
            'is_active' => true,
        ]);

        $this->rider = User::factory()->create([
            'role' => User::ROLE_DELIVERY_PERSON,
            'email' => 'rider@my2ndwife.test',
            'phone' => '+91 9876543212',
            'is_active' => true,
            'is_online' => true,
        ]);

        $category = Category::create([
            'name' => 'Biryani Category',
            'slug' => 'biryani-category',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Royal Dum Biryani',
            'slug' => 'royal-dum-biryani-test',
            'regular_price' => 350.00,
            'is_available' => true,
            'is_veg' => false,
        ]);
    }

    public function test_admin_only_access_to_firebase_settings_and_notification_logs(): void
    {
        // 1. Guest redirected
        $response = $this->get(route('admin.notifications.index'));
        $response->assertRedirect(route('login'));

        // 2. Customer gets 403 Forbidden
        $response = $this->actingAs($this->customer)->get(route('admin.notifications.index'));
        $response->assertStatus(403);

        // 3. Admin gets 200 OK
        $response = $this->actingAs($this->admin)->get(route('admin.notifications.index'));
        $response->assertStatus(200);
        $response->assertSee('Firebase Push Notifications');
    }

    public function test_admin_can_update_firebase_credentials_and_event_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            '_section' => 'notifications',
            'firebase_notifications_enabled' => '1',
            'firebase_project_id' => 'my-test-firebase-proj',
            'firebase_client_email' => 'test-service@my-test-firebase-proj.iam.gserviceaccount.com',
            'firebase_private_key' => "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASC\n-----END PRIVATE KEY-----",
            'firebase_customer_events' => ['order_placed', 'order_confirmed', 'out_for_delivery'],
            'firebase_rider_events' => ['rider_order_assigned', 'rider_pickup_reminder'],
            'firebase_admin_events' => ['admin_new_order'],
        ]);

        $response->assertRedirect();
        $this->assertTrue(RestaurantSetting::get('firebase_notifications_enabled'));
        $this->assertEquals('my-test-firebase-proj', RestaurantSetting::get('firebase_project_id'));
        $this->assertEquals('test-service@my-test-firebase-proj.iam.gserviceaccount.com', RestaurantSetting::get('firebase_client_email'));

        $savedCustEvents = RestaurantSetting::get('firebase_customer_events');
        $this->assertIsArray($savedCustEvents);
        $this->assertContains('order_placed', $savedCustEvents);
        $this->assertContains('order_confirmed', $savedCustEvents);
        $this->assertNotContains('order_delivered', $savedCustEvents);
    }

    public function test_invalid_firebase_configuration_is_rejected_gracefully(): void
    {
        $service = app(FirebaseService::class);
        $validation = $service->validateCredentials([
            'project_id' => '',
            'client_email' => '',
            'private_key' => '',
        ]);

        $this->assertFalse($validation['success']);
        $this->assertStringContainsString('Project ID is required', $validation['error']);
    }

    public function test_device_token_registration_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/devices/register', [
            'token' => 'sample_fcm_token_123',
            'device_type' => 'android',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_customer_can_register_and_remove_device_token(): void
    {
        // 1. Register Token
        $response = $this->actingAs($this->customer, 'sanctum')->postJson('/api/v1/devices/register', [
            'token' => 'fcm_android_device_token_abc_123',
            'device_type' => 'android',
            'device_model' => 'Google Pixel 8',
            'app_version' => '1.0.0',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $this->customer->id,
            'token' => 'fcm_android_device_token_abc_123',
            'device_type' => 'android',
            'is_active' => true,
        ]);

        // 2. Fetch My Tokens
        $listResponse = $this->actingAs($this->customer, 'sanctum')->getJson('/api/v1/devices/my-tokens');
        $listResponse->assertStatus(200);
        $listResponse->assertJsonCount(1, 'devices');

        // 3. Remove Token
        $removeResponse = $this->actingAs($this->customer, 'sanctum')->postJson('/api/v1/devices/remove', [
            'token' => 'fcm_android_device_token_abc_123',
        ]);

        $removeResponse->assertStatus(200);
        $removeResponse->assertJson(['success' => true]);

        $this->assertDatabaseMissing('device_tokens', [
            'user_id' => $this->customer->id,
            'token' => 'fcm_android_device_token_abc_123',
        ]);
    }

    public function test_device_token_ownership_and_reassignment(): void
    {
        $otherCustomer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        // Customer registers token
        DeviceToken::create([
            'user_id' => $this->customer->id,
            'token' => 'shared_device_token_xyz',
            'device_type' => 'android',
            'is_active' => true,
        ]);

        // Other customer attempts to remove it (should not affect Customer A's token)
        $this->actingAs($otherCustomer, 'sanctum')->postJson('/api/v1/devices/remove', [
            'token' => 'shared_device_token_xyz',
        ]);

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $this->customer->id,
            'token' => 'shared_device_token_xyz',
        ]);

        // When Other Customer logs into this device and registers the token, it reassigns cleanly
        $this->actingAs($otherCustomer, 'sanctum')->postJson('/api/v1/devices/register', [
            'token' => 'shared_device_token_xyz',
            'device_type' => 'android',
        ]);

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $otherCustomer->id,
            'token' => 'shared_device_token_xyz',
        ]);
    }

    public function test_customer_order_placed_notification_dispatched_for_cod(): void
    {
        Queue::fake();

        // Register device token for customer
        DeviceToken::create([
            'user_id' => $this->customer->id,
            'token' => 'customer_fcm_token_cod',
            'device_type' => 'android',
            'is_active' => true,
        ]);

        $cartService = app(CartService::class);
        $cartService->add($this->product->id, 1);

        $orderService = app(OrderService::class);
        $order = $orderService->createOrder([
            'customer_name' => 'John Customer',
            'customer_phone' => '+91 9876543211',
            'customer_email' => 'customer@my2ndwife.test',
            'payment_method' => 'cash_on_delivery',
            'order_type' => Order::TYPE_PICKUP,
        ], $this->customer);

        $this->assertEquals(Order::STATUS_PLACED, $order->order_status);

        // Assert customer order_placed and admin_new_order jobs dispatched
        Queue::assertPushed(SendFirebasePushNotificationJob::class, function ($job) {
            return $job->eventType === 'order_placed' && $job->target === $this->customer->id;
        });

        Queue::assertPushed(SendFirebasePushNotificationJob::class, function ($job) {
            return $job->eventType === 'admin_new_order' && $job->mode === 'role' && $job->target === 'admin';
        });
    }

    public function test_unverified_razorpay_order_never_triggers_payment_or_placed_notifications(): void
    {
        Queue::fake();

        $cartService = app(CartService::class);
        $cartService->add($this->product->id, 1);

        $orderService = app(OrderService::class);
        $order = $orderService->createOrder([
            'customer_name' => 'John Customer',
            'customer_phone' => '+91 9876543211',
            'payment_method' => 'razorpay',
            'order_type' => Order::TYPE_PICKUP,
        ], $this->customer);

        $this->assertEquals(Order::STATUS_PENDING_PAYMENT, $order->order_status);

        // Neither order_placed nor payment_successful should be dispatched yet
        Queue::assertNotPushed(SendFirebasePushNotificationJob::class, function ($job) {
            return in_array($job->eventType, ['order_placed', 'payment_successful']);
        });
    }

    public function test_verified_razorpay_order_triggers_payment_successful_and_order_placed_notifications(): void
    {
        Queue::fake();

        $cartService = app(CartService::class);
        $cartService->add($this->product->id, 1);

        $orderService = app(OrderService::class);
        $order = $orderService->createOrder([
            'customer_name' => 'John Customer',
            'customer_phone' => '+91 9876543211',
            'payment_method' => 'razorpay',
            'order_type' => Order::TYPE_PICKUP,
        ], $this->customer);

        // Payment verification confirms order
        $paymentService = app(PaymentService::class);
        $paymentService->confirmPaidOrder($order, 'pay_test_999888', 'order_test_777666', 'fake_sig');

        $this->assertEquals(Order::STATUS_PLACED, $order->fresh()->order_status);

        // Both payment_successful and order_placed must now be dispatched
        Queue::assertPushed(SendFirebasePushNotificationJob::class, function ($job) {
            return $job->eventType === 'payment_successful' && $job->target === $this->customer->id;
        });

        Queue::assertPushed(SendFirebasePushNotificationJob::class, function ($job) {
            return $job->eventType === 'order_placed' && $job->target === $this->customer->id;
        });
    }

    public function test_order_status_transitions_trigger_expected_notifications(): void
    {
        Queue::fake();

        $cartService = app(CartService::class);
        $cartService->add($this->product->id, 1);

        $orderService = app(OrderService::class);
        $order = $orderService->createOrder([
            'customer_name' => 'John Customer',
            'customer_phone' => '+91 9876543211',
            'payment_method' => 'cash_on_delivery',
            'order_type' => Order::TYPE_PICKUP,
        ], $this->customer);

        // 1. Accept Order -> order_confirmed
        $orderService->updateOrderStatus($order, Order::STATUS_ACCEPTED, 'Kitchen accepted order', $this->admin);
        Queue::assertPushed(SendFirebasePushNotificationJob::class, function ($job) {
            return $job->eventType === 'order_confirmed';
        });

        // 2. Preparing -> order_preparing
        $orderService->updateOrderStatus($order, Order::STATUS_PREPARING, 'Cooking started', $this->admin);
        Queue::assertPushed(SendFirebasePushNotificationJob::class, function ($job) {
            return $job->eventType === 'order_preparing';
        });

        // 3. Ready -> food_packed
        $orderService->updateOrderStatus($order, Order::STATUS_READY, 'Packed & ready', $this->admin);
        Queue::assertPushed(SendFirebasePushNotificationJob::class, function ($job) {
            return $job->eventType === 'food_packed';
        });

        // 4. Delivered -> order_delivered
        $orderService->updateOrderStatus($order, Order::STATUS_DELIVERED, 'Customer picked up', $this->admin);
        Queue::assertPushed(SendFirebasePushNotificationJob::class, function ($job) {
            return $job->eventType === 'order_delivered';
        });
    }

    public function test_rider_assignment_triggers_rider_and_customer_notifications(): void
    {
        Queue::fake();

        $cartService = app(CartService::class);
        $cartService->add($this->product->id, 1);

        $orderService = app(OrderService::class);
        $order = $orderService->createOrder([
            'customer_name' => 'John Customer',
            'customer_phone' => '+91 9876543211',
            'payment_method' => 'cash_on_delivery',
            'order_type' => 'delivery',
            'delivery_address_text' => '12 Park Street, Kolkata',
        ], $this->customer);

        $orderService->assignDeliveryPerson($order, $this->rider->id, $this->admin);

        // Assert customer received rider_assigned notification
        Queue::assertPushed(SendFirebasePushNotificationJob::class, function ($job) {
            return $job->eventType === 'rider_assigned' && $job->target === $this->customer->id;
        });

        // Assert rider received rider_order_assigned notification
        Queue::assertPushed(SendFirebasePushNotificationJob::class, function ($job) {
            return $job->eventType === 'rider_order_assigned' && $job->target === $this->rider->id;
        });
    }

    public function test_failed_notification_is_logged_and_can_be_retried(): void
    {
        RestaurantSetting::set('firebase_notifications_enabled', true, 'notifications', 'boolean');
        RestaurantSetting::set('firebase_project_id', 'test-proj', 'notifications');
        RestaurantSetting::set('firebase_client_email', 'test@test.com', 'notifications');
        RestaurantSetting::set('firebase_private_key', 'invalid_key', 'notifications');

        $firebaseService = app(FirebaseService::class);

        // Attempt send to token (will fail gracefully due to mock/invalid key and record log)
        $res = $firebaseService->sendToToken('dummy_fcm_token_fail', 'Test Title', 'Test Body');

        $this->assertFalse($res['success']);
        $this->assertDatabaseHas('notification_logs', [
            'device_token' => 'dummy_fcm_token_fail',
            'status' => NotificationLog::STATUS_FAILED,
        ]);

        $log = NotificationLog::where('device_token', 'dummy_fcm_token_fail')->first();

        // Admin can retry this failed log
        $retryResponse = $this->actingAs($this->admin)->post(route('admin.notifications.retry', $log));
        $this->assertNotNull($retryResponse);

        $this->assertDatabaseHas('notification_logs', [
            'id' => $log->id,
            'retry_count' => 1,
        ]);
    }

    protected function generateValidTestRsaKey(): string
    {
        $conf = file_exists('C:/xampp/apache/conf/openssl.cnf') ? 'C:/xampp/apache/conf/openssl.cnf' : null;
        $args = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        if ($conf) {
            $args['config'] = $conf;
        }
        $res = openssl_pkey_new($args);
        $privKey = '';
        if ($conf) {
            openssl_pkey_export($res, $privKey, null, ['config' => $conf]);
        } else {
            openssl_pkey_export($res, $privKey);
        }
        return $privKey;
    }

    public function test_service_account_json_upload_populates_project_id_and_client_email(): void
    {
        $validRsaKey = $this->generateValidTestRsaKey();
        $this->assertNotEmpty($validRsaKey);

        $jsonPayload = [
            'type' => 'service_account',
            'project_id' => 'my-custom-firebase-project',
            'private_key_id' => 'abcd1234efgh5678',
            'private_key' => $validRsaKey,
            'client_email' => 'firebase-adminsdk-live@my-custom-firebase-project.iam.gserviceaccount.com',
            'client_id' => '10987654321',
            'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri' => 'https://oauth2.googleapis.com/token',
        ];

        $uploadedFile = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'service-account.json',
            json_encode($jsonPayload)
        );

        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            '_section' => 'notifications',
            'firebase_notifications_enabled' => '1',
            'firebase_project_id' => '', // Empty from form, must NOT overwrite parsed JSON
            'firebase_client_email' => '', // Empty from form, must NOT overwrite parsed JSON
            'firebase_service_account_file' => $uploadedFile,
        ]);

        $response->assertRedirect();

        // Database settings must be populated from the uploaded file
        $this->assertEquals('my-custom-firebase-project', RestaurantSetting::get('firebase_project_id'));
        $this->assertEquals('firebase-adminsdk-live@my-custom-firebase-project.iam.gserviceaccount.com', RestaurantSetting::get('firebase_client_email'));
        $this->assertTrue(RestaurantSetting::get('firebase_notifications_enabled'));

        // Settings page should render these values without exposing the private key
        $pageResponse = $this->actingAs($this->admin)->get(route('admin.settings.index'));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('my-custom-firebase-project');
        $pageResponse->assertSee('firebase-adminsdk-live@my-custom-firebase-project.iam.gserviceaccount.com');
        $pageResponse->assertDontSee($validRsaKey); // Private key must remain masked
    }

    public function test_invalid_service_account_json_upload_preserves_existing_configuration(): void
    {
        // Setup initial existing configuration
        RestaurantSetting::set('firebase_project_id', 'existing-stable-proj', 'notifications');
        RestaurantSetting::set('firebase_client_email', 'existing@stable-proj.iam.gserviceaccount.com', 'notifications');
        RestaurantSetting::set('firebase_private_key', 'existing_key_data', 'notifications');

        // Upload an invalid JSON (missing private_key and client_email)
        $invalidJson = json_encode([
            'type' => 'service_account',
            'project_id' => 'hacked-or-corrupt-project',
        ]);

        $invalidFile = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'bad-service-account.json',
            $invalidJson
        );

        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            '_section' => 'notifications',
            'firebase_service_account_file' => $invalidFile,
        ]);

        $response->assertSessionHas('error');

        // Verify existing configuration is completely preserved
        $this->assertEquals('existing-stable-proj', RestaurantSetting::get('firebase_project_id'));
        $this->assertEquals('existing@stable-proj.iam.gserviceaccount.com', RestaurantSetting::get('firebase_client_email'));
        $this->assertEquals('existing_key_data', RestaurantSetting::get('firebase_private_key'));
    }

    public function test_ajax_service_account_json_parser_endpoint(): void
    {
        $validRsaKey = $this->generateValidTestRsaKey();

        $jsonPayload = [
            'type' => 'service_account',
            'project_id' => 'ajax-parsed-proj',
            'client_email' => 'ajax-service@ajax-parsed-proj.iam.gserviceaccount.com',
            'private_key' => $validRsaKey,
        ];

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('sa.json', json_encode($jsonPayload));

        $response = $this->actingAs($this->admin)->postJson(route('admin.settings.firebase.parse'), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'project_id' => 'ajax-parsed-proj',
            'client_email' => 'ajax-service@ajax-parsed-proj.iam.gserviceaccount.com',
            'has_private_key' => true,
        ]);
        $response->assertJsonMissing(['private_key' => $validRsaKey]);
    }

    public function test_device_token_route_aliases_do_not_return_404(): void
    {
        $routes = [
            '/api/v1/devices/register',
            '/api/devices/register',
            '/api/v1/device/register',
            '/api/device/register',
            '/api/v1/device-token',
            '/api/device-token',
        ];

        foreach ($routes as $url) {
            $res = $this->actingAs($this->customer, 'sanctum')->postJson($url, [
                'token' => 'token_for_' . md5($url),
                'device_type' => 'android',
            ]);
            $this->assertNotEquals(404, $res->status(), "Route {$url} unexpectedly returned 404!");
            $res->assertStatus(200);
            $res->assertJson(['success' => true]);
        }
    }

    public function test_device_token_field_aliases_fcm_token_and_device_token(): void
    {
        // 1. Using fcm_token field
        $response1 = $this->actingAs($this->customer, 'sanctum')->postJson('/api/v1/devices/register', [
            'fcm_token' => 'aliased_fcm_token_999',
            'device_type' => 'Android',
        ]);
        $response1->assertStatus(200);
        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $this->customer->id,
            'token' => 'aliased_fcm_token_999',
            'device_type' => 'android',
        ]);

        // 2. Using device_token field
        $response2 = $this->actingAs($this->customer, 'sanctum')->postJson('/api/v1/devices/register', [
            'device_token' => 'aliased_device_token_888',
        ]);
        $response2->assertStatus(200);
        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $this->customer->id,
            'token' => 'aliased_device_token_888',
        ]);
    }

    public function test_notification_event_toggle_disables_specific_customer_notification(): void
    {
        RestaurantSetting::set('firebase_notifications_enabled', true, 'notifications', 'boolean');
        // Only enable order_confirmed; disable order_placed
        RestaurantSetting::set('firebase_customer_events', ['order_confirmed'], 'notifications', 'json');

        $firebaseService = app(FirebaseService::class);
        $this->assertFalse($firebaseService->isEventEnabled('order_placed', 'customer'));
        $this->assertTrue($firebaseService->isEventEnabled('order_confirmed', 'customer'));

        DeviceToken::create([
            'user_id' => $this->customer->id,
            'token' => 'customer_token_evt_test',
            'device_type' => 'android',
            'is_active' => true,
        ]);

        // Attempt sending disabled event
        $res = $firebaseService->sendToUser($this->customer, 'order_placed', ['order_number' => '1001']);
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('disabled', $res['message']);
    }

    public function test_configuration_status_validates_key_syntax(): void
    {
        $firebaseService = app(FirebaseService::class);

        // 1. Corrupt key format
        RestaurantSetting::set('firebase_notifications_enabled', true, 'notifications', 'boolean');
        RestaurantSetting::set('firebase_project_id', 'test-proj', 'notifications');
        RestaurantSetting::set('firebase_client_email', 'test@test.com', 'notifications');
        RestaurantSetting::set('firebase_private_key', '-----BEGIN PRIVATE KEY-----\nNOT_A_VALID_KEY\n-----END PRIVATE KEY-----', 'notifications');

        $status = $firebaseService->getConfigurationStatus();
        $this->assertEquals('Invalid Private Key Format', $status['status_label']);
        $this->assertEquals('red', $status['status_severity']);
        $this->assertFalse($status['valid_key']);

        // 2. Valid key format
        $validKey = $this->generateValidTestRsaKey();
        RestaurantSetting::set('firebase_private_key', $validKey, 'notifications');
        $validStatus = $firebaseService->getConfigurationStatus();
        $this->assertEquals('Active & Ready', $validStatus['status_label']);
        $this->assertEquals('green', $validStatus['status_severity']);
        $this->assertTrue($validStatus['valid_key']);
    }

    public function test_push_dispatch_failure_returns_actionable_diagnostics_without_exposing_secrets(): void
    {
        $validKey = $this->generateValidTestRsaKey();

        RestaurantSetting::set('firebase_notifications_enabled', true, 'notifications', 'boolean');
        RestaurantSetting::set('firebase_project_id', 'test-diagnostic-project', 'notifications');
        RestaurantSetting::set('firebase_client_email', 'service@test-diagnostic-project.iam.gserviceaccount.com', 'notifications');
        RestaurantSetting::set('firebase_private_key', $validKey, 'notifications');

        // Mock Google OAuth2 to succeed with test token
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'mock_google_oauth_bearer_token',
                'expires_in' => 3600,
            ], 200),
            // Mock FCM HTTP v1 to return 403 PERMISSION_DENIED
            'https://fcm.googleapis.com/v1/projects/test-diagnostic-project/messages:send' => Http::response([
                'error' => [
                    'code' => 403,
                    'message' => 'Firebase Cloud Messaging API has not been used in project test-diagnostic-project before or it is disabled.',
                    'status' => 'PERMISSION_DENIED',
                ],
            ], 403),
        ]);

        $firebaseService = app(FirebaseService::class);
        $result = $firebaseService->sendToToken('test_device_token_xyz', 'Test Title', 'Test Body');

        $this->assertFalse($result['success']);
        $this->assertEquals(403, $result['http_status']);
        $this->assertEquals('PERMISSION_DENIED', $result['error_code']);
        $this->assertStringContainsString('Firebase Cloud Messaging API is disabled', $result['error']);

        // Check NotificationLog
        $this->assertDatabaseHas('notification_logs', [
            'device_token' => 'test_device_token_xyz',
            'status' => NotificationLog::STATUS_FAILED,
        ]);

        $log = NotificationLog::where('device_token', 'test_device_token_xyz')->latest()->first();
        $this->assertStringContainsString('[PERMISSION_DENIED]', $log->error_message);
        // Ensure secrets are not leaked
        $this->assertStringNotContainsString($validKey, $log->error_message);
        $this->assertStringNotContainsString('mock_google_oauth_bearer_token', $log->error_message);
    }

    public function test_order_placed_notification_links_to_registered_user_by_phone(): void
    {
        Queue::fake();

        // Customer has registered device token
        DeviceToken::create([
            'user_id' => $this->customer->id,
            'token' => 'customer_token_phone_matched',
            'device_type' => 'android',
            'is_active' => true,
        ]);

        $cartService = app(CartService::class);
        $cartService->add($this->product->id, 1);

        $orderService = app(OrderService::class);
        // Order placed WITHOUT passing $user object, but matching customer phone
        $order = $orderService->createOrder([
            'customer_name' => 'John By Phone',
            'customer_phone' => '+91 9876543211', // Matches $this->customer->phone
            'payment_method' => 'cash_on_delivery',
            'order_type' => Order::TYPE_PICKUP,
        ]);

        $this->assertEquals($this->customer->id, $order->user_id);

        Queue::assertPushed(SendFirebasePushNotificationJob::class, function ($job) {
            return $job->eventType === 'order_placed' && $job->target === $this->customer->id;
        });
    }
}
