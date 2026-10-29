<?php

namespace Database\Seeders;

use App\Models\RestaurantSetting;
use Illuminate\Database\Seeder;

class RestaurantSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaultHours = [
            'monday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:30'],
            'tuesday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:30'],
            'wednesday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:30'],
            'thursday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:30'],
            'friday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:30'],
            'saturday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:45'],
            'sunday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:45'],
        ];

        $settings = [
            // 2.1 General Information
            'restaurant_name' => ['My 2nd Wife', 'string', 'general'],
            'restaurant_short_name' => ['My 2nd Wife', 'string', 'general'],
            'restaurant_tagline' => ['Flavors You Can Never Resist', 'string', 'general'],
            'restaurant_short_description' => ['Authentic Indian & Bengali culinary delicacies delivered piping hot.', 'string', 'general'],
            'restaurant_description' => ['Welcome to My 2nd Wife, your destination for mouth-watering Indian Thalis, authentic Bengali curries, freshly prepared tawa breads, and refreshing beverages in Kolkata.', 'string', 'general'],
            'restaurant_email' => ['hello@my2ndwife.com', 'string', 'general'],
            'restaurant_phone' => ['+91 98300 12345', 'string', 'general'],
            'restaurant_phone_alt' => ['', 'string', 'general'],
            'restaurant_whatsapp' => ['+91 98300 12345', 'string', 'general'],
            'gstin' => ['', 'string', 'general'],
            'fssai_license' => ['', 'string', 'general'],
            'is_restaurant_open' => ['1', 'boolean', 'general'],
            'is_temporarily_closed' => ['0', 'boolean', 'general'],
            'temporary_closed_notice' => ['We are temporarily closed for private catering. We will reopen shortly!', 'string', 'general'],
            'minimum_order_amount' => ['199.00', 'float', 'general'],
            'estimated_prep_time_mins' => ['25', 'integer', 'general'],
            'restaurant_timezone' => ['Asia/Kolkata', 'string', 'general'],
            'currency_code' => ['INR', 'string', 'general'],
            'currency_symbol' => ['₹', 'string', 'general'],
            'currency_symbol_position' => ['before', 'string', 'general'],
            'allow_guest_checkout' => ['1', 'boolean', 'general'],

            // 2.2 Branding & Logo
            'restaurant_logo' => ['images/defaults/logo.svg', 'string', 'branding'],
            'restaurant_logo_dark' => ['images/defaults/logo.svg', 'string', 'branding'],
            'restaurant_logo_light' => ['images/defaults/logo.svg', 'string', 'branding'],
            'restaurant_logo_mobile' => ['images/defaults/logo.svg', 'string', 'branding'],
            'restaurant_favicon' => ['images/defaults/favicon.svg', 'string', 'branding'],
            'restaurant_cover_image' => ['images/defaults/banner-placeholder.jpg', 'string', 'branding'],
            'default_food_image' => ['images/defaults/food-placeholder.jpg', 'string', 'branding'],
            'default_category_image' => ['images/defaults/category-placeholder.jpg', 'string', 'branding'],
            'social_sharing_image' => ['images/defaults/banner-placeholder.jpg', 'string', 'branding'],
            'announcement_bar_enabled' => ['1', 'boolean', 'branding'],
            'announcement_bar_text' => ['⚡ Free Delivery on orders above ₹499 | Use code TASTE20 for 20% OFF!', 'string', 'branding'],

            // 3. Contact Information & Address
            'restaurant_address' => ['Plot 42, Park Street Boulevard, Park Circus, Kolkata - 700016', 'string', 'contact'],
            'restaurant_city' => ['Kolkata', 'string', 'contact'],
            'restaurant_district' => ['Kolkata', 'string', 'contact'],
            'restaurant_state' => ['West Bengal', 'string', 'contact'],
            'restaurant_pincode' => ['700016', 'string', 'contact'],
            'restaurant_country' => ['India', 'string', 'contact'],
            'restaurant_google_maps_url' => ['https://maps.google.com/?q=Park+Street+Kolkata', 'string', 'contact'],

            // 4. Google Maps Integration
            'google_maps_api_key' => ['', 'string', 'google_maps'],
            'google_maps_js_enabled' => ['0', 'boolean', 'google_maps'],
            'google_maps_places_enabled' => ['0', 'boolean', 'google_maps'],
            'google_maps_geocoding_enabled' => ['0', 'boolean', 'google_maps'],
            'google_maps_routes_enabled' => ['0', 'boolean', 'google_maps'],
            'restaurant_latitude' => ['22.5535', 'float', 'google_maps'],
            'restaurant_longitude' => ['88.3533', 'float', 'google_maps'],
            'default_map_zoom' => ['14', 'integer', 'google_maps'],
            'map_display_enabled' => ['0', 'boolean', 'google_maps'],
            'customer_autocomplete_enabled' => ['0', 'boolean', 'google_maps'],

            // 5. Delivery Settings
            'delivery_enabled' => ['1', 'boolean', 'delivery'],
            'takeaway_enabled' => ['1', 'boolean', 'delivery'],
            'pickup_available' => ['1', 'boolean', 'delivery'],
            'delivery_radius_km' => ['5.0', 'float', 'delivery'],
            'distance_calculation_method' => ['haversine', 'string', 'delivery'],
            'delivery_fee_calculation_method' => ['fixed', 'string', 'delivery'],
            'default_delivery_charge' => ['30.00', 'float', 'delivery'],
            'delivery_charge_per_km' => ['10.00', 'float', 'delivery'],
            'delivery_tiered_base_km' => ['3.0', 'float', 'delivery'],
            'delivery_tiered_extra_per_km' => ['12.00', 'float', 'delivery'],
            'free_delivery_threshold' => ['499.00', 'float', 'delivery'],
            'min_order_amount_delivery' => ['199.00', 'float', 'delivery'],
            'estimated_delivery_time' => ['30-45 mins', 'string', 'delivery'],
            'delivery_unavailable_message' => ['We do not deliver to this location yet.', 'string', 'delivery'],
            'delivery_operating_hours' => ['11:00 AM - 11:30 PM', 'string', 'delivery'],
            'delivery_boy_commission_amount' => ['35.00', 'float', 'delivery'],
            'delivery_boy_self_claim_enabled' => ['1', 'boolean', 'delivery'],

            // 6. Opening Hours & Availability
            'opening_time' => ['11:00', 'string', 'hours'],
            'closing_time' => ['23:30', 'string', 'hours'],
            'opening_hours_json' => [json_encode($defaultHours), 'json', 'hours'],
            'scheduled_orders_available' => ['1', 'boolean', 'hours'],

            // 7. Order Settings, Tax & Charges
            'packaging_charge' => ['25.00', 'float', 'orders'],
            'tax_enabled' => ['1', 'boolean', 'orders'],
            'tax_rate_percentage' => ['5.00', 'float', 'orders'],
            'tax_pricing_type' => ['exclusive', 'string', 'orders'],
            'order_cancellation_policy' => ['Orders can only be cancelled before kitchen acceptance.', 'string', 'orders'],
            'customer_notes_enabled' => ['1', 'boolean', 'orders'],
            'delivery_instructions_enabled' => ['1', 'boolean', 'orders'],
            'order_auto_confirm' => ['0', 'boolean', 'orders'],

            // 8. Payment Settings
            'cod_enabled' => ['1', 'boolean', 'payment'],
            'cod_display_name' => ['Cash on Delivery (COD)', 'string', 'payment'],
            'razorpay_enabled' => ['1', 'boolean', 'payment'],
            'razorpay_display_name' => ['Online Payment (UPI, Cards, NetBanking)', 'string', 'payment'],
            'razorpay_key_id' => ['', 'string', 'payment'],
            'razorpay_key_secret' => ['', 'string', 'payment'],
            'payment_mode' => ['test', 'string', 'payment'],

            // 9. Notifications
            'order_notification_email' => ['orders@my2ndwife.com', 'string', 'notifications'],
            'customer_order_email_enabled' => ['1', 'boolean', 'notifications'],
            'admin_order_notification_enabled' => ['1', 'boolean', 'notifications'],
            'sms_enabled' => ['0', 'boolean', 'notifications'],
            'sms_provider' => ['msg91', 'string', 'notifications'],
            'sms_api_key' => ['', 'string', 'notifications'],
            'sms_sender_id' => ['M2WIFE', 'string', 'notifications'],
            'whatsapp_notifications_enabled' => ['0', 'boolean', 'notifications'],
            'whatsapp_provider' => ['interakt', 'string', 'notifications'],
            'whatsapp_api_key' => ['', 'string', 'notifications'],

            // 10. SEO & Social Media
            'meta_title' => ['My 2nd Wife | Authentic Indian Food & Delivery Kolkata', 'string', 'seo'],
            'meta_description' => ['Order delicious Indian Veg & Non-Veg Thalis, homestyle curries, fresh rotis, and refreshing beverages in Kolkata.', 'string', 'seo'],
            'meta_keywords' => ['Indian thali delivery, Kolkata food order, veg thali, chicken thali, aloo posto, online food delivery', 'string', 'seo'],
            'facebook_url' => ['https://facebook.com/my2ndwiferestaurant', 'string', 'seo'],
            'instagram_url' => ['https://instagram.com/my2ndwife_food', 'string', 'seo'],
            'youtube_url' => ['https://youtube.com/@my2ndwife', 'string', 'seo'],
            'twitter_url' => ['https://twitter.com/my2ndwife_food', 'string', 'seo'],

            // 11. Legal Information
            'legal_about_us' => ['My 2nd Wife is a culinary celebration of authentic Indian and Bengali home-style comfort food, prepared with pure ingredients, traditional spices, and time-honored recipes.', 'string', 'legal'],
            'legal_contact_us' => ['Have questions or feedback? Call us at +91 98300 12345 or email hello@my2ndwife.com. We are open daily from 11:00 AM to 11:30 PM.', 'string', 'legal'],
            'legal_privacy_policy' => ['We respect your privacy. Customer delivery details, contact numbers, and personal info are solely used for order fulfillment and never shared.', 'string', 'legal'],
            'legal_terms' => ['By placing an order on My 2nd Wife, you agree to our standard terms of food delivery, hygiene compliance, and dispatch guidelines.', 'string', 'legal'],
            'legal_refund_cancellation' => ['Orders can be cancelled before kitchen confirmation for a full refund. Food quality issues must be reported within 2 hours of delivery.', 'string', 'legal'],
            'legal_delivery_policy' => ['We deliver freshly cooked meals within our service radius. Takeaway pickup is available for all addresses.', 'string', 'legal'],
        ];

        foreach ($settings as $key => [$val, $type, $group]) {
            // Only create if setting does not exist yet to preserve intentional admin changes!
            $existing = RestaurantSetting::where('key', $key)->first();
            if (!$existing) {
                RestaurantSetting::create([
                    'key' => $key,
                    'value' => $val,
                    'type' => $type,
                    'group' => $group,
                ]);
            }
        }

        \App\Models\FooterLink::ensureDefaults();
    }
}
