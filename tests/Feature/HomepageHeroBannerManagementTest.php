<?php

namespace Tests\Feature;

use App\Models\HomepageBanner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomepageHeroBannerManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RestaurantSettingSeeder::class);
        $this->seed(\Database\Seeders\CategoryAndProductSeeder::class);

        $this->adminUser = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->customerUser = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_hero_banners_index(): void
    {
        $banner = HomepageBanner::create([
            'title' => 'Sample Hero Showcase',
            'badge_text' => 'HOT DEAL',
            'subtitle' => 'Delicious food delivered fast',
            'image_path' => 'images/banners/banner-biryani.svg',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.hero-banners.index'));

        $response->assertStatus(200);
        $response->assertSee('Homepage Banners');
        $response->assertSee('Sample Hero Showcase');
    }

    public function test_guest_or_customer_cannot_access_hero_banners(): void
    {
        // 1. Guest redirected to login
        $guestResponse = $this->get(route('admin.hero-banners.index'));
        $guestResponse->assertRedirect('/login');

        // 2. Customer receives 403 Forbidden
        $customerResponse = $this->actingAs($this->customerUser)->get(route('admin.hero-banners.index'));
        $customerResponse->assertStatus(403);
    }

    public function test_admin_can_create_new_hero_banner_with_all_fields_and_images(): void
    {
        Storage::fake('public');

        $desktopImage = UploadedFile::fake()->image('desktop_hero.jpg', 2000, 700);
        $mobileImage = UploadedFile::fake()->image('mobile_hero.jpg', 900, 1200);

        $response = $this->actingAs($this->adminUser)->post(route('admin.hero-banners.store'), [
            'name' => 'Royal Festive Banner',
            'badge_text' => 'SAVORY AND SATISFYING',
            'title' => 'Flat 15% Off on Main Course',
            'subtitle' => 'Handcrafted royal curries & fragrant basmati rice.',
            'button_text' => 'Order Now',
            'button_url' => '/menu',
            'secondary_text' => 'Delivered hot & fresh in 30 mins',
            'bg_gradient' => 'from-[#FF006B] via-[#E60060] to-[#1F1F39]',
            'text_color' => '#FFFFFF',
            'image' => $desktopImage,
            'mobile_image' => $mobileImage,
            'display_order' => 1,
            'is_active' => 1,
            'starts_at' => now()->subDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('admin.hero-banners.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('homepage_banners', [
            'name' => 'Royal Festive Banner',
            'title' => 'Flat 15% Off on Main Course',
            'badge_text' => 'SAVORY AND SATISFYING',
            'button_text' => 'Order Now',
            'button_url' => '/menu',
            'secondary_text' => 'Delivered hot & fresh in 30 mins',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $banner = HomepageBanner::where('title', 'Flat 15% Off on Main Course')->first();
        $this->assertNotNull($banner);
        $this->assertNotNull($banner->image_path);
        $this->assertNotNull($banner->mobile_image_path);

        $desktopStorage = str_replace('storage/', '', $banner->image_path);
        $mobileStorage = str_replace('storage/', '', $banner->mobile_image_path);

        Storage::disk('public')->assertExists($desktopStorage);
        Storage::disk('public')->assertExists($mobileStorage);
    }

    public function test_admin_can_edit_and_update_hero_banner_with_image_replacement(): void
    {
        Storage::fake('public');

        $initialImage = UploadedFile::fake()->image('original.jpg', 1200, 400);

        $banner = HomepageBanner::create([
            'title' => 'Initial Title',
            'badge_text' => 'DEAL',
            'image_path' => 'storage/' . $initialImage->store('banners', 'public'),
            'display_order' => 5,
            'is_active' => true,
        ]);

        $oldPath = str_replace('storage/', '', $banner->image_path);
        Storage::disk('public')->assertExists($oldPath);

        // Update with new title and new image
        $newImage = UploadedFile::fake()->image('replacement.jpg', 1200, 400);
        $updateResponse = $this->actingAs($this->adminUser)->put(route('admin.hero-banners.update', $banner->id), [
            'title' => 'Updated Headline Grand Feast',
            'badge_text' => 'LIMITED TIME',
            'subtitle' => 'New seasonal delight',
            'button_text' => 'Claim Feast',
            'button_url' => '/offers',
            'image' => $newImage,
            'display_order' => 2,
            'is_active' => 1,
        ]);

        $updateResponse->assertRedirect(route('admin.hero-banners.index'));

        $banner->refresh();
        $this->assertSame('Updated Headline Grand Feast', $banner->title);
        $this->assertSame('LIMITED TIME', $banner->badge_text);
        $this->assertSame('Claim Feast', $banner->button_text);
        $this->assertSame('/offers', $banner->button_url);
        $this->assertSame(2, $banner->display_order);

        $newPath = str_replace('storage/', '', $banner->image_path);
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_admin_can_toggle_hero_banner_status(): void
    {
        $banner = HomepageBanner::create([
            'title' => 'Toggle Status Banner',
            'is_active' => true,
        ]);

        $this->assertTrue($banner->is_active);

        $toggleResponse = $this->actingAs($this->adminUser)->patch(route('admin.hero-banners.toggle', $banner->id));
        $toggleResponse->assertRedirect();

        $banner->refresh();
        $this->assertFalse($banner->is_active);

        $this->actingAs($this->adminUser)->patch(route('admin.hero-banners.toggle', $banner->id));
        $banner->refresh();
        $this->assertTrue($banner->is_active);
    }

    public function test_admin_can_delete_hero_banner_safely(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('temp.jpg', 800, 400);
        $path = $image->store('banners', 'public');

        $banner = HomepageBanner::create([
            'title' => 'Banner To Delete',
            'image_path' => 'storage/' . $path,
            'is_active' => true,
        ]);

        Storage::disk('public')->assertExists($path);

        $deleteResponse = $this->actingAs($this->adminUser)->delete(route('admin.hero-banners.destroy', $banner->id));
        $deleteResponse->assertRedirect(route('admin.hero-banners.index'));

        $this->assertDatabaseMissing('homepage_banners', ['id' => $banner->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_hero_banner_appears_dynamically_on_customer_homepage(): void
    {
        HomepageBanner::truncate();

        HomepageBanner::create([
            'name' => 'Royal Dum Biryani Sensation',
            'title' => 'Royal Dum Biryani Sensation',
            'alt_text' => 'Authentic Kolkata Royal Dum Biryani Special Offer',
            'button_text' => 'Order Biryani Now',
            'button_url' => '/menu?search=biryani',
            'image_path' => 'images/banners/hero-banner-biryani-2000x700.svg',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        // Verify 20:7 container is rendered
        $response->assertSee('homepage-banner');
        // Verify banner image URL is loaded
        $response->assertSee('images/banners/hero-banner-biryani-2000x700.svg');
        // Verify alt text is rendered for accessibility & SEO
        $response->assertSee('Authentic Kolkata Royal Dum Biryani Special Offer');
        // Verify destination link is wired
        $response->assertSee('/menu?search=biryani');
    }

    public function test_inactive_or_expired_hero_banner_does_not_appear_on_homepage(): void
    {
        HomepageBanner::truncate();

        // 1. Inactive banner
        HomepageBanner::create([
            'title' => 'Hidden Inactive Banner',
            'is_active' => false,
            'display_order' => 1,
        ]);

        // 2. Expired banner (ends_at in past)
        HomepageBanner::create([
            'title' => 'Expired Past Banner',
            'is_active' => true,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDay(),
            'display_order' => 2,
        ]);

        // 3. Future scheduled banner (starts_at in future)
        HomepageBanner::create([
            'title' => 'Future Scheduled Banner',
            'is_active' => true,
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(10),
            'display_order' => 3,
        ]);

        // 4. Valid Active banner
        HomepageBanner::create([
            'title' => 'Visible Active Banner',
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(5),
            'display_order' => 4,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Hidden Inactive Banner');
        $response->assertDontSee('Expired Past Banner');
        $response->assertDontSee('Future Scheduled Banner');
        $response->assertSee('Visible Active Banner');
    }

    public function test_display_order_controls_which_banner_appears_first(): void
    {
        HomepageBanner::truncate();

        HomepageBanner::create([
            'title' => 'Second Display Banner',
            'display_order' => 20,
            'is_active' => true,
        ]);

        HomepageBanner::create([
            'title' => 'First Display Banner',
            'display_order' => 5,
            'is_active' => true,
        ]);

        $activeBanners = HomepageBanner::active()->get();

        $this->assertCount(2, $activeBanners);
        $this->assertSame('First Display Banner', $activeBanners->first()->title);
        $this->assertSame('Second Display Banner', $activeBanners->last()->title);
    }

    public function test_admin_banner_management_includes_alt_text_and_ratio_guidance(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('banner_2000x700.jpg', 2000, 700);

        $response = $this->actingAs($this->adminUser)->post(route('admin.hero-banners.store'), [
            'name' => 'Biryani Festive Special',
            'title' => 'Flat 20% Off Biryani',
            'alt_text' => 'Crispy and delicious biryani banner promo',
            'button_url' => '/menu',
            'button_text' => 'Order Now',
            'image' => $image,
            'display_order' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.hero-banners.index'));

        $this->assertDatabaseHas('homepage_banners', [
            'name' => 'Biryani Festive Special',
            'alt_text' => 'Crispy and delicious biryani banner promo',
            'button_url' => '/menu',
        ]);

        // Verify index view provides 2000 x 700 px guidance and 20:7 ratio container
        $indexResponse = $this->actingAs($this->adminUser)->get(route('admin.hero-banners.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('2000');
        $indexResponse->assertSee('700');
        $indexResponse->assertSee('20:7');
        $indexResponse->assertSee('Crispy and delicious biryani banner promo');
    }

    public function test_three_banner_areas_have_independent_image_separation_and_uploads(): void
    {
        Storage::fake('public');

        // Initial setup of 3 separate banners
        $heroImage = UploadedFile::fake()->image('hero_init.jpg', 2000, 700);
        $heroPath = 'storage/' . $heroImage->store('banners', 'public');
        $hero = HomepageBanner::create([
            'slot' => HomepageBanner::SLOT_HERO,
            'name' => 'Hero Banner Init',
            'title' => 'Hero Banner Init',
            'image_path' => $heroPath,
            'is_active' => true,
        ]);

        $offer1Image = UploadedFile::fake()->image('offer1_init.png', 400, 400);
        $offer1Path = 'storage/' . $offer1Image->store('banners', 'public');
        $offer1 = HomepageBanner::create([
            'slot' => HomepageBanner::SLOT_OFFER_1,
            'name' => 'Offer 1 Init',
            'title' => 'Offer 1 Init',
            'image_path' => $offer1Path,
            'is_active' => true,
        ]);

        $offer2Image = UploadedFile::fake()->image('offer2_init.png', 400, 400);
        $offer2Path = 'storage/' . $offer2Image->store('banners', 'public');
        $offer2 = HomepageBanner::create([
            'slot' => HomepageBanner::SLOT_OFFER_2,
            'name' => 'Offer 2 Init',
            'title' => 'Offer 2 Init',
            'image_path' => $offer2Path,
            'is_active' => true,
        ]);

        // Scenario 1: Upload a new Hero image -> Verify ONLY Hero changes
        $newHeroImage = UploadedFile::fake()->image('hero_replacement.jpg', 2000, 700);
        $this->actingAs($this->adminUser)->put(route('admin.hero-banners.update', $hero->id), [
            'name' => 'Hero Banner Updated',
            'title' => 'Hero Banner Updated',
            'image' => $newHeroImage,
        ]);

        $hero->refresh();
        $offer1->refresh();
        $offer2->refresh();

        $this->assertNotEquals($heroPath, $hero->image_path, "Hero image should be updated.");
        $this->assertEquals($offer1Path, $offer1->image_path, "Offer 1 image must NOT change when hero changes.");
        $this->assertEquals($offer2Path, $offer2->image_path, "Offer 2 image must NOT change when hero changes.");

        // Scenario 2: Upload new image for Offer Banner 1 -> Verify ONLY Offer 1 changes
        $newOffer1Image = UploadedFile::fake()->image('offer1_replacement.png', 400, 400);
        $this->actingAs($this->adminUser)->post(route('admin.banners.offer1.update'), [
            'title' => 'Special Kolkata Kathi Rolls',
            'offer_banner_1_image' => $newOffer1Image,
        ]);

        $hero->refresh();
        $offer1->refresh();
        $offer2->refresh();

        $this->assertNotEquals($offer1Path, $offer1->image_path, "Offer 1 image should be updated.");
        $this->assertEquals($offer2Path, $offer2->image_path, "Offer 2 image must NOT change when Offer 1 changes.");
        $this->assertEquals($hero->image_path, $hero->fresh()->image_path, "Hero image must NOT change when Offer 1 changes.");

        // Scenario 3: Upload new image for Offer Banner 2 -> Verify ONLY Offer 2 changes
        $newOffer2Image = UploadedFile::fake()->image('offer2_replacement.png', 400, 400);
        $this->actingAs($this->adminUser)->post(route('admin.banners.offer2.update'), [
            'title' => 'Crispy Chicken Pakora Deal',
            'offer_banner_2_image' => $newOffer2Image,
        ]);

        $hero->refresh();
        $offer1->refresh();
        $offer2->refresh();

        $this->assertNotEquals($offer2Path, $offer2->image_path, "Offer 2 image should be updated.");
        $this->assertEquals($offer1->image_path, $offer1->fresh()->image_path, "Offer 1 image must NOT change when Offer 2 changes.");
        $this->assertEquals($hero->image_path, $hero->fresh()->image_path, "Hero image must NOT change when Offer 2 changes.");
    }

    public function test_editing_text_of_offer_banner_preserves_image_and_does_not_affect_others(): void
    {
        Storage::fake('public');

        $offer1Image = UploadedFile::fake()->image('offer1_test.png', 300, 300);
        $offer1Path = 'storage/' . $offer1Image->store('banners', 'public');

        $offer1 = HomepageBanner::updateOrCreate(
            ['slot' => HomepageBanner::SLOT_OFFER_1],
            [
                'name' => 'Original Offer 1',
                'title' => 'Original Title 1',
                'badge_text' => 'SPECIAL',
                'image_path' => $offer1Path,
                'is_active' => true,
            ]
        );

        $offer2 = HomepageBanner::updateOrCreate(
            ['slot' => HomepageBanner::SLOT_OFFER_2],
            [
                'name' => 'Original Offer 2',
                'title' => 'Original Title 2',
                'image_path' => 'images/banners/banner-rolls.svg',
                'is_active' => true,
            ]
        );

        // Edit text of Offer Banner 1 without uploading a new image
        $response = $this->actingAs($this->adminUser)->post(route('admin.banners.offer1.update'), [
            'title' => 'Brand New Feast Headline',
            'badge_text' => 'WEEKEND CHEF PICK',
            'subtitle' => 'Rich aromatic spices and saffron',
            'button_text' => 'Grab Feast',
            'button_url' => '/menu?feast=yes',
            'bg_gradient' => 'rose',
        ]);

        $response->assertRedirect(route('admin.hero-banners.index'));

        $offer1->refresh();
        $offer2->refresh();

        $this->assertSame('Brand New Feast Headline', $offer1->title);
        $this->assertSame('WEEKEND CHEF PICK', $offer1->badge_text);
        $this->assertSame($offer1Path, $offer1->image_path, "Existing image must be preserved when no new image is uploaded.");

        $this->assertSame('Original Title 2', $offer2->title);
        $this->assertSame('images/banners/banner-rolls.svg', $offer2->image_path);
    }

    public function test_deleting_offer_banner_2_does_not_affect_hero_or_offer_1(): void
    {
        Storage::fake('public');

        $heroImg = UploadedFile::fake()->image('hero_test.jpg', 2000, 700);
        $heroPath = 'storage/' . $heroImg->store('banners', 'public');
        $hero = HomepageBanner::create([
            'slot' => HomepageBanner::SLOT_HERO,
            'title' => 'Persistent Hero',
            'image_path' => $heroPath,
            'is_active' => true,
        ]);

        $offer1Img = UploadedFile::fake()->image('offer1_test.png', 400, 400);
        $offer1Path = 'storage/' . $offer1Img->store('banners', 'public');
        $offer1 = HomepageBanner::updateOrCreate(
            ['slot' => HomepageBanner::SLOT_OFFER_1],
            ['title' => 'Persistent Offer 1', 'image_path' => $offer1Path, 'is_active' => true]
        );

        $offer2Img = UploadedFile::fake()->image('offer2_to_delete.png', 400, 400);
        $offer2RelPath = $offer2Img->store('banners', 'public');
        $offer2 = HomepageBanner::updateOrCreate(
            ['slot' => HomepageBanner::SLOT_OFFER_2],
            ['title' => 'Offer 2 To Delete', 'image_path' => 'storage/' . $offer2RelPath, 'is_active' => true]
        );

        Storage::disk('public')->assertExists($offer2RelPath);

        // Delete Offer Banner 2
        $deleteResponse = $this->actingAs($this->adminUser)->delete(route('admin.banners.offer2.destroy'));
        $deleteResponse->assertRedirect(route('admin.hero-banners.index'));

        // Verify Offer 2 record deleted & file purged
        $this->assertDatabaseMissing('homepage_banners', ['slot' => HomepageBanner::SLOT_OFFER_2]);
        Storage::disk('public')->assertMissing($offer2RelPath);

        // Verify Hero and Offer 1 remain completely intact!
        $this->assertDatabaseHas('homepage_banners', ['id' => $hero->id]);
        $this->assertDatabaseHas('homepage_banners', ['id' => $offer1->id]);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $heroPath));
        Storage::disk('public')->assertExists(str_replace('storage/', '', $offer1Path));
    }

    public function test_homepage_renders_all_three_banner_areas_independently(): void
    {
        HomepageBanner::truncate();

        // 1. Top Hero Banner
        HomepageBanner::create([
            'slot' => HomepageBanner::SLOT_HERO,
            'title' => 'Grand Top Carousel Hero',
            'alt_text' => 'My 2nd Wife Grand Royal Hero Showcase',
            'image_path' => 'images/banners/hero-banner-biryani-2000x700.svg',
            'button_url' => '/menu?hero=1',
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 2. Left Promo Banner (Offer 1)
        HomepageBanner::create([
            'slot' => HomepageBanner::SLOT_OFFER_1,
            'badge_text' => 'CHEF DEAL 1',
            'title' => 'Exclusive Left Offer Banner 1',
            'subtitle' => 'Special discount on handi mutton',
            'image_path' => 'images/banners/banner-biryani.svg',
            'button_text' => 'Get Offer 1',
            'button_url' => '/offers/deal-1',
            'is_active' => true,
        ]);

        // 3. Right Promo Banner (Offer 2)
        HomepageBanner::create([
            'slot' => HomepageBanner::SLOT_OFFER_2,
            'badge_text' => 'ROLLS CRAVING',
            'title' => 'Exclusive Right Offer Banner 2',
            'subtitle' => 'Kolkata paratha egg chicken roll',
            'image_path' => 'images/banners/banner-rolls.svg',
            'button_text' => 'Get Offer 2',
            'button_url' => '/offers/deal-2',
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // Verify Hero Banner in 20:7 container
        $response->assertSee('images/banners/hero-banner-biryani-2000x700.svg');
        $response->assertSee('My 2nd Wife Grand Royal Hero Showcase');
        $response->assertSee('/menu?hero=1');

        // Verify Offer Banner 1
        $response->assertSee('CHEF DEAL 1');
        $response->assertSee('Exclusive Left Offer Banner 1');
        $response->assertSee('Get Offer 1');
        $response->assertSee('/offers/deal-1');

        // Verify Offer Banner 2
        $response->assertSee('ROLLS CRAVING');
        $response->assertSee('Exclusive Right Offer Banner 2');
        $response->assertSee('Get Offer 2');
        $response->assertSee('/offers/deal-2');
    }

    public function test_admin_homepage_banners_dashboard_shows_all_three_independent_sections(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.hero-banners.index'));

        $response->assertStatus(200);
        $response->assertSee('1. Main Hero Banner');
        $response->assertSee('2. Offer Banner 1 (Left Promo)');
        $response->assertSee('3. Offer Banner 2 (Right Promo)');
        $response->assertSee('offer_banner_1_image');
        $response->assertSee('offer_banner_2_image');
    }
}


