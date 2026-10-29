<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\AddonGroup;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryAndProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reusable Addon Groups for Indian Meals
        $thaliAddonsGroup = AddonGroup::firstOrCreate(
            ['name' => 'Thali Accompaniments & Extras'],
            [
                'min_selection' => 0,
                'max_selection' => 3,
                'is_required' => false,
                'display_order' => 1,
                'is_active' => true,
            ]
        );

        $thaliAddons = [
            ['name' => 'Extra Roti (Ghee Basted)', 'price' => 10.00],
            ['name' => 'Sweet Gulab Jamun (1 pc)', 'price' => 20.00],
            ['name' => 'Fresh Bengali Dahi (Sweet Curd)', 'price' => 25.00],
            ['name' => 'Mango Pickle & Fried Green Chilies', 'price' => 10.00],
        ];

        foreach ($thaliAddons as $i => $item) {
            Addon::firstOrCreate(
                ['addon_group_id' => $thaliAddonsGroup->id, 'name' => $item['name']],
                ['price' => $item['price'], 'display_order' => $i + 1, 'is_active' => true]
            );
        }

        $spiceLevelGroup = AddonGroup::firstOrCreate(
            ['name' => 'Spice Level Preference'],
            [
                'min_selection' => 0,
                'max_selection' => 1,
                'is_required' => false,
                'display_order' => 2,
                'is_active' => true,
            ]
        );

        $spiceLevels = [
            ['name' => 'Mild & Gentle Spiced', 'price' => 0.00],
            ['name' => 'Medium Homestyle Spice', 'price' => 0.00],
            ['name' => 'Extra Spicy & Zesty', 'price' => 0.00],
        ];

        foreach ($spiceLevels as $i => $item) {
            Addon::firstOrCreate(
                ['addon_group_id' => $spiceLevelGroup->id, 'name' => $item['name']],
                ['price' => $item['price'], 'display_order' => $i + 1, 'is_active' => true]
            );
        }

        // 2. The 7 Official Categories
        $categoriesData = [
            [
                'name' => 'Veg Thali',
                'slug' => 'veg-thali',
                'description' => 'A visually appealing Indian vegetarian thali containing rice, dal, vegetables, roti, and salad.',
                'image' => 'images/categories/veg-thali.svg',
                'display_order' => 1,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Non-Veg Thali',
                'slug' => 'non-veg-thali',
                'description' => 'A traditional Indian non-vegetarian thali with curry, rice, roti, and accompaniments.',
                'image' => 'images/categories/non-veg-thali.svg',
                'display_order' => 2,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Main Course (Veg)',
                'slug' => 'main-course-veg',
                'description' => 'Indian vegetarian curries and paneer dishes.',
                'image' => 'images/categories/main-course-veg.svg',
                'display_order' => 3,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Main Course (Non-Veg)',
                'slug' => 'main-course-non-veg',
                'description' => 'Indian chicken, egg, and fish curry dishes.',
                'image' => 'images/categories/main-course-non-veg.svg',
                'display_order' => 4,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Rice & Roti',
                'slug' => 'rice-roti',
                'description' => 'Rice, roti, and Indian bread with quantity ordering support.',
                'image' => 'images/categories/rice-roti.svg',
                'display_order' => 5,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Add-Ons',
                'slug' => 'add-ons',
                'description' => 'Indian meal accompaniments such as salad, papad, and dal.',
                'image' => 'images/categories/add-ons.svg',
                'display_order' => 6,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages',
                'description' => 'Mineral water bottles and chilled buttermilk.',
                'image' => 'images/categories/beverages.svg',
                'display_order' => 7,
                'is_active' => true,
                'is_featured' => false,
            ],
        ];

        // Mark any older un-matched categories as inactive to prevent pollution
        $officialSlugs = array_column($categoriesData, 'slug');
        Category::whereNotIn('slug', $officialSlugs)->update(['is_active' => false]);
        Product::whereNotIn('category_id', function ($q) use ($officialSlugs) {
            $q->select('id')->from('categories')->whereIn('slug', $officialSlugs);
        })->update(['is_available' => false]);

        $savedCategories = [];
        foreach ($categoriesData as $catInfo) {
            $category = Category::where('slug', $catInfo['slug'])->first();
            if (!$category) {
                $category = Category::create($catInfo);
            } else {
                // Keep existing name/image in sync if needed
                $category->update([
                    'name' => $catInfo['name'],
                    'image' => $catInfo['image'],
                    'description' => $catInfo['description'],
                    'display_order' => $catInfo['display_order'],
                    'is_active' => true,
                    'is_featured' => $catInfo['is_featured'],
                ]);
            }
            $savedCategories[$catInfo['slug']] = $category;
        }

        // 3. The 21 Dishes across the 7 Categories
        $productsData = [
            // CATEGORY 1: Veg Thali
            [
                'category_slug' => 'veg-thali',
                'name' => 'Regular Veg Thali',
                'slug' => 'regular-veg-thali',
                'description' => 'Dal, Sabzi, Rice, 4 Roti, Salad',
                'price' => 80.00,
                'is_veg' => true,
                'is_featured' => true,
                'is_popular' => true,
                'is_best_seller' => true,
                'prep_time' => 15,
                'image' => 'images/food/regular-veg-thali.svg',
                'display_order' => 1,
            ],
            [
                'category_slug' => 'veg-thali',
                'name' => 'Special Veg Thali',
                'slug' => 'special-veg-thali',
                'description' => '2 Sabzi, Dal, Rice, 4 Roti, Salad, Papad',
                'price' => 120.00,
                'is_veg' => true,
                'is_featured' => true,
                'is_popular' => true,
                'is_best_seller' => false,
                'prep_time' => 20,
                'image' => 'images/food/special-veg-thali.svg',
                'display_order' => 2,
            ],

            // CATEGORY 2: Non-Veg Thali
            [
                'category_slug' => 'non-veg-thali',
                'name' => 'Egg Thali',
                'slug' => 'egg-thali',
                'description' => 'Egg Curry, Dal, Rice, 4 Roti',
                'price' => 100.00,
                'is_veg' => false,
                'is_featured' => false,
                'is_popular' => true,
                'is_best_seller' => false,
                'prep_time' => 18,
                'image' => 'images/food/egg-thali.svg',
                'display_order' => 1,
            ],
            [
                'category_slug' => 'non-veg-thali',
                'name' => 'Chicken Thali',
                'slug' => 'chicken-thali',
                'description' => 'Chicken Curry, Rice, 4 Roti, Salad',
                'price' => 160.00,
                'is_veg' => false,
                'is_featured' => true,
                'is_popular' => true,
                'is_best_seller' => true,
                'prep_time' => 22,
                'image' => 'images/food/chicken-thali.svg',
                'display_order' => 2,
            ],
            [
                'category_slug' => 'non-veg-thali',
                'name' => 'Fish Thali',
                'slug' => 'fish-thali',
                'description' => 'Fish Curry, Rice, 4 Roti, Salad',
                'price' => 180.00,
                'is_veg' => false,
                'is_featured' => true,
                'is_popular' => true,
                'is_best_seller' => false,
                'prep_time' => 25,
                'image' => 'images/food/fish-thali.svg',
                'display_order' => 3,
            ],

            // CATEGORY 3: Main Course (Veg)
            [
                'category_slug' => 'main-course-veg',
                'name' => 'Paneer Butter Masala',
                'slug' => 'paneer-butter-masala',
                'description' => 'Rich velvety cashew and tomato gravy with cottage cheese',
                'price' => 140.00,
                'is_veg' => true,
                'is_featured' => true,
                'is_popular' => true,
                'is_best_seller' => true,
                'prep_time' => 20,
                'image' => 'images/food/paneer-butter-masala.svg',
                'display_order' => 1,
            ],
            [
                'category_slug' => 'main-course-veg',
                'name' => 'Mixed Veg',
                'slug' => 'mixed-veg',
                'description' => 'Fresh seasonal farm vegetables gently spiced',
                'price' => 100.00,
                'is_veg' => true,
                'is_featured' => false,
                'is_popular' => false,
                'is_best_seller' => false,
                'prep_time' => 15,
                'image' => 'images/food/mixed-veg.svg',
                'display_order' => 2,
            ],
            [
                'category_slug' => 'main-course-veg',
                'name' => 'Aloo Posto',
                'slug' => 'aloo-posto',
                'description' => 'Authentic Bengali style potato curry in poppy seed paste',
                'price' => 90.00,
                'is_veg' => true,
                'is_featured' => true,
                'is_popular' => true,
                'is_best_seller' => false,
                'prep_time' => 15,
                'image' => 'images/food/aloo-posto.svg',
                'display_order' => 3,
            ],
            [
                'category_slug' => 'main-course-veg',
                'name' => 'Dal Tadka',
                'slug' => 'dal-tadka',
                'description' => 'Yellow lentils tempered with ghee, cumin, garlic and chili',
                'price' => 80.00,
                'is_veg' => true,
                'is_featured' => false,
                'is_popular' => true,
                'is_best_seller' => false,
                'prep_time' => 12,
                'image' => 'images/food/dal-tadka.svg',
                'display_order' => 4,
            ],

            // CATEGORY 4: Main Course (Non-Veg)
            [
                'category_slug' => 'main-course-non-veg',
                'name' => 'Chicken Curry',
                'slug' => 'chicken-curry',
                'description' => 'Tender chicken simmered in aromatic homestyle gravy',
                'price' => 140.00,
                'is_veg' => false,
                'is_featured' => true,
                'is_popular' => true,
                'is_best_seller' => false,
                'prep_time' => 20,
                'image' => 'images/food/chicken-curry.svg',
                'display_order' => 1,
            ],
            [
                'category_slug' => 'main-course-non-veg',
                'name' => 'Chicken Kosha',
                'slug' => 'chicken-kosha',
                'description' => 'Slow-cooked dry spicy Bengali chicken roast in rich masala',
                'price' => 180.00,
                'is_veg' => false,
                'is_featured' => true,
                'is_popular' => true,
                'is_best_seller' => true,
                'prep_time' => 25,
                'image' => 'images/food/chicken-kosha.svg',
                'display_order' => 2,
            ],
            [
                'category_slug' => 'main-course-non-veg',
                'name' => 'Egg Curry (2 pcs)',
                'slug' => 'egg-curry-2-pcs',
                'description' => 'Fried boiled eggs in a spiced onion-tomato gravy',
                'price' => 90.00,
                'is_veg' => false,
                'is_featured' => false,
                'is_popular' => false,
                'is_best_seller' => false,
                'prep_time' => 15,
                'image' => 'images/food/egg-curry.svg',
                'display_order' => 3,
            ],
            [
                'category_slug' => 'main-course-non-veg',
                'name' => 'Fish Curry',
                'slug' => 'fish-curry',
                'description' => 'Fresh catch cooked in traditional mustard and mustard-oil gravy',
                'price' => 160.00,
                'is_veg' => false,
                'is_featured' => true,
                'is_popular' => true,
                'is_best_seller' => false,
                'prep_time' => 20,
                'image' => 'images/food/fish-curry.svg',
                'display_order' => 4,
            ],

            // CATEGORY 5: Rice & Roti
            [
                'category_slug' => 'rice-roti',
                'name' => 'Plain Rice',
                'slug' => 'plain-rice',
                'description' => 'Steamed long-grain fragrant white rice',
                'price' => 40.00,
                'is_veg' => true,
                'is_featured' => false,
                'is_popular' => false,
                'is_best_seller' => false,
                'prep_time' => 10,
                'image' => 'images/food/plain-rice.svg',
                'display_order' => 1,
            ],
            [
                'category_slug' => 'rice-roti',
                'name' => 'Jeera Rice',
                'slug' => 'jeera-rice',
                'description' => 'Basmati rice tossed with toasted cumin seeds and pure ghee',
                'price' => 60.00,
                'is_veg' => true,
                'is_featured' => false,
                'is_popular' => true,
                'is_best_seller' => false,
                'prep_time' => 12,
                'image' => 'images/food/jeera-rice.svg',
                'display_order' => 2,
            ],
            [
                'category_slug' => 'rice-roti',
                'name' => 'Roti (1 pc)',
                'slug' => 'roti-1-pc',
                'description' => 'Hot whole-wheat phulka straight from the tawa (support quantity-based ordering)',
                'price' => 8.00,
                'is_veg' => true,
                'is_featured' => false,
                'is_popular' => true,
                'is_best_seller' => true,
                'prep_time' => 5,
                'image' => 'images/food/roti.svg',
                'display_order' => 3,
            ],
            [
                'category_slug' => 'rice-roti',
                'name' => 'Paratha (1 pc)',
                'slug' => 'paratha-1-pc',
                'description' => 'Crispy layered triangular golden paratha (support quantity-based ordering)',
                'price' => 15.00,
                'is_veg' => true,
                'is_featured' => false,
                'is_popular' => true,
                'is_best_seller' => false,
                'prep_time' => 8,
                'image' => 'images/food/paratha.svg',
                'display_order' => 4,
            ],

            // CATEGORY 6: Add-Ons
            [
                'category_slug' => 'add-ons',
                'name' => 'Salad',
                'slug' => 'salad',
                'description' => 'Crisp sliced cucumber, onion, tomato and green chili with lemon',
                'price' => 20.00,
                'is_veg' => true,
                'is_featured' => false,
                'is_popular' => false,
                'is_best_seller' => false,
                'prep_time' => 5,
                'image' => 'images/food/salad.svg',
                'display_order' => 1,
            ],
            [
                'category_slug' => 'add-ons',
                'name' => 'Papad',
                'slug' => 'papad',
                'description' => 'Crunchy roasted or fried urad dal papad',
                'price' => 10.00,
                'is_veg' => true,
                'is_featured' => false,
                'is_popular' => false,
                'is_best_seller' => false,
                'prep_time' => 3,
                'image' => 'images/food/papad.svg',
                'display_order' => 2,
            ],
            [
                'category_slug' => 'add-ons',
                'name' => 'Extra Dal',
                'slug' => 'extra-dal',
                'description' => 'Additional bowl of homestyle tempered dal',
                'price' => 30.00,
                'is_veg' => true,
                'is_featured' => false,
                'is_popular' => false,
                'is_best_seller' => false,
                'prep_time' => 8,
                'image' => 'images/food/extra-dal.svg',
                'display_order' => 3,
            ],

            // CATEGORY 7: Beverages
            [
                'category_slug' => 'beverages',
                'name' => 'Mineral Water',
                'slug' => 'mineral-water',
                'description' => 'Packaged sealed packaged drinking mineral water (1 Litre)',
                'price' => 20.00,
                'is_veg' => true,
                'is_featured' => false,
                'is_popular' => false,
                'is_best_seller' => false,
                'prep_time' => 2,
                'image' => 'images/food/mineral-water.svg',
                'display_order' => 1,
            ],
            [
                'category_slug' => 'beverages',
                'name' => 'Buttermilk',
                'slug' => 'buttermilk',
                'description' => 'Refreshing spiced masala chaas churned with roasted cumin and mint',
                'price' => 25.00,
                'is_veg' => true,
                'is_featured' => false,
                'is_popular' => true,
                'is_best_seller' => false,
                'prep_time' => 5,
                'image' => 'images/food/buttermilk.svg',
                'display_order' => 2,
            ],
        ];

        foreach ($productsData as $prod) {
            $cat = $savedCategories[$prod['category_slug']];

            $product = Product::where('slug', $prod['slug'])->first();

            if (!$product) {
                $sku = 'M2W-' . strtoupper(Str::random(6));
                $product = Product::create([
                    'category_id' => $cat->id,
                    'name' => $prod['name'],
                    'slug' => $prod['slug'],
                    'description' => $prod['description'],
                    'sku' => $sku,
                    'regular_price' => $prod['price'],
                    'discount_price' => null,
                    'is_veg' => $prod['is_veg'],
                    'is_featured' => $prod['is_featured'],
                    'is_popular' => $prod['is_popular'],
                    'is_best_seller' => $prod['is_best_seller'],
                    'prep_time_minutes' => $prod['prep_time'],
                    'is_available' => true,
                    'display_order' => $prod['display_order'],
                    'main_image' => $prod['image'],
                ]);
            } else {
                // Product exists: update category, name, image, description, availability, but PRESERVE custom admin prices!
                $product->update([
                    'category_id' => $cat->id,
                    'name' => $prod['name'],
                    'description' => $prod['description'],
                    'main_image' => $prod['image'],
                    'is_veg' => $prod['is_veg'],
                    'is_available' => true,
                    'display_order' => $prod['display_order'],
                ]);
            }

            // Attach addon groups where appropriate
            if (str_contains($prod['category_slug'], 'thali')) {
                $product->addonGroups()->syncWithoutDetaching([$thaliAddonsGroup->id, $spiceLevelGroup->id]);
            } elseif (str_contains($prod['category_slug'], 'main-course')) {
                $product->addonGroups()->syncWithoutDetaching([$spiceLevelGroup->id]);
            }
        }
    }
}
