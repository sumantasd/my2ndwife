<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementAndReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected User $rider;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->rider = User::factory()->create([
            'name' => 'Ravi Kumar',
            'phone' => '9830099999',
            'role' => 'delivery_person',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Main Course',
            'slug' => 'main-course',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Paneer Butter Masala',
            'slug' => 'paneer-butter-masala',
            'regular_price' => 240.00,
            'discount_price' => 240.00,
            'is_veg' => true,
            'is_available' => true,
        ]);
    }

    private function createDeliveryOrder(string $status = Order::STATUS_PLACED): Order
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_type' => 'delivery',
            'customer_name' => 'Sourav Ganguly',
            'customer_phone' => '9830011111',
            'customer_email' => 'sourav@bcci.tv',
            'order_schedule' => 'asap',
            'delivery_address_text' => 'Behala Chowrasta, Kolkata',
            'delivery_instructions' => 'Call on arrival',
            'order_status' => $status,
            'subtotal' => 240.00,
            'discount_amount' => 0.00,
            'packaging_charge' => 15.00,
            'delivery_charge' => 30.00,
            'tax_amount' => 12.00,
            'total_amount' => 297.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => 240.00,
            'line_subtotal' => 240.00,
            'line_total' => 240.00,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash_on_delivery',
            'amount' => 297.00,
            'currency' => 'INR',
            'status' => Payment::STATUS_PENDING,
        ]);

        return $order;
    }

    private function createTakeawayOrder(string $status = Order::STATUS_PLACED): Order
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_type' => 'pickup',
            'customer_name' => 'Pooja Sen',
            'customer_phone' => '9830022222',
            'customer_email' => 'pooja@example.com',
            'order_schedule' => 'asap',
            'delivery_address_text' => null,
            'order_status' => $status,
            'subtotal' => 240.00,
            'discount_amount' => 0.00,
            'packaging_charge' => 15.00,
            'delivery_charge' => 0.00,
            'tax_amount' => 12.00,
            'total_amount' => 267.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => 240.00,
            'line_subtotal' => 240.00,
            'line_total' => 240.00,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash_on_delivery',
            'amount' => 267.00,
            'currency' => 'INR',
            'status' => Payment::STATUS_PENDING,
        ]);

        return $order;
    }

    /** 1 & 2. Live notifications endpoint detects new orders with proper authorization */
    public function test_live_notifications_endpoint_detects_new_order_and_prevents_unauthorized_access(): void
    {
        // Unauthorized customer cannot access live-alerts endpoint
        $unauthRes = $this->actingAs($this->customer)->get(route('admin.orders.live-alerts'));
        $unauthRes->assertStatus(403);

        // 1. Initial poll as Admin: captures latest_id without triggering popups
        $initialRes = $this->actingAs($this->admin)->getJson(route('admin.orders.live-alerts', ['initial' => '1']));
        $initialRes->assertStatus(200);
        $initialData = $initialRes->json();
        $this->assertTrue($initialData['success']);
        $this->assertEmpty($initialData['new_orders']);

        $sinceId = $initialData['latest_id'];

        // Customer creates a new order
        $newOrder = $this->createDeliveryOrder();

        // 2. Next poll tick receives the newly placed order
        $pollRes = $this->actingAs($this->admin)->getJson(route('admin.orders.live-alerts', ['since_id' => $sinceId]));
        $pollRes->assertStatus(200);
        $pollData = $pollRes->json();
        $this->assertNotEmpty($pollData['new_orders']);
        $this->assertEquals($newOrder->id, $pollData['new_orders'][0]['id']);
        $this->assertEquals('Sourav Ganguly', $pollData['new_orders'][0]['customer_name']);
        $this->assertEquals('Delivery', $pollData['new_orders'][0]['order_type_label']);
        $this->assertEquals('₹297.00', $pollData['new_orders'][0]['total_formatted']);

        // 4. Repeated poll with updated since_id returns no duplicate alerts
        $secondPollRes = $this->actingAs($this->admin)->getJson(route('admin.orders.live-alerts', ['since_id' => $pollData['latest_id']]));
        $secondPollData = $secondPollRes->json();
        $this->assertEmpty($secondPollData['new_orders']);
    }

    /** 5 & 6. Delivery order workflow: Placed -> Accepted -> Preparing -> Ready -> Rider Assigned -> Out for Delivery -> Delivered */
    public function test_delivery_order_complete_workflow_and_rider_dispatch_enforcement(): void
    {
        $order = $this->createDeliveryOrder();

        // 1. Accept Order
        $res = $this->actingAs($this->admin)->post(route('admin.orders.status', $order), [
            'status' => Order::STATUS_ACCEPTED,
        ]);
        $res->assertSessionHas('success');
        $this->assertEquals(Order::STATUS_ACCEPTED, $order->fresh()->order_status);

        // 2. Preparing
        $res = $this->actingAs($this->admin)->post(route('admin.orders.status', $order), [
            'status' => Order::STATUS_PREPARING,
        ]);
        $res->assertSessionHas('success');
        $this->assertEquals(Order::STATUS_PREPARING, $order->fresh()->order_status);

        // 3. Ready for Dispatch
        $res = $this->actingAs($this->admin)->post(route('admin.orders.status', $order), [
            'status' => Order::STATUS_READY,
        ]);
        $res->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals(Order::STATUS_READY, $order->order_status);
        $this->assertEquals('Ready for Dispatch', $order->status_label);

        // 4. Dispatch WITHOUT assigned rider must be rejected
        $dispatchFail = $this->actingAs($this->admin)->post(route('admin.orders.status', $order), [
            'status' => Order::STATUS_OUT_FOR_DELIVERY,
        ]);
        $dispatchFail->assertSessionHas('error');
        $this->assertEquals(Order::STATUS_READY, $order->fresh()->order_status);

        // 5. Assign Rider
        $assignRes = $this->actingAs($this->admin)->post(route('admin.orders.assign-rider', $order), [
            'delivery_person_id' => $this->rider->id,
        ]);
        $assignRes->assertSessionHas('success');
        $this->assertEquals($this->rider->id, $order->fresh()->delivery_person_id);

        // 6. Dispatch with assigned rider now succeeds
        $dispatchSuccess = $this->actingAs($this->admin)->post(route('admin.orders.status', $order), [
            'status' => Order::STATUS_OUT_FOR_DELIVERY,
        ]);
        $dispatchSuccess->assertSessionHas('success');
        $this->assertEquals(Order::STATUS_OUT_FOR_DELIVERY, $order->fresh()->order_status);

        // 7. Deliver order (completes payment for COD)
        $deliverRes = $this->actingAs($this->admin)->post(route('admin.orders.status', $order), [
            'status' => Order::STATUS_DELIVERED,
        ]);
        $deliverRes->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals(Order::STATUS_DELIVERED, $order->order_status);
        $this->assertEquals('completed', $order->payment->status);
    }

    /** 7 & 8. Takeaway workflow: Placed -> Accepted -> Preparing -> Ready for Pickup -> Completed (No Rider Allowed) */
    public function test_takeaway_order_workflow_and_rider_prohibition(): void
    {
        $order = $this->createTakeawayOrder();
        $this->assertTrue($order->isTakeaway());
        $this->assertFalse($order->isDelivery());

        // Attempting to assign a delivery rider to a takeaway order is rejected
        $assignRiderRes = $this->actingAs($this->admin)->post(route('admin.orders.assign-rider', $order), [
            'delivery_person_id' => $this->rider->id,
        ]);
        $assignRiderRes->assertSessionHas('error');
        $this->assertNull($order->fresh()->delivery_person_id);

        // 1. Accept
        $this->actingAs($this->admin)->post(route('admin.orders.status', $order), ['status' => Order::STATUS_ACCEPTED]);
        $this->assertEquals(Order::STATUS_ACCEPTED, $order->fresh()->order_status);

        // 2. Preparing
        $this->actingAs($this->admin)->post(route('admin.orders.status', $order), ['status' => Order::STATUS_PREPARING]);
        $this->assertEquals(Order::STATUS_PREPARING, $order->fresh()->order_status);

        // 3. Ready for Pickup
        $this->actingAs($this->admin)->post(route('admin.orders.status', $order), ['status' => Order::STATUS_READY]);
        $order->refresh();
        $this->assertEquals(Order::STATUS_READY, $order->order_status);
        $this->assertEquals('Ready for Pickup', $order->status_label);

        // Takeaway orders must NOT allow transitioning to out_for_delivery
        $invalidDispatch = $this->actingAs($this->admin)->post(route('admin.orders.status', $order), ['status' => Order::STATUS_OUT_FOR_DELIVERY]);
        $invalidDispatch->assertSessionHas('error');
        $this->assertEquals(Order::STATUS_READY, $order->fresh()->order_status);

        // 4. Mark Picked Up & Completed
        $pickupRes = $this->actingAs($this->admin)->post(route('admin.orders.status', $order), ['status' => Order::STATUS_DELIVERED]);
        $pickupRes->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals(Order::STATUS_DELIVERED, $order->order_status);
        $this->assertEquals('Picked Up / Completed', $order->status_label);
    }

    /** 9 & 10. Admin Orders List UI & Print Icon Alignment */
    public function test_admin_orders_index_renders_properly_with_print_icon_alignment(): void
    {
        $deliveryOrder = $this->createDeliveryOrder();
        $takeawayOrder = $this->createTakeawayOrder();

        $res = $this->actingAs($this->admin)->get(route('admin.orders.index'));
        $res->assertStatus(200);

        // Check for order types
        $res->assertSee('Delivery');
        $res->assertSee('Takeaway');

        // Check for alignment & thermal receipt link
        $res->assertSee('align-middle');
        $res->assertSee(route('admin.orders.receipt', $deliveryOrder));
        $res->assertSee(route('admin.orders.receipt', $takeawayOrder));
    }

    /** 11 & 12. 80mm and 58mm Thermal Receipt rendering */
    public function test_thermal_receipt_renders_correctly_for_80mm_and_58mm(): void
    {
        $order = $this->createDeliveryOrder();

        // 80mm preview
        $res80 = $this->actingAs($this->admin)->get(route('admin.orders.receipt', ['order' => $order, 'width' => '80mm']));
        $res80->assertStatus(200);
        $res80->assertSee('80mm');
        $res80->assertSee($order->order_number);
        $res80->assertSee('Sourav Ganguly');
        $res80->assertSee('Paneer Butter Masala');
        $res80->assertSee('₹297.00');
        $res80->assertSee('HOME DELIVERY');

        // 58mm preview
        $res58 = $this->actingAs($this->admin)->get(route('admin.orders.receipt', ['order' => $order, 'width' => '58mm']));
        $res58->assertStatus(200);
        $res58->assertSee('58mm');
        $res58->assertSee($order->order_number);
    }

    /** 13, 14 & 15. Admin Thermal Receipt Settings persistence and conditional field rendering */
    public function test_receipt_settings_persistence_and_field_toggles(): void
    {
        // 1. Save Thermal Receipt Settings via Admin Settings Controller
        $saveRes = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            '_section' => 'receipt',
            'receipt_paper_width' => '58mm',
            'receipt_copies' => 2,
            'receipt_show_logo' => '0', // Logo disabled
            'receipt_show_phone' => '0', // Phone disabled
            'receipt_show_notes' => '1',
            'receipt_show_footer_message' => '1',
            'receipt_footer_message' => 'Special Kitchen Copy - Thank you!',
        ]);

        $saveRes->assertSessionHas('success');

        // Verify settings in database
        $this->assertEquals('58mm', RestaurantSetting::get('receipt_paper_width'));
        $this->assertEquals(2, RestaurantSetting::get('receipt_copies'));
        $this->assertFalse(RestaurantSetting::get('receipt_show_logo'));
        $this->assertFalse(RestaurantSetting::get('receipt_show_phone'));
        $this->assertEquals('Special Kitchen Copy - Thank you!', RestaurantSetting::get('receipt_footer_message'));

        // 2. Verify settings affect receipt output
        $order = $this->createDeliveryOrder();
        $receiptRes = $this->actingAs($this->admin)->get(route('admin.orders.receipt', $order));
        $receiptRes->assertStatus(200);
        $receiptRes->assertSee('Special Kitchen Copy - Thank you!');
        $receiptRes->assertSee('58mm');
        $receiptRes->assertDontSee('alt="Logo"'); // Logo disabled
    }

    /** 16. Test Receipt endpoint renders sample data reliably */
    public function test_receipt_test_preview_endpoint_works(): void
    {
        $testRes = $this->actingAs($this->admin)->get(route('admin.settings.receipt.test'));
        $testRes->assertStatus(200);
        $testRes->assertSee('Receipt');
        $testRes->assertSee('Thermal Printing Tip');
    }

    /** 17. Existing order totals and payment data remain completely unchanged */
    public function test_order_financials_remain_immutable_across_status_changes(): void
    {
        $order = $this->createDeliveryOrder();

        $originalSubtotal = (float) $order->subtotal;
        $originalTax = (float) $order->tax_amount;
        $originalDelivery = (float) $order->delivery_charge;
        $originalPackaging = (float) $order->packaging_charge;
        $originalTotal = (float) $order->total_amount;

        // Transition through statuses
        $this->actingAs($this->admin)->post(route('admin.orders.status', $order), ['status' => Order::STATUS_ACCEPTED]);
        $this->actingAs($this->admin)->post(route('admin.orders.status', $order), ['status' => Order::STATUS_PREPARING]);
        $this->actingAs($this->admin)->post(route('admin.orders.status', $order), ['status' => Order::STATUS_READY]);

        $order->refresh();

        $this->assertEquals($originalSubtotal, (float) $order->subtotal);
        $this->assertEquals($originalTax, (float) $order->tax_amount);
        $this->assertEquals($originalDelivery, (float) $order->delivery_charge);
        $this->assertEquals($originalPackaging, (float) $order->packaging_charge);
        $this->assertEquals($originalTotal, (float) $order->total_amount);
    }

    /** 18. Order details renders all six statuses horizontally in a single row */
    public function test_order_details_horizontal_timeline_renders_all_six_statuses_for_delivery_and_takeaway(): void
    {
        // 1. Delivery order in 'preparing'
        $deliveryOrder = $this->createDeliveryOrder();
        $this->actingAs($this->admin)->post(route('admin.orders.status', $deliveryOrder), ['status' => Order::STATUS_ACCEPTED]);
        $this->actingAs($this->admin)->post(route('admin.orders.status', $deliveryOrder), ['status' => Order::STATUS_PREPARING]);

        $res = $this->actingAs($this->admin)->get(route('admin.orders.show', $deliveryOrder));
        $res->assertStatus(200);
        $res->assertSee('grid grid-cols-6');
        $res->assertSee('Placed');
        $res->assertSee('Accepted');
        $res->assertSee('Preparing');
        $res->assertSee('Ready');
        $res->assertSee('Dispatched');
        $res->assertSee('Delivered');
        $res->assertSee('overflow-x-auto'); // Container scroll on mobile

        // 2. Takeaway order in 'ready'
        $takeawayOrder = $this->createTakeawayOrder();
        $this->actingAs($this->admin)->post(route('admin.orders.status', $takeawayOrder), ['status' => Order::STATUS_ACCEPTED]);
        $this->actingAs($this->admin)->post(route('admin.orders.status', $takeawayOrder), ['status' => Order::STATUS_PREPARING]);
        $this->actingAs($this->admin)->post(route('admin.orders.status', $takeawayOrder), ['status' => Order::STATUS_READY]);

        $takeawayRes = $this->actingAs($this->admin)->get(route('admin.orders.show', $takeawayOrder));
        $takeawayRes->assertStatus(200);
        $takeawayRes->assertSee('grid grid-cols-6');
        $takeawayRes->assertSee('Ready for Pickup');
        $takeawayRes->assertSee('Picked Up');
        $takeawayRes->assertDontSee('Choose Delivery Rider:'); // Prohibited for takeaway
    }

    /** 19. Admin layout includes louder/longer ringtone controls and autoplay resilience */
    public function test_admin_layout_contains_ringtone_controls_and_audio_blocked_handling(): void
    {
        $res = $this->actingAs($this->admin)->get(route('admin.orders.index'));
        $res->assertStatus(200);
        $res->assertSee('startRingtone()', false);
        $res->assertSee('stopRingtone()', false);
        $res->assertSee('setVolume', false);
        $res->assertSee('audioBlocked', false);
        $res->assertSee('Stop Sound');
        $res->assertSee('Alert Volume');
    }
}
