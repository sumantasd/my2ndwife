<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RestaurantSetting::set('restaurant_name', 'My 2nd Wife');
        RestaurantSetting::set('currency_symbol', '₹');
        RestaurantSetting::set('is_accepting_orders', true);
    }

    public function test_api_can_fetch_categories_and_products(): void
    {
        $category = Category::create([
            'name' => 'Desserts & Sweets',
            'slug' => 'desserts-sweets',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Shahi Tukda',
            'slug' => 'shahi-tukda',
            'regular_price' => 150.00,
            'discount_price' => 120.00,
            'is_veg' => true,
            'is_available' => true,
        ]);

        // 1. Fetch categories
        $catResponse = $this->getJson('/api/v1/categories');
        $catResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['name' => 'Desserts & Sweets']);

        // 2. Fetch products
        $prodResponse = $this->getJson('/api/v1/products');
        $prodResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['name' => 'Shahi Tukda']);
    }

    public function test_api_customer_registration_and_login_issues_sanctum_token(): void
    {
        // 1. Register via API
        $regResponse = $this->postJson('/api/v1/auth/register', [
            'name' => 'Mobile User',
            'email' => 'mobile@example.com',
            'phone' => '9888877777',
            'password' => 'password123',
        ]);

        $regResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'name']]);

        // 2. Login via API
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'mobile@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $token = $loginResponse->json('token');
        $this->assertNotEmpty($token);

        // 3. Access authenticated profile using Bearer token
        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/profile');

        $profileResponse->assertStatus(200)
            ->assertJsonPath('user.email', 'mobile@example.com');
    }

    public function test_api_can_track_order_via_public_token(): void
    {
        $order = Order::create([
            'order_number' => 'M2W-API001',
            'tracking_token' => 'token-api-tracking-abc123',
            'order_type' => 'delivery',
            'order_status' => Order::STATUS_PREPARING,
            'customer_name' => 'Mobile Diner',
            'customer_phone' => '9111122222',
            'subtotal' => 300.00,
            'delivery_charge' => 40.00,
            'packaging_charge' => 25.00,
            'tax_amount' => 16.25,
            'total_amount' => 381.25,
        ]);

        $response = $this->getJson('/api/v1/orders/track/' . $order->tracking_token);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.order_number', 'M2W-API001')
            ->assertJsonPath('order.status', Order::STATUS_PREPARING);
    }
}
