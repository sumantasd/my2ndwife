<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\AddonGroup;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\RestaurantSetting;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuAndCartTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Product $product;
    protected Product $simpleProduct;
    protected ProductVariation $variation;
    protected AddonGroup $addonGroup;
    protected Addon $addon;

    protected function setUp(): void
    {
        parent::setUp();

        RestaurantSetting::set('restaurant_name', 'My 2nd Wife');
        RestaurantSetting::set('currency_symbol', '₹');
        RestaurantSetting::set('minimum_order_amount', 199.00);
        RestaurantSetting::set('default_delivery_charge', 40.00);
        RestaurantSetting::set('free_delivery_threshold', 499.00);
        RestaurantSetting::set('packaging_charge', 25.00);
        RestaurantSetting::set('tax_rate_percentage', 5.00);
        RestaurantSetting::set('is_accepting_orders', true);

        $this->category = Category::create([
            'name' => 'Royal Biryani',
            'slug' => 'royal-biryani',
            'description' => 'Fragrant basmati rice delicacies',
            'is_active' => true,
            'display_order' => 1,
        ]);

        // Product with variations
        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Dum Biryani',
            'slug' => 'dum-biryani',
            'description' => 'Hyderabadi style slow cooked dum biryani',
            'regular_price' => 499.00,
            'discount_price' => 450.00,
            'is_veg' => false,
            'is_available' => true,
            'is_featured' => true,
        ]);

        $this->variation = ProductVariation::create([
            'product_id' => $this->product->id,
            'name' => 'Full / Family Pack',
            'price' => 450.00,
            'is_active' => true,
        ]);

        $this->addonGroup = AddonGroup::create([
            'name' => 'Sides & Extras',
            'selection_type' => 'multiple',
            'is_required' => false,
            'is_active' => true,
        ]);

        $this->addon = Addon::create([
            'addon_group_id' => $this->addonGroup->id,
            'name' => 'Extra Mirchi Ka Salan',
            'price' => 30.00,
            'is_active' => true,
        ]);

        $this->product->addonGroups()->attach($this->addonGroup->id);

        // Simple product without variations
        $this->simpleProduct = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Butter Naan',
            'slug' => 'butter-naan',
            'description' => 'Soft tandoor baked bread with butter',
            'regular_price' => 60.00,
            'discount_price' => 50.00,
            'is_veg' => true,
            'is_available' => true,
            'is_featured' => false,
        ]);
    }

    public function test_menu_page_displays_active_categories_and_dishes(): void
    {
        $response = $this->get(route('menu.index'));

        $response->assertStatus(200);
        $response->assertSee('Royal Biryani');
        $response->assertSee('Dum Biryani');
        $response->assertSee('Butter Naan');
    }

    public function test_product_details_page_loads_with_variations_and_addons(): void
    {
        $response = $this->get(route('food.show', $this->product->slug));

        $response->assertStatus(200);
        $response->assertSee('Dum Biryani');
        $response->assertSee('Full / Family Pack');
        $response->assertSee('Extra Mirchi Ka Salan');
    }

    public function test_customer_can_add_item_with_variations_and_addons_to_cart(): void
    {
        $response = $this->from(route('cart.index'))->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'variation_id' => $this->variation->id,
            'addons' => [$this->addon->id],
            'quantity' => 2,
            'instructions' => 'Make it spicy',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        $cartService = app(CartService::class);
        $totals = $cartService->calculateTotals();

        $this->assertEquals(2, $totals['items_count']);
        // Variation price 450 + addon 30 = 480 per unit * 2 = 960
        $this->assertEquals(960.00, $totals['subtotal']);
    }

    public function test_cart_service_calculates_fees_and_taxes_correctly(): void
    {
        $cartService = app(CartService::class);
        $cartService->add(
            productId: $this->product->id,
            quantity: 1
        );

        $totals = $cartService->calculateTotals();

        // 1 item variation price = 450.00
        // Subtotal = 450.00 (< 499 threshold, delivery = 40.00)
        // Packaging = 25.00
        // Taxable = 450 + 25 = 475.00. 5% tax = 23.75
        // Total = 450 + 40 + 25 + 23.75 = 538.75
        $this->assertEquals(450.00, $totals['subtotal']);
        $this->assertEquals(40.00, $totals['delivery_charge']);
        $this->assertEquals(25.00, $totals['packaging_charge']);
        $this->assertEquals(23.75, $totals['tax_amount']);
        $this->assertEquals(538.75, $totals['total_amount']);
    }

    public function test_free_delivery_threshold_is_applied_when_order_exceeds_threshold(): void
    {
        $cartService = app(CartService::class);
        $cartService->add(
            productId: $this->product->id,
            quantity: 2 // 2 * 450 = 900 > 499 threshold
        );

        $totals = $cartService->calculateTotals();

        $this->assertEquals(900.00, $totals['subtotal']);
        $this->assertEquals(0.00, $totals['delivery_charge']); // Free delivery!
    }

    public function test_valid_coupon_discounts_order_correctly(): void
    {
        // 10% coupon with no cap
        Coupon::create([
            'code' => 'TASTE10',
            'type' => 'percentage',
            'discount_amount' => 10.00,
            'min_order_amount' => 100.00,
            'max_discount_amount' => null,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'expires_at' => now()->addDays(10),
        ]);

        $cartService = app(CartService::class);
        $cartService->add(
            productId: $this->product->id,
            quantity: 2 // 2 * 450 = 900
        );

        $response = $this->from(route('cart.index'))->post(route('cart.coupon.apply'), [
            'coupon_code' => 'TASTE10',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        $totals = $cartService->calculateTotals();

        // 10% of 900 = 90.00 discount
        $this->assertEquals(90.00, $totals['discount_amount']);
    }

    public function test_invalid_coupon_code_is_rejected(): void
    {
        $cartService = app(CartService::class);
        $cartService->add(
            productId: $this->product->id,
            quantity: 1
        );

        $response = $this->from(route('cart.index'))->post(route('cart.coupon.apply'), [
            'coupon_code' => 'INVALIDCODE99',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_customer_can_update_quantity_and_remove_cart_item(): void
    {
        $cartService = app(CartService::class);
        $cartService->add(
            productId: $this->product->id,
            quantity: 1
        );

        $rawCart = $cartService->getRawCart();
        $itemKey = array_key_first($rawCart);
        $this->assertNotNull($itemKey);

        // Update quantity to 4
        $updateResponse = $this->from(route('cart.index'))->post(route('cart.update'), [
            'item_key' => $itemKey,
            'quantity' => 4,
        ]);
        $updateResponse->assertRedirect(route('cart.index'));

        $totals = $cartService->calculateTotals();
        $this->assertEquals(4, $totals['items_count']);

        // Remove item
        $removeResponse = $this->from(route('cart.index'))->post(route('cart.remove'), [
            'item_key' => $itemKey,
        ]);
        $removeResponse->assertRedirect(route('cart.index'));

        $totalsAfterRemove = $cartService->calculateTotals();
        $this->assertEquals(0, $totalsAfterRemove['items_count']);
    }
}
