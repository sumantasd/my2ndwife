<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\HomepageBanner;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function index(): View
    {
        $offers = Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->get();

        $banners = HomepageBanner::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $discountedItems = Product::available()
            ->whereNotNull('discount_price')
            ->with(['category', 'variations', 'addonGroups.activeAddons'])
            ->take(12)
            ->get();

        return view('customer.offers', compact('offers', 'banners', 'discountedItems'));
    }

    public function show(string $slug): View
    {
        $banner = HomepageBanner::where('id', $slug)
            ->orWhere('title', 'like', "%{$slug}%")
            ->first();

        $coupon = Coupon::where('code', strtoupper($slug))->first();

        $items = Product::available()
            ->whereNotNull('discount_price')
            ->with(['category', 'variations', 'addonGroups.activeAddons'])
            ->paginate(12);

        return view('customer.offer-details', compact('banner', 'coupon', 'items', 'slug'));
    }
}
