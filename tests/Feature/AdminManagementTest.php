<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        RestaurantSetting::set('restaurant_name', 'My 2nd Wife');
        RestaurantSetting::set('currency_symbol', '₹');
        RestaurantSetting::set('minimum_order_amount', 199.00);

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_admin_can_create_edit_and_delete_empty_category(): void
    {
        // 1. Create Category
        $createResponse = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Royal Tandoor',
            'description' => 'Smoky charcoal delicacies',
            'display_order' => 5,
            'is_active' => true,
        ]);

        $createResponse->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Royal Tandoor',
            'slug' => 'royal-tandoor',
        ]);

        $category = Category::where('slug', 'royal-tandoor')->first();

        // 2. Edit Category
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.categories.update', $category->id), [
            'name' => 'Royal Tandoor Specials',
            'slug' => 'royal-tandoor-specials',
            'description' => 'Updated smoky charcoal delicacies',
            'display_order' => 10,
            'is_active' => true,
        ]);

        $updateResponse->assertRedirect(route('admin.categories.index'));
        $this->assertEquals('Royal Tandoor Specials', $category->fresh()->name);

        // 3. Delete Empty Category
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category->id));
        $deleteResponse->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_cannot_delete_category_with_assigned_products(): void
    {
        $category = Category::create([
            'name' => 'Desserts',
            'slug' => 'desserts',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Gulab Jamun',
            'slug' => 'gulab-jamun',
            'regular_price' => 120.00,
            'is_veg' => true,
            'is_available' => true,
        ]);

        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category->id));

        $deleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_create_product_with_variations(): void
    {
        $category = Category::create([
            'name' => 'Beverages',
            'slug' => 'beverages',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Mango Lassi',
            'description' => 'Chilled yogurt mango drink',
            'regular_price' => 140.00,
            'discount_price' => 120.00,
            'is_veg' => 1,
            'is_available' => 1,
            'is_featured' => 1,
            'prep_time_minutes' => 10,
            'variations' => [
                ['name' => 'Regular (300ml)', 'price' => 120.00, 'is_default' => 1],
                ['name' => 'Large (500ml)', 'price' => 180.00, 'is_default' => 0],
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Mango Lassi',
            'slug' => 'mango-lassi',
            'is_veg' => 1,
        ]);

        $product = Product::where('slug', 'mango-lassi')->first();
        $this->assertEquals(2, $product->variations()->count());
    }

    public function test_admin_can_view_and_update_order_status(): void
    {
        $order = Order::create([
            'order_number' => 'M2W-TEST001',
            'tracking_token' => 'token-test-12345',
            'order_type' => 'delivery',
            'order_status' => Order::STATUS_PLACED,
            'customer_name' => 'John Reviewer',
            'customer_phone' => '9888877777',
            'subtotal' => 500.00,
            'delivery_charge' => 0.00,
            'packaging_charge' => 25.00,
            'tax_amount' => 26.25,
            'total_amount' => 551.25,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash_on_delivery',
            'amount' => 551.25,
            'status' => Payment::STATUS_PENDING,
        ]);

        // 1. View order list
        $listResponse = $this->actingAs($this->admin)->get(route('admin.orders.index'));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('M2W-TEST001');

        // 2. View order details
        $showResponse = $this->actingAs($this->admin)->get(route('admin.orders.show', $order->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('M2W-TEST001');

        // 3. Update status to accepted
        $updateStatusResponse = $this->actingAs($this->admin)->post(route('admin.orders.status', $order->id), [
            'status' => Order::STATUS_ACCEPTED,
            'comment' => 'Kitchen confirmed order',
        ]);

        $updateStatusResponse->assertRedirect();
        $this->assertEquals(Order::STATUS_ACCEPTED, $order->fresh()->order_status);

        // 4. View printable tax invoice
        $invoiceResponse = $this->actingAs($this->admin)->get(route('admin.orders.invoice', $order->id));
        $invoiceResponse->assertStatus(200);
        $invoiceResponse->assertSee('TAX INVOICE');
    }

    public function test_admin_can_view_reports_and_stream_csv_export(): void
    {
        $order = Order::create([
            'order_number' => 'M2W-REPORT01',
            'tracking_token' => 'token-report-12345',
            'order_type' => 'delivery',
            'order_status' => Order::STATUS_DELIVERED,
            'customer_name' => 'Report Customer',
            'customer_phone' => '9555544444',
            'subtotal' => 600.00,
            'delivery_charge' => 0.00,
            'packaging_charge' => 25.00,
            'tax_amount' => 31.25,
            'total_amount' => 656.25,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Kadhai Paneer',
            'quantity' => 2,
            'unit_price' => 300.00,
            'line_subtotal' => 600.00,
            'line_total' => 600.00,
        ]);

        // View reports index
        $indexResponse = $this->actingAs($this->admin)->get(route('admin.reports.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Financial & Order Performance', false);
        $indexResponse->assertSee('Kadhai Paneer');

        // Export CSV
        $csvResponse = $this->actingAs($this->admin)->get(route('admin.reports.export'));
        $csvResponse->assertStatus(200);
        $this->assertStringStartsWith('text/csv', $csvResponse->headers->get('content-type'));
    }

    public function test_admin_can_update_restaurant_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'restaurant_name' => 'My 2nd Wife Luxury Dining',
            'minimum_order_amount' => 250.00,
            'default_delivery_charge' => 50.00,
            'packaging_charge' => 30.00,
            'is_restaurant_open' => '1',
            'cod_enabled' => '1',
        ]);

        $response->assertRedirect();
        $this->assertEquals('My 2nd Wife Luxury Dining', RestaurantSetting::get('restaurant_name'));
        $this->assertEquals(250.00, (float) RestaurantSetting::get('minimum_order_amount'));
    }
}
