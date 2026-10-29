<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\HomepageBanner;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use App\Services\CartService;
use App\Services\DeliveryService;
use Database\Seeders\CategoryAndProductSeeder;
use Database\Seeders\RestaurantSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestaurantMenuAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RestaurantSettingSeeder::class);
        $this->seed(CategoryAndProductSeeder::class);

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_tester@my2ndwife.com'],
            [
                'name' => 'Admin Tester',
                'password' => bcrypt('password123'),
                'phone' => '9830099991',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->customerUser = User::firstOrCreate(
            ['email' => 'customer_tester@my2ndwife.com'],
            [
                'name' => 'Customer Tester',
                'password' => bcrypt('password123'),
                'phone' => '9830099992',
                'role' => 'customer',
                'is_active' => true,
            ]
        );
    }

    public function test_seven_menu_categories_exist_with_exact_names(): void
    {
        $expectedCategories = [
            'Veg Thali',
            'Non-Veg Thali',
            'Main Course (Veg)',
            'Main Course (Non-Veg)',
            'Rice & Roti',
            'Add-Ons',
            'Beverages',
        ];

        foreach ($expectedCategories as $catName) {
            $cat = Category::where('name', $catName)->first();
            $this->assertNotNull($cat, "Category '{$catName}' should exist.");
            $this->assertTrue($cat->is_active, "Category '{$catName}' should be active.");
            $this->assertNotEmpty($cat->image, "Category '{$catName}' must have an image.");
        }

        $activeCount = Category::active()->count();
        $this->assertEquals(7, $activeCount, "There should be exactly 7 active categories.");
    }

    public function test_all_supplied_products_exist_with_exact_prices(): void
    {
        $expectedDishes = [
            // Veg Thali
            ['name' => 'Regular Veg Thali', 'price' => 80.00, 'category' => 'Veg Thali', 'is_veg' => true],
            ['name' => 'Special Veg Thali', 'price' => 120.00, 'category' => 'Veg Thali', 'is_veg' => true],

            // Non-Veg Thali
            ['name' => 'Egg Thali', 'price' => 100.00, 'category' => 'Non-Veg Thali', 'is_veg' => false],
            ['name' => 'Chicken Thali', 'price' => 160.00, 'category' => 'Non-Veg Thali', 'is_veg' => false],
            ['name' => 'Fish Thali', 'price' => 180.00, 'category' => 'Non-Veg Thali', 'is_veg' => false],

            // Main Course (Veg)
            ['name' => 'Paneer Butter Masala', 'price' => 140.00, 'category' => 'Main Course (Veg)', 'is_veg' => true],
            ['name' => 'Mixed Veg', 'price' => 100.00, 'category' => 'Main Course (Veg)', 'is_veg' => true],
            ['name' => 'Aloo Posto', 'price' => 90.00, 'category' => 'Main Course (Veg)', 'is_veg' => true],
            ['name' => 'Dal Tadka', 'price' => 80.00, 'category' => 'Main Course (Veg)', 'is_veg' => true],

            // Main Course (Non-Veg)
            ['name' => 'Chicken Curry', 'price' => 140.00, 'category' => 'Main Course (Non-Veg)', 'is_veg' => false],
            ['name' => 'Chicken Kosha', 'price' => 180.00, 'category' => 'Main Course (Non-Veg)', 'is_veg' => false],
            ['name' => 'Egg Curry (2 pcs)', 'price' => 90.00, 'category' => 'Main Course (Non-Veg)', 'is_veg' => false],
            ['name' => 'Fish Curry', 'price' => 160.00, 'category' => 'Main Course (Non-Veg)', 'is_veg' => false],

            // Rice & Roti
            ['name' => 'Plain Rice', 'price' => 40.00, 'category' => 'Rice & Roti', 'is_veg' => true],
            ['name' => 'Jeera Rice', 'price' => 60.00, 'category' => 'Rice & Roti', 'is_veg' => true],
            ['name' => 'Roti (1 pc)', 'price' => 8.00, 'category' => 'Rice & Roti', 'is_veg' => true],
            ['name' => 'Paratha (1 pc)', 'price' => 15.00, 'category' => 'Rice & Roti', 'is_veg' => true],

            // Add-Ons
            ['name' => 'Salad', 'price' => 20.00, 'category' => 'Add-Ons', 'is_veg' => true],
            ['name' => 'Papad', 'price' => 10.00, 'category' => 'Add-Ons', 'is_veg' => true],
            ['name' => 'Extra Dal', 'price' => 30.00, 'category' => 'Add-Ons', 'is_veg' => true],

            // Beverages
            ['name' => 'Mineral Water', 'price' => 20.00, 'category' => 'Beverages', 'is_veg' => true],
            ['name' => 'Buttermilk', 'price' => 25.00, 'category' => 'Beverages', 'is_veg' => true],
        ];

        foreach ($expectedDishes as $dish) {
            $product = Product::where('name', $dish['name'])->first();
            $this->assertNotNull($product, "Product '{$dish['name']}' should exist.");
            $this->assertEquals($dish['price'], (float) $product->regular_price, "Price for '{$dish['name']}' must be {$dish['price']}.");
            $this->assertEquals($dish['is_veg'], $product->is_veg, "Dietary flag for '{$dish['name']}' mismatch.");
            $this->assertEquals($dish['category'], $product->category->name, "Category for '{$dish['name']}' mismatch.");
            $this->assertTrue($product->is_available, "Product '{$dish['name']}' should be available.");
            $this->assertFileExists(public_path($product->main_image), "Image for '{$dish['name']}' must exist on disk.");
        }
    }

    public function test_seeder_is_idempotent_and_does_not_create_duplicates_or_overwrite_custom_prices(): void
    {
        $initialProductCount = Product::available()->count();
        $initialCategoryCount = Category::active()->count();

        // Admin changes price of Roti to ₹12.00
        $roti = Product::where('slug', 'roti-1-pc')->first();
        $roti->update(['regular_price' => 12.00]);

        // Re-run seeder
        $this->seed(CategoryAndProductSeeder::class);

        $this->assertEquals($initialProductCount, Product::available()->count(), "Seeder re-run must not create duplicate products.");
        $this->assertEquals($initialCategoryCount, Category::active()->count(), "Seeder re-run must not create duplicate categories.");

        // Custom admin price should NOT have been overwritten
        $rotiFresh = Product::where('slug', 'roti-1-pc')->first();
        $this->assertEquals(12.00, (float) $rotiFresh->regular_price, "Seeder must preserve customized admin prices.");

        // Reset price back to ₹8.00
        $rotiFresh->update(['regular_price' => 8.00]);
    }

    public function test_admin_can_update_restaurant_settings(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.settings.update'), [
            '_section' => 'general',
            'restaurant_name' => 'My 2nd Wife Kolkata',
            'restaurant_tagline' => 'Best Indian Thalis & Homestyle Curries',
            'minimum_order_amount' => 150.00,
            'is_restaurant_open' => '1',
        ]);

        $response->assertRedirect();
        $this->assertEquals('My 2nd Wife Kolkata', RestaurantSetting::get('restaurant_name'));
        $this->assertEquals('Best Indian Thalis & Homestyle Curries', RestaurantSetting::get('restaurant_tagline'));
        $this->assertEquals(150.00, (float) RestaurantSetting::get('minimum_order_amount'));

        // Reset
        RestaurantSetting::set('restaurant_name', 'My 2nd Wife', 'general', 'string');
    }

    public function test_unauthorized_customer_cannot_update_restaurant_settings(): void
    {
        $response = $this->actingAs($this->customerUser)->post(route('admin.settings.update'), [
            '_section' => 'general',
            'restaurant_name' => 'Hacked Restaurant',
        ]);

        $response->assertForbidden();
        $this->assertNotEquals('Hacked Restaurant', RestaurantSetting::get('restaurant_name'));
    }

    public function test_logo_upload_and_replacement_works(): void
    {
        Storage::fake('public');

        $fakeLogo = UploadedFile::fake()->image('test_logo.png', 300, 100);

        $response = $this->actingAs($this->adminUser)->post(route('admin.settings.update'), [
            '_section' => 'branding',
            'restaurant_logo' => $fakeLogo,
        ]);

        $response->assertRedirect();
        $savedPath = RestaurantSetting::get('restaurant_logo');
        $this->assertStringStartsWith('storage/branding/', $savedPath);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $savedPath));

        // Reset
        RestaurantSetting::set('restaurant_logo', 'images/defaults/logo.svg', 'branding', 'string');
    }

    public function test_invalid_image_upload_is_rejected(): void
    {
        Storage::fake('public');

        $fakeScript = UploadedFile::fake()->create('malicious.php', 10, 'application/x-php');

        $response = $this->actingAs($this->adminUser)->post(route('admin.settings.update'), [
            '_section' => 'branding',
            'restaurant_logo' => $fakeScript,
        ]);

        $response->assertSessionHasErrors(['restaurant_logo']);
    }

    public function test_blank_api_key_field_does_not_erase_saved_google_maps_key(): void
    {
        // Set an initial key
        RestaurantSetting::set('google_maps_api_key', 'AIzaSyOriginalSecretKey123', 'google_maps', 'string');

        // Admin submits form with blank or masked key
        $response = $this->actingAs($this->adminUser)->post(route('admin.settings.update'), [
            '_section' => 'google_maps',
            'google_maps_api_key' => '',
            'restaurant_latitude' => 22.5535,
            'restaurant_longitude' => 88.3533,
        ]);

        $response->assertRedirect();
        $this->assertEquals('AIzaSyOriginalSecretKey123', RestaurantSetting::get('google_maps_api_key'));

        // Admin submits masked dots '••••••••'
        $response = $this->actingAs($this->adminUser)->post(route('admin.settings.update'), [
            '_section' => 'google_maps',
            'google_maps_api_key' => '••••••••y123',
            'restaurant_latitude' => 22.5535,
            'restaurant_longitude' => 88.3533,
        ]);

        $this->assertEquals('AIzaSyOriginalSecretKey123', RestaurantSetting::get('google_maps_api_key'));
    }

    public function test_restaurant_coordinates_can_be_saved_manually(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.settings.update'), [
            '_section' => 'google_maps',
            'restaurant_latitude' => 22.5726,
            'restaurant_longitude' => 88.3639,
        ]);

        $response->assertRedirect();
        $this->assertEquals(22.5726, (float) RestaurantSetting::get('restaurant_latitude'));
        $this->assertEquals(88.3639, (float) RestaurantSetting::get('restaurant_longitude'));

        // Reset
        RestaurantSetting::set('restaurant_latitude', 22.5535, 'google_maps', 'float');
        RestaurantSetting::set('restaurant_longitude', 88.3533, 'google_maps', 'float');
    }

    public function test_delivery_radius_evaluation_and_enforcement(): void
    {
        $deliveryService = app(DeliveryService::class);

        // Restaurant is at 22.5535, 88.3533 with radius 5.0 km
        RestaurantSetting::set('restaurant_latitude', 22.5535, 'google_maps', 'float');
        RestaurantSetting::set('restaurant_longitude', 88.3533, 'google_maps', 'float');
        RestaurantSetting::set('delivery_radius_km', 5.0, 'delivery', 'float');
        RestaurantSetting::set('delivery_enabled', true, 'delivery', 'boolean');

        // Location 1: Park Street near restaurant (~1.0 km) -> Inside
        $insideCheck = $deliveryService->checkServiceability(22.5510, 88.3520);
        $this->assertTrue($insideCheck['serviceable'], "Coordinates 1 km away should be serviceable.");

        // Location 2: Kolkata Airport (~14.8 km) -> Outside 5km radius
        $outsideCheck = $deliveryService->checkServiceability(22.6547, 88.4467);
        $this->assertFalse($outsideCheck['serviceable'], "Coordinates 14.8 km away should NOT be serviceable for 5 km radius.");
        $this->assertStringContainsString('Takeaway is available', $outsideCheck['message']);
    }

    public function test_checkout_rejects_delivery_outside_radius_but_allows_takeaway(): void
    {
        // Configure 5.0 km radius
        RestaurantSetting::set('restaurant_latitude', 22.5535, 'google_maps', 'float');
        RestaurantSetting::set('restaurant_longitude', 88.3533, 'google_maps', 'float');
        RestaurantSetting::set('delivery_radius_km', 5.0, 'delivery', 'float');
        RestaurantSetting::set('delivery_enabled', true, 'delivery', 'boolean');
        RestaurantSetting::set('takeaway_enabled', true, 'delivery', 'boolean');
        RestaurantSetting::set('is_restaurant_open', true, 'general', 'boolean');
        RestaurantSetting::set('is_temporarily_closed', false, 'general', 'boolean');
        RestaurantSetting::set('minimum_order_amount', 80.00, 'general', 'float');

        // Add 2 Thalis to cart
        $cartService = app(CartService::class);
        $cartService->clear();
        $thali = Product::where('slug', 'regular-veg-thali')->first();
        $cartService->add($thali->id, 2); // 2 * 80 = 160.00

        // Attempt 1: Delivery to coordinates outside radius (Kolkata Airport: 22.6547, 88.4467 ~ 14.8 km)
        $response = $this->actingAs($this->customerUser)->post(route('checkout.process'), [
            'order_type' => 'delivery',
            'customer_name' => 'Subhojeet Roy',
            'customer_phone' => '9830012345',
            'delivery_address_selection' => 'new',
            'address_line_1' => 'Airport Gate 1, Dum Dum',
            'city' => 'Kolkata',
            'postal_code' => '700052',
            'delivery_latitude' => 22.6547,
            'delivery_longitude' => 88.4467,
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertSessionHasErrors(['delivery_address']);

        // Attempt 2: Takeaway from the same customer (should SUCCEED regardless of distance)
        $takeawayResponse = $this->actingAs($this->customerUser)->post(route('checkout.process'), [
            'order_type' => 'pickup',
            'customer_name' => 'Subhojeet Roy',
            'customer_phone' => '9830012345',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
        ]);

        $takeawayResponse->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Subhojeet Roy',
            'order_type' => 'pickup',
            'subtotal' => 160.00,
        ]);
    }

    public function test_delivery_fee_calculation_methods(): void
    {
        $deliveryService = app(DeliveryService::class);

        RestaurantSetting::set('free_delivery_threshold', 499.00, 'delivery', 'float');
        RestaurantSetting::set('default_delivery_charge', 30.00, 'delivery', 'float');

        // 1. Fixed fee below free threshold
        RestaurantSetting::set('delivery_fee_calculation_method', 'fixed', 'delivery', 'string');
        $fee = $deliveryService->calculateDeliveryFee(200.00, 4.0);
        $this->assertEquals(30.00, $fee, "Fixed delivery fee should be ₹30.00.");

        // 2. Free delivery when subtotal >= ₹499
        $freeFee = $deliveryService->calculateDeliveryFee(500.00, 4.0);
        $this->assertEquals(0.00, $freeFee, "Delivery must be free above threshold.");

        // 3. Per-km method: Base ₹30 + (4 km * ₹10/km) = ₹70
        RestaurantSetting::set('delivery_fee_calculation_method', 'per_km', 'delivery', 'string');
        RestaurantSetting::set('delivery_charge_per_km', 10.00, 'delivery', 'float');
        $perKmFee = $deliveryService->calculateDeliveryFee(200.00, 4.0);
        $this->assertEquals(70.00, $perKmFee, "Per-km fee should be base ₹30 + 4 km * ₹10 = ₹70.");

        // Reset
        RestaurantSetting::set('delivery_fee_calculation_method', 'fixed', 'delivery', 'string');
    }

    public function test_closed_status_rejects_asap_orders(): void
    {
        RestaurantSetting::set('is_temporarily_closed', true, 'general', 'boolean');
        RestaurantSetting::set('temporary_closed_notice', 'Closed for maintenance today.', 'general', 'string');

        $cartService = app(CartService::class);
        $cartService->clear();
        $thali = Product::where('slug', 'special-veg-thali')->first();
        $cartService->add($thali->id, 1);

        $response = $this->actingAs($this->customerUser)->post(route('checkout.process'), [
            'order_type' => 'pickup',
            'customer_name' => 'Priya Das',
            'customer_phone' => '9830055555',
            'order_schedule' => 'asap',
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertSessionHasErrors(['restaurant']);

        // Reset
        RestaurantSetting::set('is_temporarily_closed', false, 'general', 'boolean');
    }

    public function test_historical_orders_remain_unaltered_when_settings_change(): void
    {
        // Create an order with original settings
        $order = Order::create([
            'order_number' => 'HIST-001',
            'tracking_token' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $this->customerUser->id,
            'customer_name' => 'Historical Customer',
            'customer_phone' => '9830011111',
            'order_type' => 'delivery',
            'subtotal' => 200.00,
            'packaging_charge' => 25.00,
            'delivery_charge' => 30.00,
            'tax_amount' => 11.25,
            'total_amount' => 266.25,
            'order_status' => Order::STATUS_DELIVERED,
        ]);

        // Change global settings
        RestaurantSetting::set('default_delivery_charge', 60.00, 'delivery', 'float');
        RestaurantSetting::set('packaging_charge', 50.00, 'orders', 'float');

        // Reload the historical order
        $orderFresh = Order::find($order->id);
        $this->assertEquals(30.00, (float) $orderFresh->delivery_charge, "Historical delivery charge must not change.");
        $this->assertEquals(25.00, (float) $orderFresh->packaging_charge, "Historical packaging charge must not change.");
        $this->assertEquals(266.25, (float) $orderFresh->total_amount, "Historical total must remain accurate.");

        // Reset
        RestaurantSetting::set('default_delivery_charge', 30.00, 'delivery', 'float');
        RestaurantSetting::set('packaging_charge', 25.00, 'orders', 'float');
    }

    public function test_admin_settings_index_loads_successfully_without_route_error(): void
    {
        // 1. When banners exist
        HomepageBanner::create([
            'title' => 'Test Banner Display',
            'subtitle' => 'Special promo subtitle',
            'badge_text' => 'OFFER',
            'button_text' => 'Order Now',
            'button_url' => '/menu',
            'bg_gradient' => 'from-pink-600 to-rose-600',
            'image_path' => 'images/defaults/banner-placeholder.jpg',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.settings.index'));
        $response->assertStatus(200);
        $response->assertSee('Centralized Restaurant Settings');
        $response->assertSee('Test Banner Display');
        $response->assertSee('Restaurant Logo');
        $response->assertSee('Browser Favicon');
        $response->assertSee('Maximum Delivery Radius');
        $response->assertSee('Daily Opening Hours');
        $response->assertSee('Cash on Delivery');
        $response->assertSee('Razorpay');

        // Regression check: Ensure JavaScript code is not rendered as visible HTML body text
        $response->assertDontSee('{ this[targetProp] = e.target.result; }; reader.readAsDataURL(file); } }">', false);
        $response->assertDontSee('reader.readAsDataURL(file); } }">', false);
        $response->assertSee('function restaurantSettings(config)', false);
    }

    public function test_admin_settings_index_loads_when_banners_collection_is_empty(): void
    {
        HomepageBanner::truncate();

        $response = $this->actingAs($this->adminUser)->get(route('admin.settings.index'));
        $response->assertStatus(200);
        $response->assertSee('No promotional banners configured yet');
    }

    public function test_banner_creation_and_safe_deletion_with_storage_file(): void
    {
        Storage::fake('public');

        $fakeImage = UploadedFile::fake()->image('banner_hero.jpg', 1200, 400);

        // 1. Create banner
        $createResponse = $this->actingAs($this->adminUser)->post(route('admin.banners.store'), [
            'title' => 'Grand Biryani Fest',
            'subtitle' => 'Get free dessert with every handi biryani',
            'badge_text' => 'FESTIVE',
            'button_text' => 'Order Biryani',
            'button_url' => '/menu',
            'image' => $fakeImage,
            'display_order' => 1,
        ]);

        $createResponse->assertRedirect();
        $this->assertDatabaseHas('homepage_banners', [
            'title' => 'Grand Biryani Fest',
        ]);

        $banner = HomepageBanner::where('title', 'Grand Biryani Fest')->first();
        $this->assertNotNull($banner);
        $this->assertStringStartsWith('storage/banners/', $banner->image_path);

        $storagePath = str_replace('storage/', '', $banner->image_path);
        Storage::disk('public')->assertExists($storagePath);

        // 2. Delete banner safely
        $deleteResponse = $this->actingAs($this->adminUser)->delete(route('admin.banners.destroy', $banner->id));
        $deleteResponse->assertRedirect();

        $this->assertDatabaseMissing('homepage_banners', ['id' => $banner->id]);
        Storage::disk('public')->assertMissing($storagePath);
    }

    public function test_banner_update_with_image_replacement_and_safe_cleanup(): void
    {
        Storage::fake('public');

        $initialImage = UploadedFile::fake()->image('first_banner.jpg', 800, 400);

        // 1. Create banner with initial image
        $createResponse = $this->actingAs($this->adminUser)->post(route('admin.banners.store'), [
            'title' => 'Original Banner Title',
            'subtitle' => 'Original Subtitle',
            'image' => $initialImage,
        ]);
        $createResponse->assertRedirect();

        $banner = HomepageBanner::where('title', 'Original Banner Title')->first();
        $this->assertNotNull($banner);
        $oldStoragePath = str_replace('storage/', '', $banner->image_path);
        Storage::disk('public')->assertExists($oldStoragePath);

        // 2. Update with new image
        $newImage = UploadedFile::fake()->image('updated_banner.png', 800, 400);
        $updateResponse = $this->actingAs($this->adminUser)->put(route('admin.banners.update', $banner->id), [
            'title' => 'Updated Banner Title',
            'subtitle' => 'Updated Subtitle Offer',
            'button_text' => 'Grab Offer',
            'button_url' => '/offers',
            'image' => $newImage,
        ]);
        $updateResponse->assertRedirect();

        $banner->refresh();
        $this->assertSame('Updated Banner Title', $banner->title);
        $this->assertSame('Updated Subtitle Offer', $banner->subtitle);
        $this->assertSame('Grab Offer', $banner->button_text);
        $this->assertSame('/offers', $banner->button_url);

        // The old image was cleanly deleted, new image exists
        $newStoragePath = str_replace('storage/', '', $banner->image_path);
        $this->assertNotSame($oldStoragePath, $newStoragePath);
        Storage::disk('public')->assertMissing($oldStoragePath);
        Storage::disk('public')->assertExists($newStoragePath);
    }

    public function test_banner_toggle_active_status(): void
    {
        $banner = HomepageBanner::create([
            'title' => 'Toggle Status Test Banner',
            'image_path' => 'images/banners/banner-biryani.svg',
            'is_active' => true,
        ]);

        $this->assertTrue($banner->is_active);

        // Toggle to disabled
        $toggleResponse = $this->actingAs($this->adminUser)->patch(route('admin.banners.toggle', $banner->id));
        $toggleResponse->assertRedirect();

        $banner->refresh();
        $this->assertFalse($banner->is_active);

        // Toggle back to active
        $this->actingAs($this->adminUser)->patch(route('admin.banners.toggle', $banner->id));
        $banner->refresh();
        $this->assertTrue($banner->is_active);
    }

    public function test_updating_only_product_name_preserves_availability(): void
    {
        $product = Product::where('is_available', true)->first();
        $this->assertNotNull($product);
        $this->assertTrue($product->is_available);

        $response = $this->actingAs($this->adminUser)->put(route('admin.products.update', $product->id), [
            'name' => 'Royal Signature Kacchi Biryani',
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $fresh = $product->fresh();
        $this->assertEquals('Royal Signature Kacchi Biryani', $fresh->name);
        $this->assertTrue($fresh->is_available, 'Product availability must remain true when only name is updated.');
    }

    public function test_updating_only_product_price_preserves_availability(): void
    {
        $product = Product::where('is_available', true)->first();
        $this->assertNotNull($product);
        $this->assertTrue($product->is_available);

        $response = $this->actingAs($this->adminUser)->put(route('admin.products.update', $product->id), [
            'regular_price' => 389.00,
            'discount_price' => 349.00,
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $fresh = $product->fresh();
        $this->assertEquals(389.00, (float) $fresh->regular_price);
        $this->assertTrue($fresh->is_available, 'Product availability must remain true when only price is updated.');
    }

    public function test_updating_product_image_or_description_preserves_availability(): void
    {
        $product = Product::where('is_available', true)->first();
        $this->assertNotNull($product);
        $this->assertTrue($product->is_available);

        $response = $this->actingAs($this->adminUser)->put(route('admin.products.update', $product->id), [
            'description' => 'Aromatic basmati rice cooked with saffron, tender meat, and caramelized onions.',
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $fresh = $product->fresh();
        $this->assertStringContainsString('Aromatic basmati rice', $fresh->description);
        $this->assertTrue($fresh->is_available, 'Product availability must remain true when only description is updated.');
    }

    public function test_admin_can_explicitly_disable_and_enable_product_availability(): void
    {
        $product = Product::where('is_available', true)->first();
        $this->assertNotNull($product);

        // 1. Explicitly disable availability (mark as Sold Out)
        $disableResponse = $this->actingAs($this->adminUser)->put(route('admin.products.update', $product->id), [
            'category_id' => $product->category_id,
            'name' => $product->name,
            'regular_price' => $product->regular_price,
            'prep_time_minutes' => $product->prep_time_minutes,
            'is_available' => 0,
        ]);

        $disableResponse->assertRedirect(route('admin.products.index'));
        $this->assertFalse($product->fresh()->is_available, 'Product must be marked unavailable when is_available is explicitly 0.');

        // 2. Explicitly re-enable availability (mark as In Stock)
        $enableResponse = $this->actingAs($this->adminUser)->put(route('admin.products.update', $product->id), [
            'category_id' => $product->category_id,
            'name' => $product->name,
            'regular_price' => $product->regular_price,
            'prep_time_minutes' => $product->prep_time_minutes,
            'is_available' => 1,
        ]);

        $enableResponse->assertRedirect(route('admin.products.index'));
        $this->assertTrue($product->fresh()->is_available, 'Product must be marked available when is_available is explicitly 1.');
    }

    public function test_product_without_stock_system_does_not_become_sold_out_from_missing_quantity(): void
    {
        $product = Product::where('is_available', true)->first();
        $this->assertNotNull($product);

        // Submitting an edit without any stock_quantity / quantity field
        $response = $this->actingAs($this->adminUser)->put(route('admin.products.update', $product->id), [
            'category_id' => $product->category_id,
            'name' => $product->name . ' Special',
            'regular_price' => $product->regular_price,
            'prep_time_minutes' => $product->prep_time_minutes,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertTrue($product->fresh()->is_available, 'Product without inventory tracking must not become Sold Out when quantity is absent.');
    }

    public function test_genuinely_unavailable_product_is_blocked_at_cart_and_checkout(): void
    {
        $product = Product::where('is_available', true)->first();
        $this->assertNotNull($product);

        // Mark product unavailable (Sold Out)
        $product->update(['is_available' => false]);

        // 1. Attempt to add to cart via CartService returns failure
        $cartService = app(CartService::class);
        $result = $cartService->add($product->id, 1);

        $this->assertFalse($result['success']);
        $this->assertEquals('This food item is currently unavailable.', $result['message']);

        // 2. Customer JSON request to cart endpoint is rejected
        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $response->assertJson([
            'success' => false,
            'message' => 'This food item is currently unavailable.',
        ]);
    }

    public function test_updating_product_does_not_unintentionally_overwrite_other_fields(): void
    {
        $product = Product::where('is_veg', true)->first();
        $originalPrepTime = $product->prep_time_minutes;
        $originalCategoryId = $product->category_id;
        $originalVeg = $product->is_veg;

        $response = $this->actingAs($this->adminUser)->put(route('admin.products.update', $product->id), [
            'name' => 'Kadhai Paneer Delight',
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $fresh = $product->fresh();
        $this->assertEquals('Kadhai Paneer Delight', $fresh->name);
        $this->assertEquals($originalPrepTime, $fresh->prep_time_minutes);
        $this->assertEquals($originalCategoryId, $fresh->category_id);
        $this->assertEquals($originalVeg, $fresh->is_veg);
    }

    public function test_general_settings_saves_logo_address_and_coordinates(): void
    {
        Storage::fake('public');

        $fakeLogo = UploadedFile::fake()->image('restaurant_logo_main.png', 400, 150);
        $fakeFavicon = UploadedFile::fake()->image('favicon_ico.png', 32, 32);

        $response = $this->actingAs($this->adminUser)->post(route('admin.settings.update'), [
            '_section' => 'general',
            'restaurant_name' => 'My 2nd Wife Fine Dining',
            'restaurant_tagline' => 'Flavors You Can Never Resist',
            'restaurant_description' => 'Fine dining restaurant serving exquisite Indian specialties.',
            'restaurant_logo' => $fakeLogo,
            'restaurant_favicon' => $fakeFavicon,
            'restaurant_email' => 'contact@my2ndwife.com',
            'restaurant_phone' => '+91 98300 55555',
            'restaurant_whatsapp' => '+91 98300 55555',
            'restaurant_address' => '45, Shakespeare Sarani, Kolkata',
            'restaurant_city' => 'Kolkata',
            'restaurant_state' => 'West Bengal',
            'restaurant_pincode' => '700017',
            'restaurant_latitude' => 22.5450,
            'restaurant_longitude' => 88.3620,
        ]);

        $response->assertRedirect();

        $this->assertEquals('My 2nd Wife Fine Dining', RestaurantSetting::get('restaurant_name'));
        $this->assertEquals('45, Shakespeare Sarani, Kolkata', RestaurantSetting::get('restaurant_address'));
        $this->assertEquals('Kolkata', RestaurantSetting::get('restaurant_city'));
        $this->assertEquals('700017', RestaurantSetting::get('restaurant_pincode'));
        $this->assertEquals(22.5450, (float) RestaurantSetting::get('restaurant_latitude'));
        $this->assertEquals(88.3620, (float) RestaurantSetting::get('restaurant_longitude'));
        $this->assertEquals('+91 98300 55555', RestaurantSetting::get('restaurant_phone'));

        $logoPath = RestaurantSetting::get('restaurant_logo');
        $this->assertStringStartsWith('storage/branding/', $logoPath);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $logoPath));
    }

    public function test_saving_one_section_preserves_unrelated_sections(): void
    {
        // 1. Establish known settings in payment and general
        RestaurantSetting::set('cod_enabled', true, 'payment', 'boolean');
        RestaurantSetting::set('restaurant_name', 'My 2nd Wife Stable Name', 'general', 'string');

        // 2. Save only the delivery section
        $response = $this->actingAs($this->adminUser)->post(route('admin.settings.update'), [
            '_section' => 'delivery',
            'delivery_radius_km' => 8.5,
            'default_delivery_charge' => 45.00,
            'delivery_enabled' => '1',
            'takeaway_enabled' => '1',
        ]);

        $response->assertRedirect();

        // 3. Verify delivery updated
        $this->assertEquals(8.5, (float) RestaurantSetting::get('delivery_radius_km'));
        $this->assertEquals(45.00, (float) RestaurantSetting::get('default_delivery_charge'));

        // 4. Verify unrelated settings in payment and general are untouched
        $this->assertTrue(RestaurantSetting::get('cod_enabled'));
        $this->assertEquals('My 2nd Wife Stable Name', RestaurantSetting::get('restaurant_name'));
    }

    public function test_opening_hours_can_be_configured_and_persists(): void
    {
        $hoursConfig = [
            'monday' => ['is_open' => '1', 'open_time' => '10:00', 'close_time' => '23:00'],
            'tuesday' => ['is_open' => '0', 'open_time' => '11:00', 'close_time' => '22:00'], // closed
            'wednesday' => ['is_open' => '1', 'open_time' => '11:00', 'close_time' => '23:30'],
            'thursday' => ['is_open' => '1', 'open_time' => '11:00', 'close_time' => '23:30'],
            'friday' => ['is_open' => '1', 'open_time' => '11:00', 'close_time' => '00:00'],
            'saturday' => ['is_open' => '1', 'open_time' => '11:00', 'close_time' => '00:30'],
            'sunday' => ['is_open' => '1', 'open_time' => '11:00', 'close_time' => '00:30'],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.settings.update'), [
            '_section' => 'hours',
            'opening_hours' => $hoursConfig,
        ]);

        $response->assertRedirect();

        $savedHours = RestaurantSetting::get('opening_hours_json');
        $this->assertIsArray($savedHours);
        $this->assertTrue($savedHours['monday']['is_open']);
        $this->assertEquals('10:00', $savedHours['monday']['open_time']);
        $this->assertFalse($savedHours['tuesday']['is_open'], 'Tuesday should be marked closed.');
        $this->assertTrue($savedHours['wednesday']['is_open']);
    }
}
