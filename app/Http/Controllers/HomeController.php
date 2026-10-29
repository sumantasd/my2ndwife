<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomepageBanner;
use App\Models\Product;
use App\Models\RestaurantSetting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $heroBanners = HomepageBanner::hero()->active()->get();
        $offerBanner1 = HomepageBanner::offer1()->first();
        $offerBanner2 = HomepageBanner::offer2()->first();
        $banners = $heroBanners; // backward compatibility for any existing view references

        $categories = Category::active()
            ->withCount(['products' => function ($q) {
                $q->where('is_available', true);
            }])
            ->get();

        $exploreMenuProducts = Product::available()
            ->with(['category', 'variations'])
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        $featuredProducts = Product::available()
            ->featured()
            ->with(['category', 'variations'])
            ->take(8)
            ->get();

        $popularProducts = Product::available()
            ->popular()
            ->with(['category', 'variations'])
            ->take(8)
            ->get();

        $bestSellers = Product::available()
            ->bestSeller()
            ->with(['category', 'variations'])
            ->take(6)
            ->get();

        $specialOffers = Product::available()
            ->whereNotNull('discount_price')
            ->whereColumn('discount_price', '<', 'regular_price')
            ->with(['category', 'variations'])
            ->take(6)
            ->get();

        $testimonials = [
            [
                'name' => 'Dr. Ananya Sen',
                'role' => 'Food Connoisseur & Food Blogger',
                'comment' => 'The Special Veg Thali and Aloo Posto remind me of authentic Sunday homestyle Bengali lunches. Arrived piping hot in immaculate packaging!',
                'rating' => 5,
                'avatar' => '👩‍⚕️',
            ],
            [
                'name' => 'Vikramaditya Roy',
                'role' => 'Tech Lead, Salt Lake',
                'comment' => 'Chicken Kosha with layered parathas is out of this world! Rich slow-cooked masala gravy and tender chicken. Quick 30 min doorstep delivery.',
                'rating' => 5,
                'avatar' => '👨‍💻',
            ],
            [
                'name' => 'Pooja Agarwal',
                'role' => 'Architect & Designer',
                'comment' => 'The Regular Veg Thali at ₹80 is the most honest, delicious, and balanced meal box in Kolkata. My 2nd Wife is our everyday go-to kitchen now!',
                'rating' => 5,
                'avatar' => '👩‍🎨',
            ],
        ];

        $offers = \App\Models\Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->take(2)
            ->get();

        return view('customer.home', compact(
            'banners',
            'heroBanners',
            'offerBanner1',
            'offerBanner2',
            'categories',
            'exploreMenuProducts',
            'featuredProducts',
            'popularProducts',
            'bestSellers',
            'specialOffers',
            'offers',
            'testimonials'
        ));
    }
}
