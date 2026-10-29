<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFooterCreditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RestaurantSettingSeeder']);
    }

    public function test_admin_footer_displays_permanent_tech_googly_credit_and_modal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // 1. Exact footer credit text
        $response->assertSee('Crafted & Developed by Tech Googly | Proprietary Code & Rights', false);

        // 2. Exact modal title
        $response->assertSee('Software Development & Proprietary Rights Notice', false);

        // 3. Exact legal & proprietary body notice
        $response->assertSee('This software system has been designed and developed by <strong>Tech Googly</strong>.', false);
        $response->assertSee('All rights, title, and interest in the original source code, custom-developed modules, system architecture, and proprietary components of this software are reserved exclusively by Tech Googly, subject to applicable law, contractual agreements, and third-party license terms.', false);
        $response->assertSee("Unauthorized copying, reproduction, redistribution, resale, or commercial exploitation of Tech Googly's proprietary code and components without prior written authorization is prohibited, subject to applicable law and contractual rights.", false);
        $response->assertSee('2026 Tech Googly. All Rights Reserved.', false);

        // 4. Clickable link pointing to techgoogly.com
        $response->assertSee('https://techgoogly.com', false);

        // 5. Modal triggers & accessibility
        $response->assertSee('techGooglyModalOpen = true', false);
        $response->assertSee('role="dialog"', false);
        $response->assertSee('aria-modal="true"', false);
    }

    public function test_customer_storefront_does_not_contain_admin_tech_googly_credit(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Crafted & Developed by Tech Googly | Proprietary Code & Rights', false);
        $response->assertDontSee('Software Development & Proprietary Rights Notice', false);
    }

    public function test_credit_is_displayed_consistently_across_admin_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Test on Admin Orders
        $ordersRes = $this->actingAs($admin)->get(route('admin.orders.index'));
        $ordersRes->assertStatus(200);
        $ordersRes->assertSee('Crafted & Developed by Tech Googly | Proprietary Code & Rights', false);

        // Test on Admin Settings
        $settingsRes = $this->actingAs($admin)->get(route('admin.settings.index'));
        $settingsRes->assertStatus(200);
        $settingsRes->assertSee('Crafted & Developed by Tech Googly | Proprietary Code & Rights', false);

        // Test on Admin Categories
        $categoriesRes = $this->actingAs($admin)->get(route('admin.categories.index'));
        $categoriesRes->assertStatus(200);
        $categoriesRes->assertSee('Crafted & Developed by Tech Googly | Proprietary Code & Rights', false);
    }
}
