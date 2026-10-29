<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\RestaurantSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodKingAuditRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Product $dish;
    protected Coupon $coupon;

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

        $this->dish = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Shahi Dum Biryani',
            'slug' => 'shahi-dum-biryani',
            'description' => 'Hyderabadi style slow cooked dum biryani',
            'regular_price' => 499.00,
            'discount_price' => 420.00,
            'is_veg' => false,
            'is_available' => true,
            'is_featured' => true,
        ]);

        $this->coupon = Coupon::create([
            'code' => 'TASTE20',
            'type' => 'percentage',
            'discount_amount' => 20.00,
            'min_order_amount' => 200.00,
            'max_discount_amount' => 100.00,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'expires_at' => now()->addDays(30),
        ]);
    }

    public function test_offers_page_renders_active_deals_and_coupons(): void
    {
        $response = $this->get(route('offers.index'));

        $response->assertStatus(200);
        $response->assertSee('All Offers');
        $response->assertSee('TASTE20');
        $response->assertSee('Shahi Dum Biryani');
    }

    public function test_offer_details_page_renders_coupon_info(): void
    {
        $response = $this->get(route('offers.show', ['slug' => 'taste20']));

        $response->assertStatus(200);
        $response->assertSee('TASTE20');
        $response->assertSee('Shahi Dum Biryani');
    }

    public function test_search_page_returns_and_filters_products(): void
    {
        $response = $this->get(route('search.index'));
        $response->assertStatus(200);
        $response->assertSee('Search Food Dishes');

        $searchResponse = $this->get(route('search.index', ['q' => 'Biryani']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Shahi Dum Biryani');
    }

    public function test_foodking_layout_contains_slideover_cart_and_navigation(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('My 2nd Wife');
        $response->assertSee('cartDrawerOpen');
        $response->assertSee('quickModalOpen');
    }
}
