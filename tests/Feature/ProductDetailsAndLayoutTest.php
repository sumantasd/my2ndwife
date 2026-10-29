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

class ProductDetailsAndLayoutTest extends TestCase
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
        RestaurantSetting::set('minimum_order_amount', 100.00);
        RestaurantSetting::set('announcement_bar_enabled', true);
        RestaurantSetting::set('announcement_bar_text', 'Free delivery on orders above ₹499!');

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->category = Category::create([
            'name' => 'Signature Thali',
            'slug' => 'signature-thali',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Special Royal Thali',
            'slug' => 'special-royal-thali',
            'regular_price' => 350.00,
            'discount_price' => 299.00,
            'is_veg' => true,
            'is_available' => true,
            'description' => 'A royal assortment of delicious delicacies served fresh.',
        ]);

        ProductVariation::create([
            'product_id' => $this->product->id,
            'name' => 'Standard Portion',
            'price' => 299.00,
            'is_default' => true,
            'is_active' => true,
            'display_order' => 1,
        ]);
    }

    /**
     * BUG 1 & 5: JavaScript does NOT appear as plain text in the layout
     */
    public function test_layout_renders_cleanly_without_javascript_leaking_into_body_html(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();

        $content = $response->getContent();

        // 1. Verify body tag opens cleanly with x-data="globalAppData()"
        $this->assertStringContainsString('x-data="globalAppData()"', $content);

        // 2. Extract content between <body and first header banner to ensure NO raw JS was dumped
        $this->assertMatchesRegularExpression('/<body[^>]*>\s*(<!--.*?-->\s*)*<div/s', $content);

        // 3. Verify that the raw JS leak string does NOT appear as visible plain text
        $this->assertStringNotContainsString('this.addToast(toast); window.addEventListener', $content);

        // 4. Verify promotional banner and header are rendered properly
        $response->assertSee('Free delivery on orders above ₹499!');
        $response->assertSee('My 2nd Wife');
    }

    /**
     * BUG 2 & 4: Product page displays price, button label, and quantity stepper correctly
     */
    public function test_product_page_displays_price_button_label_and_quantity_stepper(): void
    {
        $response = $this->get(route('food.show', $this->product->slug));

        $response->assertOk();

        // Product title & description
        $response->assertSee('Special Royal Thali');
        $response->assertSee('A royal assortment of delicious delicacies');

        // Product price display (Must have fallback numeric price inside, not just bare ₹)
        $response->assertSee('299.00');
        $response->assertSee('₹<span x-text="calculateTotal()">299.00</span>', false);

        // Add to Cart button label (Must have Add to Cart text, not just rupee symbol)
        $response->assertSee('Add to Cart');
        $response->assertSee('<span x-text="adding ? \'Adding...\' : \'Add to Cart\'">Add to Cart</span>', false);

        // Quantity stepper default
        $response->assertSee('<span class="mx-3 text-sm font-extrabold text-[#25232A]" x-text="quantity">1</span>', false);

        // Product customizer component
        $response->assertSee('x-data="productCustomizerData()"', false);
    }

    /**
     * BUG 3: Add to Cart AJAX submission updates cart, subtotal, and returns JSON
     */
    public function test_add_to_cart_ajax_submits_and_returns_clean_json_toast_payload(): void
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
            'product_name' => 'Special Royal Thali',
        ]);

        $this->assertEquals('Special Royal Thali added to cart!', $response->json('message'));
        $this->assertEquals(598.00, (float) $response->json('cart_subtotal'));

        // Verify cart service count
        $cartService = app(CartService::class);
        $this->assertEquals(2, $cartService->count());
    }

    /**
     * Add to cart prevents duplicate submission while loading
     */
    public function test_add_to_cart_handles_out_of_stock_product_with_error_json(): void
    {
        $this->product->update(['is_available' => false]);

        $response = $this->actingAs($this->customer)
            ->postJson(route('cart.add'), [
                'product_id' => $this->product->id,
                'quantity' => 1,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => false,
            'message' => 'This food item is currently unavailable.',
        ]);
    }
}
