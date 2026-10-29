<?php

namespace Database\Seeders;

use App\Models\HomepageBanner;
use Illuminate\Database\Seeder;

class HomepageBannerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Main Hero Banner
        HomepageBanner::updateOrCreate(
            ['slot' => HomepageBanner::SLOT_HERO, 'display_order' => 1],
            [
                'name' => 'Royal Dum Biryani Festival',
                'title' => 'Flat 20% Off on Royal Dum Biryani & Starters',
                'alt_text' => 'My 2nd Wife Royal Dum Biryani and Starters - Flat 20% Off with code BIRYANI20',
                'button_text' => 'Order Biryani Now',
                'button_url' => '/menu?search=biryani',
                'image_path' => 'images/banners/hero-banner-biryani-2000x700.svg',
                'bg_gradient' => 'mint',
                'text_color' => '#FFFFFF',
                'display_order' => 1,
                'is_active' => true,
            ]
        );

        // 2. Offer Banner 1 (Left Promo Banner below Our Menu)
        HomepageBanner::updateOrCreate(
            ['slot' => HomepageBanner::SLOT_OFFER_1],
            [
                'name' => 'Offer Banner 1 (Left Promo)',
                'badge_text' => 'Uplifting anytime',
                'title' => 'Flat 20% off on Dum Biryani & Starters',
                'subtitle' => 'Slow dum-cooked with royal saffron & spices',
                'button_text' => 'Order Now',
                'button_url' => '/menu',
                'image_path' => 'images/banners/banner-biryani.svg',
                'alt_text' => 'Flat 20% off on Dum Biryani & Starters',
                'bg_gradient' => 'mint',
                'text_color' => '#1F1F39',
                'display_order' => 1,
                'is_active' => true,
            ]
        );

        // 3. Offer Banner 2 (Right Promo Banner below Our Menu)
        HomepageBanner::updateOrCreate(
            ['slot' => HomepageBanner::SLOT_OFFER_2],
            [
                'name' => 'Offer Banner 2 (Right Promo)',
                'badge_text' => 'Savory and Satisfying',
                'title' => 'Flat 15% off on Kolkata Rolls & Kebabs',
                'subtitle' => 'Crispy parathas with spiced chicken & chutney',
                'button_text' => 'Order Now',
                'button_url' => '/menu',
                'image_path' => 'images/banners/banner-rolls.svg',
                'alt_text' => 'Flat 15% off on Kolkata Rolls & Kebabs',
                'bg_gradient' => 'peach',
                'text_color' => '#1F1F39',
                'display_order' => 2,
                'is_active' => true,
            ]
        );
    }
}
