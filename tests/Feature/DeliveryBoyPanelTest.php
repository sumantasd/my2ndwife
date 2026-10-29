<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\RestaurantSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeliveryBoyPanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $rider1;
    protected User $rider2;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Admin Manager',
            'email' => 'admin@test.com',
            'phone' => '+91 98000 00001',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->rider1 = User::factory()->create([
            'name' => 'Rahul Sharma',
            'email' => 'rider1@test.com',
            'phone' => '+91 98765 43210',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_DELIVERY_PERSON,
            'vehicle_type' => 'Bajaj Pulsar 150',
            'vehicle_number' => 'WB-02-AK-4421',
            'is_active' => true,
            'is_online' => true,
        ]);

        $this->rider2 = User::factory()->create([
            'name' => 'Amit Roy',
            'email' => 'rider2@test.com',
            'phone' => '+91 98765 43211',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_DELIVERY_PERSON,
            'vehicle_type' => 'Honda Activa',
            'vehicle_number' => 'WB-06-BQ-7712',
            'is_active' => true,
            'is_online' => true,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Sumanta Das',
            'email' => 'customer@test.com',
            'phone' => '+91 98300 99887',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        RestaurantSetting::set('delivery_boy_commission_amount', 35.00);
        RestaurantSetting::set('delivery_boy_self_claim_enabled', true);
    }

    /**
     * Helper to create a delivery order
     */
    protected function createDeliveryOrder(array $attributes = []): Order
    {
        $order = Order::create(array_merge([
            'order_number' => 'M2W-' . date('Ymd') . '-' . rand(1000, 9999),
            'tracking_token' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'customer_email' => $this->customer->email,
            'order_type' => Order::TYPE_DELIVERY,
            'order_schedule' => 'asap',
            'delivery_address_text' => 'Flat 4B, Silver Oak Heights, Southern Avenue, Kolkata',
            'delivery_instructions' => 'Ring bell twice and leave package on shoe rack.',
            'order_status' => Order::STATUS_READY,
            'subtotal' => 450.00,
            'discount_amount' => 0.00,
            'packaging_charge' => 25.00,
            'delivery_charge' => 30.00,
            'tax_amount' => 25.25,
            'total_amount' => 530.25,
            'delivery_latitude' => 22.5123450,
            'delivery_longitude' => 88.3567890,
            'delivery_distance_km' => 3.45,
        ], $attributes));

        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Royal Chicken Thali',
            'unit_price' => 250.00,
            'quantity' => 1,
            'line_subtotal' => 250.00,
            'line_total' => 250.00,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash_on_delivery',
            'amount' => $order->total_amount,
            'currency' => 'INR',
            'status' => Payment::STATUS_PENDING,
        ]);

        return $order;
    }

    public function test_delivery_login_page_renders_successfully(): void
    {
        $response = $this->get(route('delivery.login'));

        $response->assertStatus(200);
        $response->assertSee('Delivery Partner Portal');
        $response->assertDontSee('Return to Customer Website');
        $response->assertDontSee('FAST DEMO TEST ACCOUNTS');
        $response->assertDontSee('Rahul Sharma');
        $response->assertDontSee('Amit Roy');
        $response->assertSee('+91');
    }

    public function test_delivery_boy_can_authenticate_via_email(): void
    {
        $response = $this->post(route('delivery.login.post'), [
            'login_type' => 'email',
            'email' => 'rider1@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('delivery.dashboard'));
        $this->assertAuthenticatedAs($this->rider1);
    }

    public function test_delivery_boy_can_authenticate_via_indian_mobile_number(): void
    {
        $response = $this->post(route('delivery.login.post'), [
            'login_type' => 'phone',
            'country_code' => '+91',
            'phone' => '9876543210',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('delivery.dashboard'));
        $this->assertAuthenticatedAs($this->rider1);
    }

    public function test_delivery_boy_can_authenticate_via_formatted_mobile_number(): void
    {
        $response = $this->post(route('delivery.login.post'), [
            'login_type' => 'phone',
            'country_code' => '+91',
            'phone' => '+91 98765 43210',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('delivery.dashboard'));
        $this->assertAuthenticatedAs($this->rider1);
    }

    public function test_delivery_boy_login_fails_with_invalid_indian_mobile_number(): void
    {
        $response = $this->post(route('delivery.login.post'), [
            'login_type' => 'phone',
            'country_code' => '+91',
            'phone' => '12345',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertGuest();
    }

    public function test_delivery_boy_can_authenticate_via_dedicated_login(): void
    {
        $response = $this->post(route('delivery.login.post'), [
            'login' => 'rider1@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('delivery.dashboard'));
        $this->assertAuthenticatedAs($this->rider1);
    }

    public function test_delivery_boy_authenticating_at_main_login_is_redirected_to_delivery_dashboard(): void
    {
        $response = $this->post(route('login.post'), [
            'login' => 'rider1@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('delivery.dashboard'));
        $this->assertAuthenticatedAs($this->rider1);
    }

    public function test_customer_cannot_authenticate_at_delivery_login(): void
    {
        $response = $this->post(route('delivery.login.post'), [
            'login' => 'customer@test.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_deactivated_delivery_boy_cannot_login(): void
    {
        $this->rider1->update(['is_active' => false]);

        $response = $this->post(route('delivery.login.post'), [
            'login' => 'rider1@test.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_guest_cannot_access_delivery_dashboard(): void
    {
        $response = $this->get(route('delivery.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_delivery_dashboard(): void
    {
        $response = $this->actingAs($this->customer)->get(route('delivery.dashboard'));

        $response->assertStatus(403);
    }

    public function test_delivery_boy_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->rider1)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_delivery_boy_dashboard_loads_with_real_metrics(): void
    {
        // 1 Active order for rider1
        $this->createDeliveryOrder([
            'delivery_person_id' => $this->rider1->id,
            'order_status' => Order::STATUS_READY,
        ]);

        // 1 Completed order today
        $this->createDeliveryOrder([
            'delivery_person_id' => $this->rider1->id,
            'order_status' => Order::STATUS_DELIVERED,
            'delivered_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->rider1)->get(route('delivery.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Active Transit');
        $response->assertSee('Delivered Today');
        $response->assertSee("Today's Earnings", false);
        $response->assertSee('₹35.00'); // 1 completed * 35 commission
    }

    public function test_delivery_boy_cannot_view_another_riders_private_order(): void
    {
        $orderForRider2 = $this->createDeliveryOrder([
            'delivery_person_id' => $this->rider2->id,
            'order_status' => Order::STATUS_READY,
        ]);

        $response = $this->actingAs($this->rider1)->get(route('delivery.orders.show', $orderForRider2));

        $response->assertStatus(403);
    }

    public function test_delivery_boy_cannot_update_status_of_another_riders_order(): void
    {
        $orderForRider2 = $this->createDeliveryOrder([
            'delivery_person_id' => $this->rider2->id,
            'order_status' => Order::STATUS_READY,
        ]);

        $response = $this->actingAs($this->rider1)->post(route('delivery.orders.status', $orderForRider2), [
            'status' => Order::STATUS_OUT_FOR_DELIVERY,
        ]);

        $response->assertStatus(403);
    }

    public function test_delivery_boy_can_advance_assigned_order_from_ready_to_out_for_delivery(): void
    {
        $order = $this->createDeliveryOrder([
            'delivery_person_id' => $this->rider1->id,
            'order_status' => Order::STATUS_READY,
        ]);

        $response = $this->actingAs($this->rider1)->post(route('delivery.orders.status', $order), [
            'status' => Order::STATUS_OUT_FOR_DELIVERY,
        ]);

        $response->assertRedirect(route('delivery.orders.show', $order));
        $this->assertEquals(Order::STATUS_OUT_FOR_DELIVERY, $order->fresh()->order_status);
    }

    public function test_delivery_boy_can_confirm_order_delivered_with_proof_notes(): void
    {
        $order = $this->createDeliveryOrder([
            'delivery_person_id' => $this->rider1->id,
            'order_status' => Order::STATUS_OUT_FOR_DELIVERY,
        ]);

        $response = $this->actingAs($this->rider1)->post(route('delivery.orders.status', $order), [
            'status' => Order::STATUS_DELIVERED,
            'delivery_proof_notes' => 'Handed over directly to customer at flat 4B',
        ]);

        $response->assertRedirect(route('delivery.orders.show', $order));

        $fresh = $order->fresh();
        $this->assertEquals(Order::STATUS_DELIVERED, $fresh->order_status);
        $this->assertNotNull($fresh->delivered_at);
        $this->assertEquals('Handed over directly to customer at flat 4B', $fresh->delivery_proof_notes);

        // COD payment should be marked completed
        $this->assertEquals(Payment::STATUS_COMPLETED, $fresh->payment->status);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $order = $this->createDeliveryOrder([
            'delivery_person_id' => $this->rider1->id,
            'order_status' => Order::STATUS_READY,
        ]);

        // Cannot jump directly from ready to delivered without out_for_delivery
        $response = $this->actingAs($this->rider1)->post(route('delivery.orders.status', $order), [
            'status' => Order::STATUS_DELIVERED,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(Order::STATUS_READY, $order->fresh()->order_status);
    }

    public function test_delivery_boy_can_view_order_details_with_navigation_and_call_button(): void
    {
        $order = $this->createDeliveryOrder([
            'delivery_person_id' => $this->rider1->id,
            'delivery_latitude' => 22.5123450,
            'delivery_longitude' => 88.3567890,
        ]);

        $response = $this->actingAs($this->rider1)->get(route('delivery.orders.show', $order));

        $response->assertStatus(200);
        $response->assertSee('tel:' . $this->customer->phone, false);
        $response->assertSee('CASH ON DELIVERY');
        $response->assertSee('Please collect exactly');
        $response->assertSee('₹530.25');
    }

    public function test_fallback_navigation_when_gps_coordinates_absent(): void
    {
        $order = $this->createDeliveryOrder([
            'delivery_person_id' => $this->rider1->id,
            'delivery_latitude' => null,
            'delivery_longitude' => null,
            'delivery_address_text' => 'Park Circus, Kolkata 700016',
        ]);

        $response = $this->actingAs($this->rider1)->get(route('delivery.orders.show', $order));

        $response->assertStatus(200);
        $response->assertSee('google.com/maps/search/?api=1', false);
    }

    public function test_scheduled_delivery_order_renders_scheduled_badge(): void
    {
        $scheduledTime = Carbon::now()->addHours(4);
        $order = $this->createDeliveryOrder([
            'delivery_person_id' => $this->rider1->id,
            'order_schedule' => 'scheduled',
            'scheduled_at' => $scheduledTime,
        ]);

        $response = $this->actingAs($this->rider1)->get(route('delivery.orders.show', $order));

        $response->assertStatus(200);
        $response->assertSee('Scheduled:');
        $response->assertSee($scheduledTime->format('d M'));
    }

    public function test_delivery_boy_can_claim_available_unassigned_order(): void
    {
        $unassignedOrder = $this->createDeliveryOrder([
            'delivery_person_id' => null,
            'order_status' => Order::STATUS_READY,
        ]);

        $response = $this->actingAs($this->rider1)->post(route('delivery.orders.claim', $unassignedOrder));

        $response->assertRedirect(route('delivery.orders.show', $unassignedOrder));
        $this->assertEquals($this->rider1->id, $unassignedOrder->fresh()->delivery_person_id);
    }

    public function test_duplicate_order_claiming_is_prevented(): void
    {
        $assignedOrder = $this->createDeliveryOrder([
            'delivery_person_id' => $this->rider2->id,
            'order_status' => Order::STATUS_READY,
        ]);

        $response = $this->actingAs($this->rider1)->post(route('delivery.orders.claim', $assignedOrder));

        $response->assertSessionHas('error');
        $this->assertEquals($this->rider2->id, $assignedOrder->fresh()->delivery_person_id);
    }

    public function test_delivery_boy_can_toggle_duty_status(): void
    {
        $this->assertTrue($this->rider1->is_online);

        $response = $this->actingAs($this->rider1)->post(route('delivery.duty.toggle'));

        $response->assertSessionHas('success');
        $this->assertFalse($this->rider1->fresh()->is_online);
    }

    public function test_delivery_boy_can_update_profile_and_vehicle_details(): void
    {
        $response = $this->actingAs($this->rider1)->post(route('delivery.profile.update'), [
            'phone' => '+91 99999 88888',
            'vehicle_type' => 'Electric Scooter',
            'vehicle_number' => 'WB-01-EE-9999',
        ]);

        $response->assertSessionHas('success');

        $fresh = $this->rider1->fresh();
        $this->assertEquals('+91 99999 88888', $fresh->phone);
        $this->assertEquals('Electric Scooter', $fresh->vehicle_type);
        $this->assertEquals('WB-01-EE-9999', $fresh->vehicle_number);
    }

    public function test_admin_can_manage_delivery_partners(): void
    {
        // 1. List
        $response = $this->actingAs($this->superAdmin)->get(route('admin.delivery.index'));
        $response->assertStatus(200);
        $response->assertSee('Rahul Sharma');
        $response->assertSee('Amit Roy');

        // 2. Add New Partner
        $addResponse = $this->actingAs($this->superAdmin)->post(route('admin.delivery.store'), [
            'name' => 'Vikram Roy',
            'email' => 'vikram@test.com',
            'phone' => '+91 98888 77777',
            'vehicle_type' => 'Hero Splendor',
            'vehicle_number' => 'WB-04-XX-1234',
            'password' => 'secret123',
        ]);

        $addResponse->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'vikram@test.com',
            'role' => User::ROLE_DELIVERY_PERSON,
            'vehicle_number' => 'WB-04-XX-1234',
        ]);

        // 3. Edit Partner
        $createdRider = User::where('email', 'vikram@test.com')->first();
        $editResponse = $this->actingAs($this->superAdmin)->put(route('admin.delivery.update', $createdRider), [
            'name' => 'Vikram Roy Updated',
            'email' => 'vikram@test.com',
            'phone' => '+91 98888 77777',
            'vehicle_type' => 'TVS Jupiter',
            'vehicle_number' => 'WB-04-YY-9999',
            'is_active' => true,
        ]);

        $editResponse->assertSessionHas('success');
        $this->assertEquals('Vikram Roy Updated', $createdRider->fresh()->name);
        $this->assertEquals('TVS Jupiter', $createdRider->fresh()->vehicle_type);

        // 4. Toggle Status
        $toggleResponse = $this->actingAs($this->superAdmin)->post(route('admin.delivery.toggle', $createdRider));
        $toggleResponse->assertSessionHas('success');
        $this->assertFalse($createdRider->fresh()->is_active);
    }

    public function test_admin_can_assign_order_to_delivery_rider(): void
    {
        $order = $this->createDeliveryOrder([
            'delivery_person_id' => null,
            'order_status' => Order::STATUS_READY,
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.orders.assign-rider', $order), [
            'delivery_person_id' => $this->rider1->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals($this->rider1->id, $order->fresh()->delivery_person_id);
    }
}
