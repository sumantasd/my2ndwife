<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\RestaurantSetting;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImmediateCartUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Category $category;
    protected Product $thali;
    protected Product $biryani;

    protected function setUp(): void
    {
        parent::setUp();

        RestaurantSetting::set('restaurant_name', 'My 2nd Wife');
        RestaurantSetting::set('currency_symbol', '₹');
        RestaurantSetting::set('minimum_order_amount', 199.00);
        RestaurantSetting::set('default_delivery_charge', 40.00);
        RestaurantSetting::set('free_delivery_threshold', 499.00);
        RestaurantSetting::set('packaging_charge', 25.00);
        RestaurantSetting::set('tax_enabled', true);
        RestaurantSetting::set('tax_rate_percentage', 5.00);
        RestaurantSetting::set('is_accepting_orders', true);
        RestaurantSetting::set('is_restaurant_open', true);

        $this->customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $this->category = Category::create([
            'name' => 'Authentic Thalis',
            'slug' => 'authentic-thalis',
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->thali = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Regular Veg Thali',
            'slug' => 'regular-veg-thali',
            'description' => 'Dal, Seasonal Sabzi, 4 Roti, Rice & Salad',
            'regular_price' => 120.00,
            'discount_price' => 99.00,
            'is_veg' => true,
            'is_available' => true,
        ]);

        $this->biryani = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Royal Mutton Biryani',
            'slug' => 'royal-mutton-biryani',
            'description' => 'Aromatic Kolkata Biryani with mutton and potato',
            'regular_price' => 380.00,
            'discount_price' => 349.00,
            'is_veg' => false,
            'is_available' => true,
        ]);
    }

    public function test_ajax_add_to_cart_returns_complete_totals_and_items_payload(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson(route('cart.add'), [
                'product_id' => $this->thali->id,
                'quantity' => 1,
                'instructions' => 'Less oil please',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'cart_count' => 1,
            'product_name' => 'Regular Veg Thali',
        ]);

        $this->assertEquals(99.00, (float) $response->json('cart_subtotal'));
        $this->assertNotEmpty($response->json('totals'));
        $this->assertNotEmpty($response->json('items'));

        $item = $response->json('items.0');
        $this->assertEquals('Regular Veg Thali', $item['product_name']);
        $this->assertEquals(1, $item['quantity']);
        $this->assertEquals(99.00, (float) $item['line_subtotal']);
        $this->assertEquals('Less oil please', $item['instructions']);
    }

    public function test_adding_regular_veg_thali_and_another_dish_updates_and_merges(): void
    {
        // 1. Add Regular Veg Thali (Qty: 1)
        $res1 = $this->actingAs($this->customer)
            ->postJson(route('cart.add'), [
                'product_id' => $this->thali->id,
                'quantity' => 1,
            ]);
        $res1->assertOk();
        $this->assertEquals(1, $res1->json('cart_count'));
        $this->assertCount(1, $res1->json('items'));

        // 2. Add Royal Mutton Biryani (Qty: 1)
        $res2 = $this->actingAs($this->customer)
            ->postJson(route('cart.add'), [
                'product_id' => $this->biryani->id,
                'quantity' => 1,
            ]);
        $res2->assertOk();
        $this->assertEquals(2, $res2->json('cart_count'));
        $this->assertCount(2, $res2->json('items'));

        // 3. Add Regular Veg Thali again (Qty: 2) -> Should merge quantity to 3
        $res3 = $this->actingAs($this->customer)
            ->postJson(route('cart.add'), [
                'product_id' => $this->thali->id,
                'quantity' => 2,
            ]);
        $res3->assertOk();
        $this->assertEquals(4, $res3->json('cart_count'));
        $this->assertCount(2, $res3->json('items'));

        // Verify Regular Veg Thali has merged quantity of 3
        $items = collect($res3->json('items'));
        $thaliItem = $items->firstWhere('product_id', $this->thali->id);
        $this->assertEquals(3, $thaliItem['quantity']);
        $this->assertEquals(297.00, (float) $thaliItem['line_subtotal']);
    }

    public function test_ajax_update_quantity_recalculates_totals_and_items(): void
    {
        // Add item
        $addRes = $this->actingAs($this->customer)
            ->postJson(route('cart.add'), [
                'product_id' => $this->thali->id,
                'quantity' => 1,
            ]);
        $addRes->assertOk();
        $itemKey = $addRes->json('items.0.key');
        $this->assertNotNull($itemKey);

        // Update quantity to 4 via AJAX
        $updateRes = $this->actingAs($this->customer)
            ->postJson(route('cart.update'), [
                'item_key' => $itemKey,
                'quantity' => 4,
            ]);

        $updateRes->assertOk();
        $updateRes->assertJson([
            'success' => true,
            'cart_count' => 4,
        ]);

        $this->assertEquals(396.00, (float) $updateRes->json('cart_subtotal'));
        $item = $updateRes->json('items.0');
        $this->assertEquals(4, $item['quantity']);
        $this->assertEquals(396.00, (float) $item['line_subtotal']);
    }

    public function test_ajax_remove_item_updates_cart_and_returns_empty_state(): void
    {
        // Add item
        $addRes = $this->actingAs($this->customer)
            ->postJson(route('cart.add'), [
                'product_id' => $this->thali->id,
                'quantity' => 1,
            ]);
        $itemKey = $addRes->json('items.0.key');

        // Remove item via AJAX
        $removeRes = $this->actingAs($this->customer)
            ->postJson(route('cart.remove'), [
                'item_key' => $itemKey,
            ]);

        $removeRes->assertOk();
        $removeRes->assertJson([
            'success' => true,
            'cart_count' => 0,
        ]);
        $this->assertEmpty($removeRes->json('items'));
        $this->assertEquals(0.00, (float) $removeRes->json('cart_subtotal'));
    }

    public function test_cart_summary_endpoint_supports_delivery_and_pickup_order_types(): void
    {
        // Add item worth 99 (below 499 free delivery threshold)
        $this->actingAs($this->customer)
            ->postJson(route('cart.add'), [
                'product_id' => $this->thali->id,
                'quantity' => 1,
            ]);

        // 1. Delivery summary -> Has delivery fee
        $deliverySummary = $this->actingAs($this->customer)
            ->getJson(route('cart.summary', ['order_type' => 'delivery']));
        $deliverySummary->assertOk();
        $this->assertEquals(1, $deliverySummary->json('cart_count'));
        $this->assertGreaterThan(0, (float) $deliverySummary->json('totals.delivery_charge'));

        // 2. Pickup (Takeaway) summary -> Delivery fee must be 0
        $pickupSummary = $this->actingAs($this->customer)
            ->getJson(route('cart.summary', ['order_type' => 'pickup']));
        $pickupSummary->assertOk();
        $this->assertEquals(1, $pickupSummary->json('cart_count'));
        $this->assertEquals(0.00, (float) $pickupSummary->json('totals.delivery_charge'));
    }

    public function test_layout_contains_reactive_cart_drawer_and_modal_bindings(): void
    {
        $response = $this->actingAs($this->customer)->get(route('home'));
        $response->assertOk();

        // 1. Drawer backdrop and modal cloaking
        $response->assertSee('x-show="cartDrawerOpen" x-cloak style="display: none;"', false);
        $response->assertSee('x-show="quickModalOpen" x-cloak style="display: none;"', false);

        // 2. Reactive cart count badge
        $response->assertSee('x-text="cartCount"', false);

        // 3. Dynamic cart items loop
        $response->assertSee('x-for="item in cartItems"', false);
        $response->assertSee('x-text="item.product_name"', false);
        $response->assertSee('x-text="item.quantity"', false);

        // 4. Stepper and remove methods without full page reload
        $response->assertSee('@click="updateCartQuantity(item.key, item.quantity - 1)"', false);
        $response->assertSee('@click="updateCartQuantity(item.key, item.quantity + 1)"', false);
        $response->assertSee('@click="removeCartItem(item.key)"', false);

        // 5. Empty cart message conditionally displayed
        $response->assertSee('x-show="!cartItems || cartItems.length === 0"', false);
        $response->assertSee('Your cart is empty');

        // 6. Delivery vs Takeaway order type toggle
        $response->assertSee('@click="setOrderType(\'delivery\')"', false);
        $response->assertSee('@click="setOrderType(\'pickup\')"', false);
    }
}
