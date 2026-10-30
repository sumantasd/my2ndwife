<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\HomepageBanner;
use App\Models\RestaurantSetting;
use Illuminate\Http\JsonResponse;

class ApiSettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = RestaurantSetting::getAll();
        $banners = HomepageBanner::active()->get();

        return response()->json([
            'success' => true,
            'restaurant' => [
                'name' => $settings['restaurant_name'] ?? 'My 2nd Wife',
                'tagline' => $settings['restaurant_tagline'] ?? '',
                'phone' => $settings['restaurant_phone'] ?? '',
                'address' => $settings['restaurant_address'] ?? '',
                'is_open' => (bool) ($settings['is_restaurant_open'] ?? true),
                'opening_time' => $settings['opening_time'] ?? '11:00',
                'closing_time' => $settings['closing_time'] ?? '23:30',
                'currency_symbol' => $settings['currency_symbol'] ?? '₹',
                'min_order' => (float) ($settings['minimum_order_amount'] ?? 199.00),
                'delivery_charge' => (float) ($settings['default_delivery_charge'] ?? 40.00),
                'free_delivery_threshold' => (float) ($settings['free_delivery_threshold'] ?? 499.00),
                'packaging_charge' => (float) ($settings['packaging_charge'] ?? 25.00),
                'tax_rate' => (float) ($settings['tax_rate_percentage'] ?? 5.00),
            ],
            'legal_links' => [
                'privacy_policy' => url('/privacy'),
                'terms' => url('/terms'),
                'refund_policy' => url('/refund-policy'),
                'account_deletion' => url('/page/play-store-app-delete'),
            ],
            'banners' => $banners,
        ]);
    }
}
