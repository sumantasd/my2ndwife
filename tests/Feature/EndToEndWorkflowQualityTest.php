<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\AddonGroup;
use App\Models\Address;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\RestaurantSetting;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndWorkflowQualityTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected Category $category;
    protected Product $product;
    protected ProductVariation $variation;
    protected AddonGroup $addonGroup;
    protected Addon $addon;
    protected Coupon $coupon;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed restaurant settings
        RestaurantSetting::set('restaurant_name', 'My 2nd Wife');
        RestaurantSetting::set('currency_symbol', '₹');
        RestaurantSetting::set('minimum_order_amount', 150.00);
        RestaurantSetting::set('default_delivery_charge', 40.00);
        RestaurantSetting::set('free_delivery_threshold', 500.00);
        RestaurantSetting::set('packaging_charge', 20.00);
        RestaurantSetting::set('tax_rate_percentage', 5.00);
        RestaurantSetting::set('is_accepting_orders', true);

        // Seed users
        $this->customer = User::create([
            'name' => 'Rahul Sen',
            'email' => 'rahul@example.com',
            'phone' => '9830112233',
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);

        $this->admin = User::create([
            'name' => 'Admin Chef',
            'email' => 'admin@my2ndwife.com',
            'phone' => '9830998877',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);

        // Seed menu
        $this->category = Category::create([
            'name' => 'Biryani Specialties',
            'slug' => 'biryani-specialties',
            'description' => 'Royal dum cooked biryani with fragrant spices',
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Awadhi Mutton Biryani',
            'slug' => 'awadhi-mutton-biryani',
            'description' => 'Tender mutton cooked with basmati rice in slow dum',
            'regular_price' => 450.00,
            'discount_price' => 399.00,
            'is_veg' => false,
            'is_available' => true,
            'is_featured' => true,
        ]);

        $this->variation = ProductVariation::create([
            'product_id' => $this->product->id,
            'name' => 'Family Pack (Serves 3)',
            'price' => 799.00,
            'is_default' => false,
            'is_active' => true,
        ]);

        $this->addonGroup = AddonGroup::create([
            'name' => 'Extra Accompaniments',
            'is_required' => false,
            'is_active' => true,
        ]);

        $this->addon = Addon::create([
            'addon_group_id' => $this->addonGroup->id,
            'name' => 'Extra Raita',
            'price' => 45.00,
            'is_active' => true,
        ]);

        $this->product->addonGroups()->attach($this->addonGroup->id);

        $this->coupon = Coupon::create([
            'code' => 'ROYAL50',
            'type' => 'fixed',
            'discount_amount' => 50.00,
            'min_order_amount' => 200.00,
            'max_discount_amount' => 50.00,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'expires_at' => now()->addDays(20),
        ]);
    }

    public function test_complete_customer_checkout_and_tracking_workflow(): void
    {
        // 1. Visit Homepage & Menu
        $homeRes = $this->get(route('home'));
        $homeRes->assertStatus(200);
        $homeRes->assertSee('Awadhi Mutton Biryani');

        $menuRes = $this->get(route('menu.index', ['category' => 'biryani-specialties']));
        $menuRes->assertStatus(200);
        $menuRes->assertSee('Awadhi Mutton Biryani');

        // 2. Add product with variation and addon to cart
        $addRes = $this->from(route('cart.index'))->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'variation_id' => $this->variation->id,
            'addons' => [$this->addon->id],
            'quantity' => 1,
            'instructions' => 'Make it mild spicy',
        ]);
        $addRes->assertRedirect(route('cart.index'));

        // 3. View cart & verify items and calculations
        $cartRes = $this->get(route('cart.index'));
        $cartRes->assertStatus(200);
        $cartRes->assertSee('Family Pack (Serves 3)');
        $cartRes->assertSee('Extra Raita');
        $cartRes->assertSee('Make it mild spicy');

        // 4. Apply coupon
        $couponRes = $this->post(route('cart.coupon.apply'), [
            'coupon_code' => 'ROYAL50',
        ]);
        $couponRes->assertRedirect(route('cart.index'));

        $cartAfterCoupon = $this->get(route('cart.index'));
        $cartAfterCoupon->assertSee('ROYAL50');
        $cartAfterCoupon->assertSee('-₹50.00');

        // 5. Customer Login and Checkout
        $this->actingAs($this->customer);

        $checkoutPageRes = $this->get(route('checkout.index'));
        $checkoutPageRes->assertStatus(200);
        $checkoutPageRes->assertSee('Checkout');

        // 6. Submit checkout
        $checkoutSubmitRes = $this->post(route('checkout.process'), [
            'order_type' => 'delivery',
            'customer_name' => 'Rahul Sen',
            'customer_phone' => '9830112233',
            'customer_email' => 'rahul@example.com',
            'order_schedule' => 'asap',
            'delivery_address_selection' => 'new',
            'address_line_1' => 'Flat 4B, Park Mansions',
            'city' => 'Kolkata',
            'postal_code' => '700016',
            'delivery_instructions' => 'Leave at door',
            'payment_method' => 'cash_on_delivery',
        ]);

        $checkoutSubmitRes->assertRedirect();
        
        // Assert order exists in database
        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('Rahul Sen', $order->customer_name);
        $this->assertEquals(1, $order->items->count());
        $this->assertEquals(50.00, $order->discount_amount);
        $this->assertEquals('cash_on_delivery', $order->payment?->payment_method);

        // 7. Cart should now be cleared
        $cartService = app(CartService::class);
        $this->assertEquals(0, $cartService->count());

        // 8. Track order page displays order details
        $trackRes = $this->get(route('order.track', ['token' => $order->tracking_token]));
        $trackRes->assertStatus(200);
        $trackRes->assertSee($order->order_number);
        $trackRes->assertSee('Tracking Live Order');

        // 9. Status polling endpoint returns valid JSON
        $jsonRes = $this->get(route('order.status.json', ['token' => $order->tracking_token]));
        $jsonRes->assertStatus(200);
        $jsonRes->assertJsonStructure([
            'status',
            'status_label',
            'progress_step',
            'is_paid',
        ]);
        $this->assertEquals('placed', $jsonRes->json('status'));
        $this->assertEquals(1, $jsonRes->json('progress_step'));

        // 10. Customer Account Order Details
        $detailRes = $this->get(route('account.orders.details', ['orderNumber' => $order->order_number]));
        $detailRes->assertStatus(200);
        $detailRes->assertSee($order->order_number);
        $detailRes->assertSee('Cancel Order');

        // 11. Customer can cancel order while in placed state
        $cancelRes = $this->post(route('account.orders.cancel', ['orderNumber' => $order->order_number]), [
            'reason' => 'Changed my mind',
        ]);
        $cancelRes->assertRedirect();
        $order->refresh();
        $this->assertEquals('cancelled', $order->order_status);
        $this->assertEquals('Changed my mind', $order->cancellation_reason);
    }

    public function test_security_access_control_and_authorization(): void
    {
        // 1. Guest cannot access customer account profile
        $guestRes = $this->get(route('account.profile'));
        $guestRes->assertRedirect(route('login'));

        // 2. Customer cannot access admin routes
        $this->actingAs($this->customer);
        $adminDashboardRes = $this->get(route('admin.dashboard'));
        $adminDashboardRes->assertStatus(403);

        // 3. Super Admin can access admin dashboard
        $this->actingAs($this->admin);
        $adminRes = $this->get(route('admin.dashboard'));
        $adminRes->assertStatus(200);
        $adminRes->assertSee('Dashboard');
    }

    public function test_address_management_and_profile_updates(): void
    {
        $this->actingAs($this->customer);

        // Create Address
        $addAddrRes = $this->from(route('account.addresses'))->post(route('account.addresses.store'), [
            'type' => 'home',
            'contact_name' => 'Rahul Sen',
            'contact_phone' => '9830112233',
            'address_line_1' => 'Tower 2, Flat 101, South City',
            'city' => 'Kolkata',
            'postal_code' => '700068',
            'delivery_instructions' => 'Ring bell twice',
            'is_default' => 1,
        ]);
        $addAddrRes->assertRedirect(route('account.addresses'));

        $address = Address::latest()->first();
        $this->assertNotNull($address);
        $this->assertEquals('Tower 2, Flat 101, South City', $address->address_line_1);
        $this->assertTrue((bool)$address->is_default);

        // Update Profile
        $profileRes = $this->from(route('account.profile'))->post(route('account.profile.update'), [
            'name' => 'Rahul Sen Gupta',
            'phone' => '9830119999',
        ]);
        $profileRes->assertRedirect(route('account.profile'));

        $this->customer->refresh();
        $this->assertEquals('Rahul Sen Gupta', $this->customer->name);
        $this->assertEquals('+91 9830119999', $this->customer->phone);
    }
}
