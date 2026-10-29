<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialBrandingAndFooterManagementTest extends TestCase
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

        RestaurantSetting::set('restaurant_name', 'My 2nd Wife');
        RestaurantSetting::set('restaurant_logo', 'storage/branding/xsVhiGkcR8Y0dguSaDl6QVARBw5KWEgoXxWD476N.png');
        RestaurantSetting::set('restaurant_logo_dark', 'storage/branding/aqrfj8cQRmrFunQEx5igo5Kmpcm09VkzCagNgzLk.png');
        RestaurantSetting::set('footer_description', 'Authentic culinary delicacies delivered piping hot across Kolkata.');
        RestaurantSetting::set('facebook_enabled', true);
        RestaurantSetting::set('facebook_url', 'https://facebook.com/my2ndwife');
        RestaurantSetting::set('instagram_enabled', true);
        RestaurantSetting::set('instagram_url', 'https://instagram.com/my2ndwife');
        RestaurantSetting::set('twitter_enabled', true);
        RestaurantSetting::set('twitter_url', 'https://x.com/my2ndwife');
        RestaurantSetting::set('youtube_enabled', true);
        RestaurantSetting::set('youtube_url', 'https://youtube.com/@my2ndwife');
        RestaurantSetting::set('linkedin_enabled', true);
        RestaurantSetting::set('linkedin_url', 'https://linkedin.com/company/my2ndwife');
        RestaurantSetting::set('whatsapp_enabled', true);
        RestaurantSetting::set('restaurant_whatsapp', '+91 98300 12345');
        RestaurantSetting::set('footer_copyright_text', 'All rights reserved.');
    }

    public function test_footer_displays_only_complete_official_logo_image(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // Footer reuses the configured dark logo image
        $response->assertSee('storage/branding/aqrfj8cQRmrFunQEx5igo5Kmpcm09VkzCagNgzLk.png');

        // Does NOT have rounded square wrapper with duplicate text beside it
        $response->assertDontSee('<span class="block text-[10px] font-bold text-gray-400 tracking-[0.2em] uppercase mt-1">', false);
        $response->assertDontSee('h-12 w-12 rounded-2xl bg-white/10 p-1.5 flex items-center justify-center', false);
    }

    public function test_admin_sidebar_displays_complete_official_logo(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Sidebar reuses official logo
        $response->assertSee('storage/branding/aqrfj8cQRmrFunQEx5igo5Kmpcm09VkzCagNgzLk.png');
        $response->assertSee('Admin Portal');

        // Does not have legacy generic 2W square
        $response->assertDontSee('w-10 h-10 rounded-xl bg-gradient-to-br from-[#F50070] to-[#FF3D96] flex items-center justify-center font-black', false);
    }

    public function test_all_footer_social_media_icons_are_visible_inline_svgs(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // Social profile links and safe target attributes
        $response->assertSee('href="https://facebook.com/my2ndwife"', false);
        $response->assertSee('href="https://instagram.com/my2ndwife"', false);
        $response->assertSee('href="https://x.com/my2ndwife"', false);
        $response->assertSee('href="https://youtube.com/@my2ndwife"', false);
        $response->assertSee('href="https://linkedin.com/company/my2ndwife"', false);
        $response->assertSee('href="https://wa.me/919830012345"', false);

        // Accessible labels and safe link attributes
        $response->assertSee('aria-label="Follow My 2nd Wife on Facebook"', false);
        $response->assertSee('aria-label="Follow My 2nd Wife on Instagram"', false);
        $response->assertSee('aria-label="Follow My 2nd Wife on Twitter / X"', false);
        $response->assertSee('aria-label="Subscribe to My 2nd Wife on YouTube"', false);
        $response->assertSee('aria-label="Connect with My 2nd Wife on LinkedIn"', false);
        $response->assertSee('aria-label="Chat with My 2nd Wife on WhatsApp"', false);
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_disabled_or_empty_social_links_are_hidden_cleanly(): void
    {
        RestaurantSetting::set('facebook_enabled', false);
        RestaurantSetting::set('linkedin_url', '');

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('href="https://facebook.com/my2ndwife"', false);
        $response->assertDontSee('aria-label="Connect with My 2nd Wife on LinkedIn"', false);
    }

    public function test_admin_can_manage_footer_settings_persistently(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            '_section' => 'footer',
            'footer_description' => 'Updated gourmet kitchen footer description testing.',
            'facebook_enabled' => '1',
            'facebook_url' => 'https://facebook.com/updatedpage',
            'instagram_enabled' => '1',
            'instagram_url' => 'https://instagram.com/updatedinsta',
            'linkedin_enabled' => '1',
            'linkedin_url' => 'https://linkedin.com/company/updatedcompany',
            'footer_copyright_text' => 'Custom copyright notice 2026.',
            'footer_quick_links_enabled' => '1',
            'footer_support_links_enabled' => '1',
            'footer_app_download_enabled' => '1',
            'app_android_url' => 'https://play.google.com/store/apps/details?id=com.my2ndwife.app',
        ]);

        $response->assertRedirect();

        $this->assertEquals('Updated gourmet kitchen footer description testing.', RestaurantSetting::get('footer_description'));
        $this->assertEquals('https://facebook.com/updatedpage', RestaurantSetting::get('facebook_url'));
        $this->assertEquals('Custom copyright notice 2026.', RestaurantSetting::get('footer_copyright_text'));

        // Check public render
        $home = $this->get(route('home'));
        $home->assertSee('Updated gourmet kitchen footer description testing.');
        $home->assertSee('Custom copyright notice 2026.');
        $home->assertSee('Tech Googly');
    }

    public function test_admin_can_edit_cms_page_and_public_displays_formatted_content(): void
    {
        $page = Page::create([
            'title' => 'Our Gourmet Story',
            'slug' => 'our-story',
            'content' => '<h2>Authentic Bengali Delicacies</h2><p>Cooked with pure mustard oil and royal whole spices.</p><ul><li>Fresh Daily Catch</li><li>Zero Artificial Flavors</li></ul>',
            'is_published' => true,
            'display_order' => 10,
        ]);

        // Edit via Admin CMS
        $response = $this->actingAs($this->admin)->put(route('admin.pages.update', $page), [
            'title' => 'Our Gourmet Story Updated',
            'slug' => 'our-story',
            'content' => '<h2>Updated Bengali Delicacies</h2><p>Crafted fresh by master chefs.</p><ul><li>Insulated Delivery</li><li>Hot on Arrival</li></ul>',
            'is_published' => '1',
            'display_order' => 10,
        ]);

        $response->assertRedirect(route('admin.pages.index'));

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Our Gourmet Story Updated',
        ]);

        // Public page displays sanitized formatted HTML
        $publicRes = $this->get(route('page.show', 'our-story'));
        $publicRes->assertStatus(200);
        $publicRes->assertSee('Updated Bengali Delicacies');
        $publicRes->assertSee('Crafted fresh by master chefs.');
        $publicRes->assertSee('Insulated Delivery');
        $publicRes->assertSee('Hot on Arrival');
    }

    public function test_unauthorized_users_cannot_access_or_edit_website_pages(): void
    {
        $page = Page::create([
            'title' => 'Confidential Page',
            'slug' => 'confidential',
            'content' => '<p>Secret</p>',
            'is_published' => true,
        ]);

        // Guest is redirected
        $guestRes = $this->get(route('admin.pages.index'));
        $guestRes->assertRedirect(route('login'));

        $guestPut = $this->put(route('admin.pages.update', $page), [
            'title' => 'Hacked',
            'slug' => 'confidential',
            'content' => '<p>Hacked</p>',
        ]);
        $guestPut->assertRedirect(route('login'));

        // Customer role gets 403 Forbidden
        $customerRes = $this->actingAs($this->customer)->get(route('admin.pages.index'));
        $customerRes->assertStatus(403);
    }
}
