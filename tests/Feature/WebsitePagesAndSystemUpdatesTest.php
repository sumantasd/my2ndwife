<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebsitePagesAndSystemUpdatesTest extends TestCase
{
    use RefreshDatabase;
    protected User $admin;
    protected User $rider;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'phone' => '+91 98000 00001',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->rider = User::factory()->create([
            'name' => 'Rider User',
            'email' => 'rider@test.com',
            'phone' => '+91 98765 12345',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_DELIVERY_PERSON,
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@test.com',
            'phone' => '+91 98300 54321',
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        RestaurantSetting::set('restaurant_name', 'My 2nd Wife');
        RestaurantSetting::set('restaurant_logo', 'storage/branding/logo.png');
        RestaurantSetting::set('restaurant_timezone', 'Asia/Kolkata');
        RestaurantSetting::set('app_android_url', '#');
    }

    public function test_delivery_login_page_does_not_show_return_to_customer_website(): void
    {
        $response = $this->get(route('delivery.login'));

        $response->assertStatus(200);
        $response->assertDontSee('Return to Customer Website');
        $response->assertSee('+91');
        $response->assertSee('Mobile Number');
        $response->assertSee('Email Address');
    }

    public function test_delivery_rider_can_login_with_phone_and_default_country_code(): void
    {
        $response = $this->post(route('delivery.login.post'), [
            'login_type' => 'phone',
            'country_code' => '+91',
            'phone' => '9876512345',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('delivery.dashboard'));
        $this->assertAuthenticatedAs($this->rider);
    }

    public function test_delivery_rider_can_login_with_formatted_phone_number(): void
    {
        $response = $this->post(route('delivery.login.post'), [
            'login_type' => 'phone',
            'country_code' => '+91',
            'phone' => '+91 98765 12345',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('delivery.dashboard'));
        $this->assertAuthenticatedAs($this->rider);
    }

    public function test_delivery_rider_login_fails_with_invalid_phone_number(): void
    {
        $response = $this->post(route('delivery.login.post'), [
            'login_type' => 'phone',
            'country_code' => '+91',
            'phone' => '999',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertGuest();
    }

    public function test_delivery_rider_can_login_with_email(): void
    {
        $response = $this->post(route('delivery.login.post'), [
            'login_type' => 'email',
            'email' => 'rider@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('delivery.dashboard'));
        $this->assertAuthenticatedAs($this->rider);
    }

    public function test_admin_settings_displays_active_ist_timezone(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Asia/Kolkata');
        $response->assertSee('India Standard Time (IST)');
        $response->assertSee('Active: Asia/Kolkata (IST)');
    }

    public function test_admin_sidebar_displays_official_logo(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('storage/branding/logo.png');
    }

    public function test_footer_displays_official_logo_and_about_us(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('storage/branding/logo.png');
        $response->assertSee('About Us');
        $response->assertDontSee('Staff Login');
    }

    public function test_footer_displays_tech_googly_link_and_no_online_delivering_text(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('https://techgoogly.com/');
        $response->assertSee('Tech Googly');
        $response->assertDontSee('Online &amp; Delivering Across Kolkata');
        $response->assertDontSee('Online & Delivering Across Kolkata');
    }

    public function test_public_about_page_renders_successfully(): void
    {
        $response = $this->get(route('page.about'));

        $response->assertStatus(200);
        $response->assertSee('About');
        $response->assertSee('My 2nd Wife');
    }

    public function test_public_can_view_published_cms_page(): void
    {
        $page = Page::create([
            'title' => 'Catering & Events',
            'slug' => 'catering-events',
            'content' => '<p>Special corporate catering services in Kolkata.</p>',
            'meta_title' => 'Catering | My 2nd Wife',
            'is_published' => true,
        ]);

        $response = $this->get(route('page.show', $page->slug));

        $response->assertStatus(200);
        $response->assertSee('Catering & Events');
        $response->assertSee('Special corporate catering services in Kolkata.');
    }

    public function test_public_cannot_view_unpublished_cms_page(): void
    {
        $page = Page::create([
            'title' => 'Secret Recipe Preview',
            'slug' => 'secret-recipe-preview',
            'content' => '<p>Secret biryani herbs.</p>',
            'is_published' => false,
        ]);

        $response = $this->get(route('page.show', $page->slug));

        $response->assertStatus(404);
    }

    public function test_admin_can_preview_unpublished_cms_page(): void
    {
        $page = Page::create([
            'title' => 'Secret Recipe Preview',
            'slug' => 'secret-recipe-preview-admin',
            'content' => '<p>Secret biryani herbs.</p>',
            'is_published' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('page.show', $page->slug));

        $response->assertStatus(200);
        $response->assertSee('Secret Recipe Preview');
    }

    public function test_admin_can_manage_pages_via_cms(): void
    {
        // 1. View Index
        $response = $this->actingAs($this->admin)->get(route('admin.pages.index'));
        $response->assertStatus(200);
        $response->assertSee('Managed Website Pages');

        // 2. Create Page
        $postResponse = $this->actingAs($this->admin)->post(route('admin.pages.store'), [
            'title' => 'Hygiene Standards',
            'slug' => 'hygiene-standards',
            'content' => '<p>Daily temperature checks and sanitized kitchen stations.</p>',
            'meta_title' => 'Hygiene Standards | My 2nd Wife',
            'is_published' => true,
            'display_order' => 8,
        ]);

        $postResponse->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseHas('pages', ['slug' => 'hygiene-standards']);

        $page = Page::where('slug', 'hygiene-standards')->first();

        // 3. Edit Page
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.pages.update', $page), [
            'title' => 'Hygiene & Cleanliness Standards',
            'slug' => 'hygiene-standards',
            'content' => '<p>Updated daily temperature checks.</p>',
            'is_published' => true,
        ]);

        $updateResponse->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseHas('pages', ['title' => 'Hygiene & Cleanliness Standards']);

        // 4. Toggle Status
        $toggleResponse = $this->actingAs($this->admin)->post(route('admin.pages.toggle', $page));
        $toggleResponse->assertRedirect();
        $this->assertFalse($page->fresh()->is_published);

        // 5. Delete Page
        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.pages.destroy', $page));
        $deleteResponse->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }
}
