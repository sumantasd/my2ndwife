<?php

namespace Tests\Feature;

use App\Models\FooterLink;
use App\Models\Page;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFooterManagementModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@test.com',
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);
    }

    public function test_admin_sidebar_contains_footer_settings_link(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('admin.footer.index'));
        $response->assertSee('Footer Settings');
    }

    public function test_admin_can_access_footer_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.footer.index'));

        $response->assertStatus(200);
        $response->assertSee('Footer Settings & Navigation Links', false);
        $response->assertSee('Quick Links');
        $response->assertSee('Help & Support', false);
        $response->assertSee('Bottom Policy Bar');
        $response->assertSee('Social Media');
        $response->assertSee('Contact Information');
        $response->assertSee('App Downloads');
        $response->assertSee('Newsletter');
        $response->assertSee('Copyright & Credits', false);
        $response->assertSee('Section Toggles');
    }

    public function test_unauthorized_users_cannot_access_footer_settings(): void
    {
        // Guests redirected to login
        $guestRes = $this->get(route('admin.footer.index'));
        $guestRes->assertRedirect(route('login'));

        // Customer role gets 403 Forbidden
        $customerRes = $this->actingAs($this->customer)->get(route('admin.footer.index'));
        $customerRes->assertStatus(403);
    }

    public function test_admin_can_update_general_footer_settings_and_visibility(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.footer.general.update'), [
            'footer_description' => 'Special Kolkata gourmet meals delivered in hot insulated carriers.',
            'restaurant_phone' => '+91 99999 88888',
            'restaurant_email' => 'support@my2ndwife.com',
            'restaurant_address' => '12B Camac Street, Kolkata - 700017',
            'opening_time' => '10:30 AM',
            'closing_time' => '11:45 PM',
            'facebook_enabled' => '1',
            'facebook_url' => 'https://facebook.com/my2ndwifeofficial',
            'instagram_enabled' => '1',
            'instagram_url' => 'https://instagram.com/my2ndwifeofficial',
            'twitter_enabled' => '0',
            'footer_newsletter_enabled' => '1',
            'footer_newsletter_heading' => 'Stay Updated with Kolkata Flavors',
            'footer_newsletter_description' => 'Subscribe for secret chef discounts.',
            'footer_newsletter_placeholder' => 'Your primary email...',
            'footer_newsletter_button_text' => 'Join VIP Club',
            'footer_app_heading' => 'Order Faster with Mobile App',
            'footer_app_description' => 'Instant live tracking right to your doorstep.',
            'footer_app_download_enabled' => '1',
            'app_android_enabled' => '1',
            'app_android_url' => 'https://play.google.com/store/apps/details?id=com.my2ndwife.app',
            'app_android_button_label' => 'Get on Android',
            'app_pwa_enabled' => '1',
            'app_pwa_button_label' => 'Web App (PWA)',
            'footer_copyright_text' => 'Custom 2026 Copyright Notice.',
            'footer_dev_credit_text' => 'Tech Googly',
            'footer_dev_credit_url' => 'https://techgoogly.com/',
            'footer_quick_links_enabled' => '1',
            'footer_support_links_enabled' => '1',
            'footer_policy_links_enabled' => '1',
            'footer_payment_methods_enabled' => '1',
            'footer_decorations_enabled' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('Special Kolkata gourmet meals delivered in hot insulated carriers.', RestaurantSetting::get('footer_description'));
        $this->assertEquals('+91 99999 88888', RestaurantSetting::get('restaurant_phone'));
        $this->assertEquals('support@my2ndwife.com', RestaurantSetting::get('restaurant_email'));
        $this->assertEquals('Stay Updated with Kolkata Flavors', RestaurantSetting::get('footer_newsletter_heading'));
        $this->assertEquals('Join VIP Club', RestaurantSetting::get('footer_newsletter_button_text'));
        $this->assertEquals('Get on Android', RestaurantSetting::get('app_android_button_label'));

        // Verify public customer footer renders all updated fields
        $home = $this->get(route('home'));
        $home->assertStatus(200);
        $home->assertSee('Special Kolkata gourmet meals delivered in hot insulated carriers.');
        $home->assertSee('+91 99999 88888');
        $home->assertSee('support@my2ndwife.com');
        $home->assertSee('Stay Updated with Kolkata Flavors');
        $home->assertSee('Join VIP Club');
        $home->assertSee('Get on Android');
        $home->assertSee('https://play.google.com/store/apps/details?id=com.my2ndwife.app');
        $home->assertSee('Custom 2026 Copyright Notice.');
        $home->assertSee('Tech Googly');
        $home->assertSee('https://techgoogly.com/');
    }

    public function test_admin_can_add_custom_url_footer_link(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.footer.links.store'), [
            'section' => 'quick_links',
            'title' => 'Chef Specials Today',
            'link_type' => 'url',
            'url' => '/offers/chef-specials',
            'icon' => 'sparkles',
            'open_in_new_tab' => '1',
            'is_enabled' => '1',
            'display_order' => 15,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('footer_links', [
            'section' => 'quick_links',
            'title' => 'Chef Specials Today',
            'url' => '/offers/chef-specials',
            'open_in_new_tab' => true,
            'is_enabled' => true,
        ]);

        // Appears in public website footer
        $home = $this->get(route('home'));
        $home->assertSee('Chef Specials Today');
        $home->assertSee('/offers/chef-specials');
    }

    public function test_admin_can_add_cms_page_footer_link(): void
    {
        $page = Page::create([
            'title' => 'Kolkata Kitchen Heritage',
            'slug' => 'heritage',
            'content' => '<p>Heritage Bengali comfort foods since generations.</p>',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.footer.links.store'), [
            'section' => 'support_links',
            'title' => 'Culinary Heritage',
            'link_type' => 'page',
            'page_id' => $page->id,
            'icon' => 'book-open',
            'open_in_new_tab' => '0',
            'is_enabled' => '1',
            'display_order' => 20,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('footer_links', [
            'section' => 'support_links',
            'title' => 'Culinary Heritage',
            'page_id' => $page->id,
        ]);

        // Public footer links to the CMS page URL
        $home = $this->get(route('home'));
        $home->assertSee('Culinary Heritage');
        $home->assertSee($page->public_url);
    }

    public function test_admin_can_edit_footer_link(): void
    {
        $link = FooterLink::create([
            'section' => 'quick_links',
            'title' => 'Old Link Title',
            'url' => '/old-url',
            'open_in_new_tab' => false,
            'is_enabled' => true,
            'display_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.footer.links.update', $link), [
            'title' => 'Updated Menu Highlights',
            'link_type' => 'url',
            'url' => '/menu/highlights',
            'icon' => 'star',
            'open_in_new_tab' => '1',
            'is_enabled' => '1',
            'display_order' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('footer_links', [
            'id' => $link->id,
            'title' => 'Updated Menu Highlights',
            'url' => '/menu/highlights',
            'open_in_new_tab' => true,
        ]);
    }

    public function test_admin_can_toggle_footer_link_status(): void
    {
        $link = FooterLink::create([
            'section' => 'quick_links',
            'title' => 'Secret Deals',
            'url' => '/secret',
            'is_enabled' => true,
            'display_order' => 1,
        ]);

        // Toggle to false
        $response = $this->actingAs($this->admin)->patch(route('admin.footer.links.toggle', $link));
        $response->assertRedirect();

        $this->assertFalse($link->fresh()->is_enabled);

        // Does not show on customer storefront when disabled
        $this->flushSession();
        $home = $this->get(route('home'));
        $home->assertDontSee('Secret Deals');
    }

    public function test_admin_can_delete_footer_link(): void
    {
        $link = FooterLink::create([
            'section' => 'quick_links',
            'title' => 'Temporary Promo',
            'url' => '/promo',
            'is_enabled' => true,
            'display_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.footer.links.destroy', $link));
        $response->assertRedirect();

        $this->assertDatabaseMissing('footer_links', [
            'id' => $link->id,
        ]);
    }

    public function test_newsletter_subscription_remains_operational(): void
    {
        $response = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'vipsubscriber@kolkatafood.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'vipsubscriber@kolkatafood.com',
        ]);
    }

    public function test_tech_googly_credit_and_url_are_preserved_in_footer(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Developed By');
        $response->assertSee('Tech Googly');
        $response->assertSee('https://techgoogly.com/');
        $response->assertDontSee('Online & Delivering Across Kolkata');
        $response->assertDontSee('Online &amp; Delivering Across Kolkata');
    }

    public function test_restaurant_settings_footer_tab_redirects_to_canonical_footer_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.index', ['tab' => 'footer']));

        $response->assertRedirect(route('admin.footer.index'));
    }

    public function test_restaurant_settings_footer_section_post_redirects_to_canonical_footer_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            '_section' => 'footer',
            'footer_description' => 'Updated via legacy settings route',
        ]);

        $response->assertRedirect(route('admin.footer.index'));
        $this->assertEquals('Updated via legacy settings route', RestaurantSetting::get('footer_description'));
    }

    public function test_restaurant_settings_navigation_does_not_contain_duplicate_footer_tab(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        // Sidebar button for footer settings must not be present
        $response->assertDontSee('switchTab(\'footer\')', false);
        $response->assertDontSee('🦶 Footer Settings', false);
    }

    public function test_canonical_footer_settings_contains_all_responsive_tabs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.footer.index'));

        $response->assertStatus(200);
        $response->assertSee('Branding & Tagline', false);
        $response->assertSee('Quick Links', false);
        $response->assertSee('Help & Support', false);
        $response->assertSee('Bottom Policy Bar', false);
        $response->assertSee('Social Media', false);
        $response->assertSee('Contact Information', false);
        $response->assertSee('App Downloads', false);
        $response->assertSee('Newsletter', false);
        $response->assertSee('Copyright & Credits', false);
        $response->assertSee('Section Toggles', false);
    }
}
