<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageLoadingAndCloakTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RestaurantSettingSeeder']);
        $this->artisan('db:seed', ['--class' => 'HomepageBannerSeeder']);
        $this->artisan('db:seed', ['--class' => 'CategoryAndProductSeeder']);
    }

    public function test_customer_layout_contains_critical_xcloak_style_in_head(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        // Verify head contains critical x-cloak rule to prevent initial paint ghost overlays
        $response->assertSee('[x-cloak]', false);
        $response->assertSee('display: none !important;', false);

        // Verify full-screen backdrop containers have x-cloak and inline display: none
        $response->assertSee('x-show="cartDrawerOpen" x-cloak style="display: none;"', false);
        $response->assertSee('x-show="quickModalOpen" x-cloak style="display: none;"', false);
    }

    public function test_admin_layout_contains_critical_xcloak_style_in_head(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Verify head contains critical x-cloak rule
        $response->assertSee('[x-cloak]', false);
        $response->assertSee('display: none !important;', false);

        // Verify sidebar mobile backdrop and modal are cloaked with display: none
        $response->assertSee('x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak style="display: none;"', false);
        $response->assertSee('x-show="techGooglyModalOpen"', false);
        $response->assertSee('style="display: none;"', false);
    }

    public function test_internal_pages_render_without_uncloaked_overlays(): void
    {
        $pages = [
            route('home'),
            route('menu.index'),
            route('offers.index'),
            route('search.index'),
            route('cart.index'),
            route('page.terms'),
            route('page.privacy'),
            route('page.faq'),
            route('page.contact'),
        ];

        foreach ($pages as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $response->assertSee('[x-cloak]', false);
            // Ensure cartDrawerOpen is always cloaked
            $response->assertSee('x-show="cartDrawerOpen" x-cloak style="display: none;"', false);
        }
    }

    public function test_checkout_page_with_active_cart_renders_with_cloaked_fields(): void
    {
        $product = \App\Models\Product::first();

        // Add item to cart
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->get(route('checkout.index'));
        $response->assertStatus(200);

        // Verify head cloak
        $response->assertSee('[x-cloak]', false);

        // Verify scheduled delivery slot is cloaked by default
        $response->assertSee('x-show="scheduleMode === \'scheduled\'" x-cloak style="display: none;"', false);

        // Verify service message alert is cloaked initially
        $response->assertSee('x-show="serviceCheckMessage" x-cloak style="display: none;"', false);
    }
}
