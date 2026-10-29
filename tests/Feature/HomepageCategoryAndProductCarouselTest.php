<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HomepageBanner;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageCategoryAndProductCarouselTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create 7 standard categories
        $categoryNames = [
            'Veg Thali',
            'Non-Veg Thali',
            'Main Course (Veg)',
            'Main Course (Non-Veg)',
            'Rice & Roti',
            'Add-Ons',
            'Beverages',
        ];

        foreach ($categoryNames as $index => $name) {
            Category::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'display_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        // Create 12 available products across categories, 9 of which are featured
        $categories = Category::all();
        for ($i = 1; $i <= 12; $i++) {
            Product::create([
                'category_id' => $categories->get($i % $categories->count())->id,
                'name' => "Special Homestyle Dish {$i}",
                'slug' => "special-homestyle-dish-{$i}",
                'description' => "Freshly prepared homestyle delicacy number {$i}",
                'regular_price' => 100.00 + ($i * 10),
                'discount_price' => $i % 2 === 0 ? 90.00 + ($i * 10) : null,
                'is_veg' => $i % 2 !== 0,
                'is_available' => true,
                'is_featured' => $i <= 9, // 9 products marked as featured
                'display_order' => $i,
            ]);
        }

        // Create a hero banner
        HomepageBanner::create([
            'title' => 'Festive Treats',
            'type' => 'hero',
            'image' => 'images/banners/hero.jpg',
            'is_active' => true,
            'display_order' => 1,
        ]);
    }

    public function test_homepage_renders_all_seven_categories_with_enlarged_icons_and_carousel_markup(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // All seven category names appear in the HTML
        $response->assertSee('Veg Thali');
        $response->assertSee('Non-Veg Thali');
        $response->assertSee('Main Course (Veg)');
        $response->assertSee('Main Course (Non-Veg)');
        $response->assertSee('Rice & Roti');
        $response->assertSee('Add-Ons');
        $response->assertSee('Beverages');

        // Check for enlarged icon wrapper and carousel CSS class
        $response->assertSee('category-carousel-item');
        $response->assertSee('x-ref="catTrack"', false);
        $response->assertSee('Our Menu');
    }

    public function test_new_explore_our_menu_product_carousel_renders_with_actual_products(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // Section header and subtitle
        $response->assertSee('Explore Our Menu');
        $response->assertSee("Chef's handcrafted homestyle dishes cooked fresh for you", false);

        // Product carousel track and items
        $response->assertSee('x-ref="exploreTrack"', false);
        $response->assertSee('product-carousel-item');
        $response->assertSee('Special Homestyle Dish 1');
        $response->assertSee('Special Homestyle Dish 5');
    }

    public function test_featured_items_section_displays_eight_distinct_products_when_available(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Featured Items');

        // Check view data passed to home view
        $featuredProducts = $response->viewData('featuredProducts');
        $this->assertCount(8, $featuredProducts);

        // All 8 products are distinct
        $uniqueIds = $featuredProducts->pluck('id')->unique();
        $this->assertCount(8, $uniqueIds);

        // Grid layout has 4 columns on desktop (2 rows of 4) and 2 columns on mobile (4 rows of 2)
        $response->assertSee('grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4', false);
    }

    public function test_featured_items_section_handles_fewer_than_eight_products_without_duplication(): void
    {
        // Reduce featured products to only 4
        Product::where('id', '>', 4)->update(['is_featured' => false]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $featuredProducts = $response->viewData('featuredProducts');
        $this->assertCount(4, $featuredProducts);

        $uniqueIds = $featuredProducts->pluck('id')->unique();
        $this->assertCount(4, $uniqueIds);
    }

    public function test_homepage_section_order_is_preserved_correctly(): void
    {
        $response = $this->get(route('home'));
        $content = $response->getContent();

        $heroPos = strpos($content, 'Promotional Banners');
        $ourMenuPos = strpos($content, 'Our Menu');
        $explorePos = strpos($content, 'Explore Our Menu');
        $featuredPos = strpos($content, 'Featured Items');
        $popularPos = strpos($content, 'Most Popular Items');

        $this->assertNotFalse($ourMenuPos);
        $this->assertNotFalse($explorePos);
        $this->assertNotFalse($featuredPos);
        $this->assertNotFalse($popularPos);

        // 1. Hero banner -> 2. Our Menu -> 3. Explore Our Menu -> 4. Featured Items -> 5. Most Popular Items
        $this->assertTrue($ourMenuPos < $explorePos, 'Our Menu must appear before Explore Our Menu');
        $this->assertTrue($explorePos < $featuredPos, 'Explore Our Menu must appear before Featured Items');
        $this->assertTrue($featuredPos < $popularPos, 'Featured Items must appear before Most Popular Items');
    }

    public function test_see_all_and_category_links_are_functional(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee(route('menu.index'));
        $response->assertSee(route('menu.index', ['category' => 'veg-thali']));
        $response->assertSee(route('menu.index', ['category' => 'beverages']));
    }
}
