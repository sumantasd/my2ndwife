<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteFooterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'CategoryAndProductSeeder']);
        $this->artisan('db:seed', ['--class' => 'RestaurantSettingSeeder']);
        $this->artisan('db:seed', ['--class' => 'HomepageBannerSeeder']);
    }

    public function test_footer_renders_on_homepage_with_reference_styling(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('#0D1127');
        $response->assertSee('aria-label="My 2nd Wife Home"', false);
        $response->assertSee('Quick Links');
        $response->assertSee('Help & Support', false);
        $response->assertSee('Contact Information');
        $response->assertSee('Order Faster with Our Mobile App');
        $response->assertSee('Subscribe to our newsletter');
    }

    public function test_excessive_blank_space_above_footer_is_eliminated(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        // The rogue 80px mobile spacer above the footer must be absent
        $response->assertDontSee('class="h-20 md:hidden"', false);
        // Main should use flex-1 rather than pb-24
        $response->assertDontSee('class="flex-grow pb-24 md:pb-0"', false);
        $response->assertSee('class="flex-1"', false);
        // Footer should have natural margin rather than legacy mt-20 mb-14
        $response->assertDontSee('mt-20 mb-14');
        $response->assertSee('mt-8 sm:mt-12');
    }

    public function test_footer_uses_centralized_restaurant_settings(): void
    {
        RestaurantSetting::set('restaurant_phone', '+91 99999 88888', 'contact');
        RestaurantSetting::set('restaurant_email', 'custom_support@my2ndwife.com', 'contact');
        RestaurantSetting::set('restaurant_address', '100 Gourmet Way, Central Kolkata', 'contact');
        RestaurantSetting::set('opening_time', '10:00 AM', 'hours');
        RestaurantSetting::set('closing_time', '11:00 PM', 'hours');

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('+91 99999 88888');
        $response->assertSee('custom_support@my2ndwife.com');
        $response->assertSee('100 Gourmet Way, Central Kolkata');
        $response->assertSee('10:00 AM - 11:00 PM');
    }

    public function test_all_help_and_support_pages_render_successfully(): void
    {
        // 1. Terms & Conditions
        $termsRes = $this->get(route('page.terms'));
        $termsRes->assertStatus(200);
        $termsRes->assertSee('Terms & Conditions');

        // 2. Privacy Policy
        $privacyRes = $this->get(route('page.privacy'));
        $privacyRes->assertStatus(200);
        $privacyRes->assertSee('Privacy Policy');

        // 3. Refund Policy
        $refundRes = $this->get(route('page.refund'));
        $refundRes->assertStatus(200);
        $refundRes->assertSee('Refund & Cancellation');

        // 4. FAQ
        $faqRes = $this->get(route('page.faq'));
        $faqRes->assertStatus(200);
        $faqRes->assertSee('Frequently Asked Questions');

        // 5. Contact
        $contactRes = $this->get(route('page.contact'));
        $contactRes->assertStatus(200);
        $contactRes->assertSee('Contact My 2nd Wife');
    }

    public function test_newsletter_subscription_with_valid_email(): void
    {
        $response = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'gourmet_lover@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'already_subscribed' => false,
        ]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'gourmet_lover@example.com',
        ]);
    }

    public function test_newsletter_subscription_handles_duplicate_email(): void
    {
        NewsletterSubscriber::create([
            'email' => 'duplicate@example.com',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'duplicate@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'already_subscribed' => true,
        ]);

        $this->assertEquals(1, NewsletterSubscriber::where('email', 'duplicate@example.com')->count());
    }

    public function test_newsletter_subscription_validates_invalid_email(): void
    {
        $response = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_admin_can_update_footer_settings(): void
    {
        $admin = User::where('role', 'admin')->first() ?: User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            '_section' => 'footer',
            'footer_description' => 'Custom footer description from admin testing suite.',
            'footer_newsletter_enabled' => '1',
            'footer_app_download_enabled' => '1',
            'app_android_url' => 'https://play.google.com/store/apps/details?id=com.my2ndwife',
            'restaurant_whatsapp' => '+91 98888 77777',
        ]);

        $response->assertRedirect();

        $this->assertEquals('Custom footer description from admin testing suite.', RestaurantSetting::get('footer_description'));
        $this->assertEquals('+91 98888 77777', RestaurantSetting::get('restaurant_whatsapp'));

        // Verify custom description renders on public homepage
        $homeRes = $this->get(route('home'));
        $homeRes->assertSee('Custom footer description from admin testing suite.');
    }
}
