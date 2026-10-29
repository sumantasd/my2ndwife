<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\RestaurantSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display the About Us page.
     */
    public function about(): View
    {
        $page = Page::whereIn('slug', ['about', 'about-us'])->first();

        if ($page && $this->isAccessible($page)) {
            return view('customer.page', [
                'page' => $page,
                'sanitizedContent' => $this->sanitizeHtml($page->content),
            ]);
        }

        // Safe fallback if page record is missing
        $title = 'About Us';
        $fallback = "<h2>About My 2nd Wife</h2><p>Welcome to <strong>My 2nd Wife</strong> Gourmet Kitchen. We are dedicated to bringing Kolkata the finest authentic flavors, royal dum-cooked delicacies, and wholesome comfort food delivered directly to your doorstep.</p>";
        $virtualPage = new Page([
            'title' => $title,
            'slug' => 'about',
            'content' => $fallback,
            'meta_title' => 'About Us | My 2nd Wife Food Delivery Kolkata',
            'meta_description' => 'Discover the story, authentic flavors, and chef-crafted culinary passion behind My 2nd Wife food delivery system in Kolkata.',
            'is_published' => true,
        ]);
        $virtualPage->updated_at = now();

        return view('customer.page', [
            'page' => $virtualPage,
            'sanitizedContent' => $fallback,
        ]);
    }

    /**
     * Display Terms & Conditions.
     */
    public function terms(): View
    {
        $page = Page::whereIn('slug', ['terms', 'terms-and-conditions'])->first();

        if ($page && $this->isAccessible($page)) {
            return view('customer.page', [
                'page' => $page,
                'sanitizedContent' => $this->sanitizeHtml($page->content),
            ]);
        }

        // Backward compatibility fallback
        $title = 'Terms & Conditions';
        $policy = RestaurantSetting::get('legal_terms') ?: 'Welcome to My 2nd Wife. By accessing our website, placing an order, or utilizing our delivery services in Kolkata, you agree to comply with our service terms, food hygiene protocols, and dispatch conditions.';
        $deliveryPolicy = RestaurantSetting::get('legal_delivery_policy') ?: 'All meals are cooked fresh to order. Delivery timings vary based on real-time traffic and distance within our service radius.';
        
        return view('customer.legal', [
            'pageTitle' => $title,
            'activeSection' => 'terms',
            'content' => $policy,
            'secondaryContent' => $deliveryPolicy,
        ]);
    }

    /**
     * Display Privacy Policy.
     */
    public function privacy(): View
    {
        $page = Page::whereIn('slug', ['privacy', 'privacy-policy'])->first();

        if ($page && $this->isAccessible($page)) {
            return view('customer.page', [
                'page' => $page,
                'sanitizedContent' => $this->sanitizeHtml($page->content),
            ]);
        }

        $title = 'Privacy Policy';
        $policy = RestaurantSetting::get('legal_privacy_policy') ?: 'At My 2nd Wife, your privacy is paramount. Customer phone numbers, delivery coordinates, and order histories are collected strictly for order fulfillment, delivery routing, and vital service communication. We never sell or distribute your private information to third parties.';
        
        return view('customer.legal', [
            'pageTitle' => $title,
            'activeSection' => 'privacy',
            'content' => $policy,
        ]);
    }

    /**
     * Display Refund & Cancellation Policy.
     */
    public function refund(): View
    {
        $page = Page::whereIn('slug', ['refund', 'refund-policy', 'refund-and-cancellation'])->first();

        if ($page && $this->isAccessible($page)) {
            return view('customer.page', [
                'page' => $page,
                'sanitizedContent' => $this->sanitizeHtml($page->content),
            ]);
        }

        $title = 'Refund & Cancellation Policy';
        $policy = RestaurantSetting::get('legal_refund_cancellation') ?: 'Orders can be cancelled prior to kitchen acceptance for a 100% immediate refund. Due to the perishable nature of gourmet culinary dishes, orders accepted and actively prepared by our kitchen cannot be cancelled. Any food temperature or packing discrepancies must be notified within 2 hours of delivery receipt.';
        
        return view('customer.legal', [
            'pageTitle' => $title,
            'activeSection' => 'refund',
            'content' => $policy,
        ]);
    }

    /**
     * Display dynamic custom website page by slug.
     */
    public function show(string $slug): View
    {
        $page = Page::where('slug', $slug)->first();

        if (!$page) {
            abort(404, 'The requested website page could not be found.');
        }

        if (!$this->isAccessible($page)) {
            abort(404, 'This page is currently unpublished.');
        }

        return view('customer.page', [
            'page' => $page,
            'sanitizedContent' => $this->sanitizeHtml($page->content),
        ]);
    }

    /**
     * Display Contact Us page.
     */
    public function contact(): View
    {
        $phone = RestaurantSetting::get('restaurant_phone', '+91 98300 12345');
        $altPhone = RestaurantSetting::get('restaurant_phone_alt');
        $email = RestaurantSetting::get('restaurant_email', 'hello@my2ndwife.com');
        $address = RestaurantSetting::get('restaurant_address', 'Plot 42, Park Street Boulevard, Park Circus, Kolkata - 700016');
        $city = RestaurantSetting::get('restaurant_city', 'Kolkata');
        $state = RestaurantSetting::get('restaurant_state', 'West Bengal');
        $pincode = RestaurantSetting::get('restaurant_pincode', '700016');
        $openTime = RestaurantSetting::get('opening_time', '11:00 AM');
        $closeTime = RestaurantSetting::get('closing_time', '11:30 PM');
        $mapsUrl = RestaurantSetting::get('restaurant_google_maps_url', 'https://maps.google.com/?q=Park+Street+Kolkata');
        $whatsapp = RestaurantSetting::get('restaurant_whatsapp', '+91 98300 12345');

        return view('customer.contact', compact(
            'phone',
            'altPhone',
            'email',
            'address',
            'city',
            'state',
            'pincode',
            'openTime',
            'closeTime',
            'mapsUrl',
            'whatsapp'
        ));
    }

    /**
     * Display FAQ page.
     */
    public function faq(): View
    {
        $faqs = [
            [
                'question' => 'How fast is food delivery from My 2nd Wife?',
                'answer' => 'Our average delivery time is 30 to 45 minutes depending on your location in Kolkata and kitchen order volume. Meals are prepared fresh and dispatched in insulated thermal bags to maintain piping hot temperatures.',
            ],
            [
                'question' => 'What payment options do you support?',
                'answer' => 'We accept Cash on Delivery (COD), UPI (Google Pay, PhonePe, Paytm, BHIM), Credit/Debit Cards (Visa, Mastercard, RuPay), and NetBanking via our secure checkout gateway.',
            ],
            [
                'question' => 'Is there a minimum order amount for free delivery?',
                'answer' => 'Free delivery is available on orders above the configured threshold (default ₹499). Orders below this amount carry our standard nominal delivery charge.',
            ],
            [
                'question' => 'Can I track my order live?',
                'answer' => 'Yes! Once you place an order, you will receive a direct tracking link where you can watch status changes from kitchen confirmation, food preparation, dispatch with rider details, to arrival.',
            ],
            [
                'question' => 'What if I need to cancel my order?',
                'answer' => 'You can cancel your order directly from your order tracking screen or account dashboard as long as the kitchen has not accepted and started cooking your dishes.',
            ],
            [
                'question' => 'How do I contact customer support?',
                'answer' => 'You can reach out to us directly by phone or WhatsApp at our support line, or email us at our customer support inbox. We reply to all inquiries promptly.',
            ],
        ];

        return view('customer.faq', compact('faqs'));
    }

    /**
     * Determine if a page is accessible (published or viewing as admin preview).
     */
    protected function isAccessible(Page $page): bool
    {
        if ($page->is_published) {
            return true;
        }

        // Allow logged-in administrators to preview unpublished pages
        return Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isStaff());
    }

    /**
     * Sanitize HTML content to prevent XSS.
     */
    protected function sanitizeHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $allowedTags = '<p><br><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><s><strike><ul><ol><li><a><blockquote><code><pre><hr><table><thead><tbody><tr><th><td><span><div><img>';
        $cleaned = strip_tags($html, $allowedTags);

        // Remove dangerous on* attributes (onclick, onload, onerror, etc.)
        $cleaned = preg_replace('/\s+on[a-z]+\s*=\s*(["\']).*?\1/i', '', $cleaned);
        // Remove javascript: in href
        $cleaned = preg_replace('/\s+href\s*=\s*(["\'])\s*javascript:.*?\1/i', ' href="#"', $cleaned);

        return $cleaned;
    }
}
