<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\RestaurantSetting;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existingTerms = RestaurantSetting::get('legal_terms') ?: "Welcome to My 2nd Wife Food Delivery System. By accessing our website, placing an order, or utilizing our delivery services in Kolkata, you agree to comply with our service terms, food hygiene protocols, and dispatch conditions.\n\nAll orders are freshly prepared by our culinary team upon confirmation. Delivery times may vary depending on weather, traffic, and distance within our service radius.";

        $existingPrivacy = RestaurantSetting::get('legal_privacy_policy') ?: "At My 2nd Wife, your privacy is paramount. Customer phone numbers, delivery coordinates, and order histories are collected strictly for order fulfillment, delivery routing, and vital service communication.\n\nWe implement industry-standard encryption and security measures. We never sell, rent, or distribute your personal data to third parties.";

        $existingRefund = RestaurantSetting::get('legal_refund_cancellation') ?: "Orders can be cancelled prior to kitchen acceptance for a 100% immediate refund. Due to the perishable nature of gourmet culinary dishes, orders accepted and actively prepared by our kitchen cannot be cancelled.\n\nAny food temperature, packaging, or item discrepancies must be reported within 2 hours of delivery receipt for prompt investigation and replacement or credit.";

        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about',
                'content' => "<h2>Welcome to My 2nd Wife Gourmet Kitchen</h2>\n<p>At <strong>My 2nd Wife</strong>, food is more than sustenance—it is our passion, craft, and heartfelt dedication. Born in the vibrant culinary heart of Kolkata, our mission is to bring royal authentic tastes, homestyle warmth, and contemporary kitchen hygiene right to your doorstep.</p>\n<h3>Our Culinary Philosophy</h3>\n<p>Every single dish on our menu is prepared with handpicked spices, authentic slow dum-cooking methods, and wholesome ingredients sourced daily from verified regional farmers and spice merchants. We do not use artificial preservatives or shortcuts.</p>\n<h3>Piping Hot Delivery Across Kolkata</h3>\n<p>With our fleet of dedicated delivery riders and temperature-insulated packaging, every biryani, tender kebab, and aromatic curry arrives at your table steaming hot, exactly as the chef intended.</p>\n<p>Thank you for letting us serve you and your family!</p>",
                'meta_title' => 'About Us | My 2nd Wife Gourmet Food Delivery Kolkata',
                'meta_description' => 'Discover the story, authentic flavors, and chef-crafted culinary passion behind My 2nd Wife food delivery system in Kolkata.',
                'is_published' => true,
                'display_order' => 1,
            ],
            [
                'title' => 'Help Center',
                'slug' => 'help-center',
                'content' => "<h2>Customer Help & Support Center</h2>\n<p>We are here to assist you with every step of your dining and delivery experience. Browse the essential guides below or get in touch with our live support dispatch.</p>\n<h3>Placing Orders & Tracking</h3>\n<p>Select your favorite dishes from our live menu, add customized spice levels or addons, and checkout seamlessly via Cash on Delivery (COD) or instant UPI/card payments. Real-time GPS and status tracking is available directly from your order tracking screen.</p>\n<h3>Delivery Hours & Coverage</h3>\n<p>We deliver across Kolkata from 11:00 AM to 11:30 PM daily. Delivery fees are dynamically calculated based on actual road distance from our kitchen.</p>\n<h3>Need Urgent Assistance?</h3>\n<p>Contact our Kolkata dispatch line directly via phone or WhatsApp for immediate help regarding ongoing orders.</p>",
                'meta_title' => 'Help Center | My 2nd Wife Customer Assistance',
                'meta_description' => 'Get instant help and answers regarding orders, delivery radius, payments, and dining support at My 2nd Wife.',
                'is_published' => true,
                'display_order' => 2,
            ],
            [
                'title' => 'Frequently Asked Questions',
                'slug' => 'faq',
                'content' => "<h2>Frequently Asked Questions</h2>\n<h3>How fast is food delivery from My 2nd Wife?</h3>\n<p>Our average delivery time is 30 to 45 minutes depending on your location in Kolkata and kitchen order volume. Meals are prepared fresh and dispatched in insulated thermal bags to maintain piping hot temperatures.</p>\n<h3>What payment options do you support?</h3>\n<p>We accept Cash on Delivery (COD), UPI (Google Pay, PhonePe, Paytm, BHIM), Credit/Debit Cards (Visa, Mastercard, RuPay), and NetBanking via our secure checkout gateway.</p>\n<h3>Is there a minimum order amount for free delivery?</h3>\n<p>Free delivery is available on orders above the configured threshold. Orders below this amount carry our standard nominal delivery charge.</p>\n<h3>Can I track my order live?</h3>\n<p>Yes! Once you place an order, you will receive a direct tracking link where you can watch status changes from kitchen confirmation, food preparation, dispatch with rider details, to arrival.</p>",
                'meta_title' => 'FAQ | Frequently Asked Questions | My 2nd Wife',
                'meta_description' => 'Find answers to common questions about food preparation, delivery speed, accepted payments, and order tracking.',
                'is_published' => true,
                'display_order' => 3,
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms',
                'content' => "<h2>Terms & Conditions</h2>\n<p>" . nl2br(e($existingTerms)) . "</p>",
                'meta_title' => 'Terms & Conditions | My 2nd Wife Food Delivery',
                'meta_description' => 'Read our terms and conditions governing food ordering, delivery fulfillment, hygiene standards, and customer rights.',
                'is_published' => true,
                'display_order' => 4,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'content' => "<h2>Privacy Policy</h2>\n<p>" . nl2br(e($existingPrivacy)) . "</p>",
                'meta_title' => 'Privacy Policy | My 2nd Wife Food Delivery',
                'meta_description' => 'Learn how My 2nd Wife protects and securely handles your personal information, phone numbers, and delivery addresses.',
                'is_published' => true,
                'display_order' => 5,
            ],
            [
                'title' => 'Refund & Cancellation Policy',
                'slug' => 'refund-policy',
                'content' => "<h2>Refund & Cancellation Policy</h2>\n<p>" . nl2br(e($existingRefund)) . "</p>",
                'meta_title' => 'Refund & Cancellation Policy | My 2nd Wife Food Delivery',
                'meta_description' => 'Understand our cancellation timelines and prompt refund policies for orders placed with My 2nd Wife.',
                'is_published' => true,
                'display_order' => 6,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'content' => "<h2>Contact My 2nd Wife</h2>\n<p>Have feedback, catering inquiries, or need help with a recent order? Our Kolkata customer care team is always here for you.</p>\n<p><strong>Address:</strong> Plot 42, Park Street Boulevard, Park Circus, Kolkata - 700016</p>\n<p><strong>Operating Hours:</strong> Monday – Sunday, 11:00 AM – 11:30 PM</p>",
                'meta_title' => 'Contact Us | My 2nd Wife Gourmet Kitchen Kolkata',
                'meta_description' => 'Reach out to My 2nd Wife restaurant support via phone, email, WhatsApp, or visit our central kitchen in Kolkata.',
                'is_published' => true,
                'display_order' => 7,
            ],
        ];

        foreach ($pages as $data) {
            Page::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
