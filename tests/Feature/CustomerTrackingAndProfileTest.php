<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerTrackingAndProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        RestaurantSetting::set('restaurant_name', 'My 2nd Wife');
        RestaurantSetting::set('restaurant_phone', '+91 98300 12345');
        RestaurantSetting::set('restaurant_address', '123 Gourmet St, Food City');

        $this->customer = User::factory()->create([
            'name' => 'Aditi Sharma',
            'email' => 'aditi.sharma@example.com',
            'phone' => '+91 9876543210',
            'role' => User::ROLE_CUSTOMER,
            'password' => Hash::make('Secret@123'),
        ]);

        $category = Category::create([
            'name' => 'Signature Dishes',
            'slug' => 'signature-dishes',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Chicken Tikka Butter Masala',
            'slug' => 'chicken-tikka-butter-masala',
            'regular_price' => 320.00,
            'discount_price' => 280.00,
            'is_veg' => false,
            'is_available' => true,
        ]);
    }

    protected function createOrder(array $attributes = []): Order
    {
        $token = Str::random(32);
        $order = Order::create(array_merge([
            'order_number' => 'MW-' . rand(100000, 999999),
            'tracking_token' => $token,
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'customer_email' => $this->customer->email,
            'order_type' => Order::TYPE_DELIVERY,
            'order_status' => Order::STATUS_PLACED,
            'delivery_address_text' => 'Flat 402, Royal Palms, Salt Lake, Kolkata',
            'subtotal' => 280.00,
            'discount_amount' => 0.00,
            'delivery_charge' => 40.00,
            'packaging_charge' => 20.00,
            'tax_amount' => 17.00,
            'total_amount' => 357.00,
        ], $attributes));

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => 280.00,
            'line_subtotal' => 280.00,
            'line_total' => 280.00,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash_on_delivery',
            'status' => Payment::STATUS_PENDING,
            'amount' => $order->total_amount,
        ]);

        return $order;
    }

    public function test_delivery_order_tracking_page_renders_all_six_steps_in_sequence(): void
    {
        $order = $this->createOrder([
            'order_status' => Order::STATUS_PREPARING,
        ]);

        $response = $this->get(route('order.track', ['token' => $order->tracking_token]));

        $response->assertStatus(200);
        $response->assertSee('Live Preparation & Fulfillment Progress', false);
        
        // 6 steps and descriptions for Delivery
        $response->assertSee('Placed');
        $response->assertSee('Order received');
        $response->assertSee('Accepted');
        $response->assertSee('Kitchen confirmed');
        $response->assertSee('Preparing');
        $response->assertSee('Cooking fresh');
        $response->assertSee('Ready');
        $response->assertSee('Packed & sealed');
        $response->assertSee('Out for Delivery');
        $response->assertSee('Rider dispatched');
        $response->assertSee('Delivered');
        $response->assertSee('Enjoy your meal!');

        // Assigned delivery partner & delivery location
        $response->assertSee('Assigned Delivery Partner');
        $response->assertSee('Delivery Location');
        $response->assertSee($order->customer_name);
        $response->assertSee('Flat 402, Royal Palms');
    }

    public function test_takeaway_order_tracking_page_respects_takeaway_workflow(): void
    {
        $order = $this->createOrder([
            'order_type' => Order::TYPE_PICKUP,
            'order_status' => Order::STATUS_READY,
            'delivery_charge' => 0.00,
            'total_amount' => 317.00,
        ]);

        $response = $this->get(route('order.track', ['token' => $order->tracking_token]));

        $response->assertStatus(200);

        // Takeaway specific labels
        $response->assertSee('Ready for Pickup');
        $response->assertSee('Waiting at counter');
        $response->assertSee('Picked Up');
        $response->assertSee('Takeaway Counter Collection');
        $response->assertSee('Pickup Restaurant Location');
        $response->assertSee('My 2nd Wife');
        $response->assertSee('+91 98300 12345');

        // Does NOT display delivery rider partner card or out for delivery label
        $response->assertDontSee('Assigned Delivery Partner');
        $response->assertDontSee('Out for Delivery');
        $response->assertDontSee('Rider dispatched');
    }

    public function test_delivery_order_progress_step_mapping_across_lifecycle(): void
    {
        $order = $this->createOrder();

        $statuses = [
            Order::STATUS_PLACED => 1,
            Order::STATUS_ACCEPTED => 2,
            Order::STATUS_PREPARING => 3,
            Order::STATUS_READY => 4,
            Order::STATUS_OUT_FOR_DELIVERY => 5,
            Order::STATUS_DELIVERED => 6,
        ];

        foreach ($statuses as $status => $expectedStep) {
            $order->update(['order_status' => $status]);
            $this->assertEquals($expectedStep, $order->fresh()->progress_step, "Failed for delivery status: {$status}");
        }
    }

    public function test_takeaway_order_progress_step_mapping_across_lifecycle(): void
    {
        $order = $this->createOrder([
            'order_type' => Order::TYPE_PICKUP,
        ]);

        $statuses = [
            Order::STATUS_PLACED => 1,
            Order::STATUS_ACCEPTED => 2,
            Order::STATUS_PREPARING => 3,
            Order::STATUS_READY => 5, // Food ready, at counter for pickup
            Order::STATUS_DELIVERED => 6, // Handed over / Completed
        ];

        foreach ($statuses as $status => $expectedStep) {
            $order->update(['order_status' => $status]);
            $this->assertEquals($expectedStep, $order->fresh()->progress_step, "Failed for takeaway status: {$status}");
        }
    }

    public function test_order_status_json_endpoint_returns_correct_data(): void
    {
        $order = $this->createOrder([
            'order_status' => Order::STATUS_ACCEPTED,
        ]);

        $response = $this->get(route('order.status.json', ['token' => $order->tracking_token]));

        $response->assertStatus(200);
        $response->assertJson([
            'order_number' => $order->order_number,
            'status' => 'accepted',
            'status_label' => 'Order Accepted',
            'progress_step' => 2,
            'is_paid' => false,
        ]);
    }

    public function test_customer_profile_requires_authentication(): void
    {
        $response = $this->get(route('account.profile'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_profile_renders_header_with_real_account_data(): void
    {
        $order = $this->createOrder();

        $address = Address::create([
            'user_id' => $this->customer->id,
            'type' => 'home',
            'contact_name' => $this->customer->name,
            'contact_phone' => $this->customer->phone,
            'address_line_1' => 'Tower 5, EcoSpace',
            'city' => 'Kolkata',
            'postal_code' => '700156',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->customer)->get(route('account.profile'));

        $response->assertStatus(200);
        
        // Header summary data
        $response->assertSee('Aditi Sharma');
        $response->assertSee('+91 9876543210');
        $response->assertSee('aditi.sharma@example.com');
        $response->assertSee('Verified');
        $response->assertSee('Edit Profile');

        // Navigation Menu
        $response->assertSee('Account Overview');
        $response->assertSee('Personal Information & Security', false);
        $response->assertSee('Recent Order History');
        $response->assertSee('Saved Delivery Addresses');
        $response->assertSee('Logout from Account');

        // Order and Address preview in Overview
        $response->assertSee($order->order_number);
        $response->assertSee('Tower 5, EcoSpace');
        $response->assertSee('Track Live');
        $response->assertSee('Details');
    }

    public function test_customer_can_update_profile_name_and_phone(): void
    {
        $response = $this->actingAs($this->customer)->post(route('account.profile.update'), [
            'name' => 'Aditi Sen Sharma',
            'phone' => '+91 9999988888',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $this->customer->id,
            'name' => 'Aditi Sen Sharma',
            'phone' => '+91 9999988888',
            'email' => 'aditi.sharma@example.com', // preserved
        ]);
    }

    public function test_customer_profile_update_validates_required_fields(): void
    {
        $response = $this->actingAs($this->customer)->post(route('account.profile.update'), [
            'name' => '',
            'phone' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'phone']);
    }

    public function test_customer_can_update_password_with_valid_current_password(): void
    {
        $response = $this->actingAs($this->customer)->post(route('account.profile.update'), [
            'name' => $this->customer->name,
            'phone' => $this->customer->phone,
            'current_password' => 'Secret@123',
            'new_password' => 'NewStrongPassword#2026',
            'new_password_confirmation' => 'NewStrongPassword#2026',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $this->assertTrue(Hash::check('NewStrongPassword#2026', $this->customer->fresh()->password));
    }

    public function test_customer_password_update_fails_with_incorrect_current_password(): void
    {
        $response = $this->actingAs($this->customer)->post(route('account.profile.update'), [
            'name' => $this->customer->name,
            'phone' => $this->customer->phone,
            'current_password' => 'WrongPassword',
            'new_password' => 'NewStrongPassword#2026',
            'new_password_confirmation' => 'NewStrongPassword#2026',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    public function test_customer_profile_does_not_leak_other_customer_data(): void
    {
        $otherCustomer = User::factory()->create([
            'name' => 'Secret Customer',
            'email' => 'secret@example.com',
            'phone' => '+91 1111122222',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $otherOrder = Order::create([
            'order_number' => 'MW-SECRET999',
            'tracking_token' => Str::random(32),
            'user_id' => $otherCustomer->id,
            'customer_name' => $otherCustomer->name,
            'customer_phone' => $otherCustomer->phone,
            'order_type' => Order::TYPE_DELIVERY,
            'order_status' => Order::STATUS_PLACED,
            'delivery_address_text' => 'Confidential Penthouse',
            'subtotal' => 500.00,
            'total_amount' => 550.00,
        ]);

        $response = $this->actingAs($this->customer)->get(route('account.profile'));

        $response->assertStatus(200);
        $response->assertDontSee('MW-SECRET999');
        $response->assertDontSee('Confidential Penthouse');
        $response->assertDontSee('Secret Customer');
    }

    public function test_customer_can_update_email_address_and_resets_verification_status(): void
    {
        // Give customer a verified timestamp initially
        $this->customer->update([
            'email_verified_at' => now(),
        ]);

        $this->assertNotNull($this->customer->fresh()->email_verified_at);

        $response = $this->actingAs($this->customer)->post(route('account.profile.update'), [
            'name' => $this->customer->name,
            'phone' => $this->customer->phone,
            'email' => 'aditi.updated@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $fresh = $this->customer->fresh();
        $this->assertEquals('aditi.updated@example.com', $fresh->email);
        $this->assertNull($fresh->email_verified_at);

        // Check profile page displays the new email and 'Pending Verification' badge
        $profileRes = $this->actingAs($fresh)->get(route('account.profile'));
        $profileRes->assertStatus(200);
        $profileRes->assertSee('aditi.updated@example.com');
        $profileRes->assertSee('Pending Verification');
    }

    public function test_customer_cannot_update_email_to_another_customers_email(): void
    {
        $otherCustomer = User::factory()->create([
            'email' => 'taken@example.com',
            'phone' => '+91 9123456780',
        ]);

        $originalEmail = $this->customer->email;

        $response = $this->actingAs($this->customer)->post(route('account.profile.update'), [
            'name' => $this->customer->name,
            'phone' => $this->customer->phone,
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertEquals($originalEmail, $this->customer->fresh()->email);
    }

    public function test_customer_profile_rejects_invalid_email_format(): void
    {
        $response = $this->actingAs($this->customer)->post(route('account.profile.update'), [
            'name' => $this->customer->name,
            'phone' => $this->customer->phone,
            'email' => 'not-a-valid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_customer_order_history_and_addresses_remain_intact_after_email_change(): void
    {
        $order = $this->createOrder();

        $address = Address::create([
            'user_id' => $this->customer->id,
            'type' => 'home',
            'contact_name' => $this->customer->name,
            'contact_phone' => $this->customer->phone,
            'address_line_1' => 'Flat 502, Lake View',
            'city' => 'Kolkata',
            'postal_code' => '700091',
            'is_default' => true,
        ]);

        $this->actingAs($this->customer)->post(route('account.profile.update'), [
            'name' => 'Aditi Sharma Roy',
            'phone' => $this->customer->phone,
            'email' => 'aditi.sharma.roy@example.com',
        ]);

        // Verify orders and addresses are intact for this user
        $fresh = $this->customer->fresh();
        $this->assertEquals(1, $fresh->orders()->count());
        $this->assertEquals($order->id, $fresh->orders()->first()->id);
        $this->assertEquals(1, $fresh->addresses()->count());
        $this->assertEquals($address->id, $fresh->addresses()->first()->id);

        // Verify profile view loads successfully with the existing order and new email
        $response = $this->actingAs($fresh)->get(route('account.profile'));
        $response->assertStatus(200);
        $response->assertSee('aditi.sharma.roy@example.com');
        $response->assertSee($order->order_number);
        $response->assertSee('Flat 502, Lake View');
    }

    public function test_customer_profile_change_password_fields_render_show_hide_password_toggles(): void
    {
        $response = $this->actingAs($this->customer)->get(route('account.profile'));

        $response->assertStatus(200);
        $response->assertSee('id="profile_current_password"', false);
        $response->assertSee('id="profile_new_password"', false);
        $response->assertSee('id="profile_new_password_confirmation"', false);
        $response->assertSee('Show password');
        $response->assertSee('Hide password');
    }
}
