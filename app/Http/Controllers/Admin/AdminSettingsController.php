<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\HomepageBanner;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\RestaurantSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->query('tab') === 'footer') {
            return redirect()->route('admin.footer.index');
        }

        $settings = RestaurantSetting::getAll();
        $banners = HomepageBanner::orderBy('display_order')->get();
        $heroBanners = HomepageBanner::hero()->orderBy('display_order')->get();
        $offer1 = HomepageBanner::offer1()->first();
        $offer2 = HomepageBanner::offer2()->first();

        $openingHours = $settings['opening_hours_json'] ?? null;
        if (is_string($openingHours)) {
            $openingHours = json_decode($openingHours, true);
        }
        if (!is_array($openingHours)) {
            $openingHours = [
                'monday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:30', 'slot2_open' => '', 'slot2_close' => ''],
                'tuesday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:30', 'slot2_open' => '', 'slot2_close' => ''],
                'wednesday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:30', 'slot2_open' => '', 'slot2_close' => ''],
                'thursday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:30', 'slot2_open' => '', 'slot2_close' => ''],
                'friday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:30', 'slot2_open' => '', 'slot2_close' => ''],
                'saturday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:45', 'slot2_open' => '', 'slot2_close' => ''],
                'sunday' => ['is_open' => true, 'open_time' => '11:00', 'close_time' => '23:45', 'slot2_open' => '', 'slot2_close' => ''],
            ];
        }

        // Masked secrets for view
        $firebaseService = app(\App\Services\FirebaseService::class);
        $firebaseCreds = $firebaseService->getCredentials();
        if (empty($settings['firebase_project_id']) && !empty($firebaseCreds['project_id'])) {
            $settings['firebase_project_id'] = $firebaseCreds['project_id'];
        }
        if (empty($settings['firebase_client_email']) && !empty($firebaseCreds['client_email'])) {
            $settings['firebase_client_email'] = $firebaseCreds['client_email'];
        }

        $maskedSecrets = [
            'google_maps_api_key' => $this->maskSecret($settings['google_maps_api_key'] ?? ''),
            'razorpay_key_secret' => $this->maskSecret($settings['razorpay_key_secret'] ?? ''),
            'razorpay_webhook_secret' => $this->maskSecret($settings['razorpay_webhook_secret'] ?? ''),
            'sms_api_key' => $this->maskSecret($settings['sms_api_key'] ?? ''),
            'whatsapp_api_key' => $this->maskSecret($settings['whatsapp_api_key'] ?? ''),
            'firebase_private_key' => $this->maskSecret($settings['firebase_private_key'] ?? ($firebaseCreds['private_key'] ?? '')),
        ];

        $pages = \App\Models\Page::orderBy('display_order')->get();
        $firebaseStatus = $firebaseService->getConfigurationStatus();

        return view('admin.settings.index', compact('settings', 'banners', 'heroBanners', 'offer1', 'offer2', 'openingHours', 'maskedSecrets', 'pages', 'firebaseStatus'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $section = $request->input('_section', 'general');

        // 1. Validate based on section or general input
        $validated = $request->validate([
            'restaurant_name' => ['nullable', 'string', 'max:150'],
            'restaurant_short_name' => ['nullable', 'string', 'max:60'],
            'restaurant_tagline' => ['nullable', 'string', 'max:255'],
            'restaurant_short_description' => ['nullable', 'string', 'max:500'],
            'restaurant_description' => ['nullable', 'string', 'max:2000'],
            'restaurant_email' => ['nullable', 'email', 'max:150'],
            'restaurant_phone' => ['nullable', 'string', 'max:30'],
            'restaurant_phone_alt' => ['nullable', 'string', 'max:30'],
            'restaurant_whatsapp' => ['nullable', 'string', 'max:30'],
            'gstin' => ['nullable', 'string', 'max:30'],
            'fssai_license' => ['nullable', 'string', 'max:30'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
            'estimated_prep_time_mins' => ['nullable', 'integer', 'min:1'],
            'restaurant_timezone' => ['nullable', 'string', 'max:50', 'timezone:all'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'currency_symbol_position' => ['nullable', 'in:before,after'],
            'restaurant_address' => ['nullable', 'string', 'max:300'],
            'restaurant_city' => ['nullable', 'string', 'max:100'],
            'restaurant_district' => ['nullable', 'string', 'max:100'],
            'restaurant_state' => ['nullable', 'string', 'max:100'],
            'restaurant_pincode' => ['nullable', 'string', 'max:20'],
            'restaurant_country' => ['nullable', 'string', 'max:60'],
            'restaurant_google_maps_url' => ['nullable', 'url', 'max:500'],

            // Google Maps
            'google_maps_api_key' => ['nullable', 'string', 'max:200'],
            'restaurant_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'restaurant_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'default_map_zoom' => ['nullable', 'integer', 'between:1,21'],

            // Delivery
            'delivery_radius_km' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
            'distance_calculation_method' => ['nullable', 'in:haversine,google_routes'],
            'delivery_fee_calculation_method' => ['nullable', 'in:fixed,per_km,distance_tiered'],
            'default_delivery_charge' => ['nullable', 'numeric', 'min:0'],
            'delivery_charge_per_km' => ['nullable', 'numeric', 'min:0'],
            'delivery_tiered_base_km' => ['nullable', 'numeric', 'min:0'],
            'delivery_tiered_extra_per_km' => ['nullable', 'numeric', 'min:0'],
            'free_delivery_threshold' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount_delivery' => ['nullable', 'numeric', 'min:0'],
            'estimated_delivery_time' => ['nullable', 'string', 'max:100'],
            'delivery_unavailable_message' => ['nullable', 'string', 'max:300'],
            'delivery_operating_hours' => ['nullable', 'string', 'max:100'],

            // Opening Hours
            'opening_time' => ['nullable', 'string', 'max:10'],
            'closing_time' => ['nullable', 'string', 'max:10'],
            'opening_hours' => ['nullable', 'array'],

            // Order, Tax & Charges
            'packaging_charge' => ['nullable', 'numeric', 'min:0'],
            'tax_rate_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_pricing_type' => ['nullable', 'in:inclusive,exclusive'],
            'order_cancellation_policy' => ['nullable', 'string', 'max:500'],

            // Payment
            'cod_display_name' => ['nullable', 'string', 'max:100'],
            'razorpay_display_name' => ['nullable', 'string', 'max:100'],
            'razorpay_key_id' => ['nullable', 'string', 'max:150'],
            'razorpay_key_secret' => ['nullable', 'string', 'max:150'],
            'razorpay_webhook_secret' => ['nullable', 'string', 'max:150'],
            'payment_mode' => ['nullable', 'in:test,live'],

            // Notifications
            'order_notification_email' => ['nullable', 'email', 'max:150'],
            'sms_provider' => ['nullable', 'string', 'max:50'],
            'sms_api_key' => ['nullable', 'string', 'max:200'],
            'sms_sender_id' => ['nullable', 'string', 'max:30'],
            'whatsapp_provider' => ['nullable', 'string', 'max:50'],
            'whatsapp_api_key' => ['nullable', 'string', 'max:200'],
            'firebase_project_id' => ['nullable', 'string', 'max:100'],
            'firebase_client_email' => ['nullable', 'string', 'max:150'],
            'firebase_private_key' => ['nullable', 'string'],
            'firebase_service_account_file' => ['nullable', 'file', 'mimetypes:application/json,text/plain,application/octet-stream', 'max:2048'],
            'firebase_channel_id' => ['nullable', 'string', 'max:100'],
            'firebase_customer_events' => ['nullable', 'array'],
            'firebase_rider_events' => ['nullable', 'array'],
            'firebase_admin_events' => ['nullable', 'array'],

            // SEO & Social
            'meta_title' => ['nullable', 'string', 'max:150'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'meta_keywords' => ['nullable', 'string', 'max:300'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'restaurant_whatsapp' => ['nullable', 'string', 'max:50'],

            // Footer & App Promotion
            'footer_description' => ['nullable', 'string', 'max:500'],
            'footer_copyright_text' => ['nullable', 'string', 'max:300'],
            'app_android_url' => ['nullable', 'string', 'max:255'],
            'app_ios_url' => ['nullable', 'string', 'max:255'],

            // Branding File Uploads
            'restaurant_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'restaurant_logo_dark' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'restaurant_logo_light' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'restaurant_logo_mobile' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'restaurant_favicon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg,ico', 'max:2048'],
            'restaurant_cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'default_food_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'default_category_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'social_sharing_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
        ]);

        // Cross-field validation: Mode vs Key ID prefix
        if ($section === 'payment' && $request->filled('razorpay_key_id') && $request->filled('payment_mode')) {
            $keyId = trim((string) $request->input('razorpay_key_id'));
            $mode = $request->input('payment_mode');
            if ($mode === 'test' && str_starts_with($keyId, 'rzp_live_')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'razorpay_key_id' => 'Cannot use a Live Key ID (rzp_live_...) in Test / Sandbox mode.',
                ]);
            }
            if ($mode === 'live' && str_starts_with($keyId, 'rzp_test_')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'razorpay_key_id' => 'Cannot use a Test Key ID (rzp_test_...) in Live / Production mode.',
                ]);
            }
        }

        // 2. Handle File Uploads
        $fileFields = [
            'restaurant_logo',
            'restaurant_logo_dark',
            'restaurant_logo_light',
            'restaurant_logo_mobile',
            'restaurant_favicon',
            'restaurant_cover_image',
            'default_food_image',
            'default_category_image',
            'social_sharing_image',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $oldSetting = RestaurantSetting::where('key', $field)->first();
                $path = $request->file($field)->store('branding', 'public');
                RestaurantSetting::set($field, 'storage/' . $path, 'branding', 'string');

                // Safely delete previous uploaded file if stored in storage/branding/ and not default placeholder
                if ($oldSetting && !empty($oldSetting->value) && str_starts_with($oldSetting->value, 'storage/branding/')) {
                    $oldFilePath = str_replace('storage/', '', $oldSetting->value);
                    if ($oldFilePath !== $path && Storage::disk('public')->exists($oldFilePath)) {
                        Storage::disk('public')->delete($oldFilePath);
                    }
                }
            }
        }

        // 3. Handle Masked Secret Fields (Never erase if submitted empty or masked)
        $secretFields = [
            'google_maps_api_key' => 'google_maps',
            'razorpay_key_secret' => 'payment',
            'razorpay_webhook_secret' => 'payment',
            'sms_api_key' => 'notifications',
            'whatsapp_api_key' => 'notifications',
            'firebase_private_key' => 'notifications',
        ];

        foreach ($secretFields as $field => $group) {
            if ($request->has($field)) {
                $submittedVal = trim($request->input($field));
                // Only update if not blank and not masked dots
                if ($submittedVal !== '' && !str_starts_with($submittedVal, '••••')) {
                    RestaurantSetting::set($field, $submittedVal, $group, 'string');
                }
            }
        }

        // Handle uploaded Firebase Service Account JSON file with strict validation
        $serviceAccountUploaded = false;
        if ($request->hasFile('firebase_service_account_file')) {
            $file = $request->file('firebase_service_account_file');
            $rawContent = file_get_contents($file->getRealPath());
            $parsed = json_decode($rawContent, true);

            if (!is_array($parsed)) {
                return back()->with('error', 'Uploaded Service Account file is not valid JSON format. Existing settings were preserved.')->withInput();
            }

            // Validate required fields
            $missingFields = [];
            if (empty($parsed['project_id'])) $missingFields[] = 'project_id';
            if (empty($parsed['client_email'])) $missingFields[] = 'client_email';
            if (empty($parsed['private_key'])) $missingFields[] = 'private_key';

            if (!empty($missingFields)) {
                return back()->with('error', 'Service Account JSON is missing required fields: ' . implode(', ', $missingFields) . '. Existing configuration was preserved.')->withInput();
            }

            // Cryptographically validate RSA private key format
            $fs = app(\App\Services\FirebaseService::class);
            if (!$fs->validatePrivateKeyFormat($parsed['private_key'])) {
                return back()->with('error', 'Service Account JSON contains an invalid RSA private key format that cannot be parsed by OpenSSL. Existing configuration was preserved.')->withInput();
            }

            // Valid! Store securely in storage/app/firebase/
            $firebaseDir = storage_path('app/firebase');
            if (!\Illuminate\Support\Facades\File::exists($firebaseDir)) {
                \Illuminate\Support\Facades\File::makeDirectory($firebaseDir, 0750, true);
            }
            $targetPath = $firebaseDir . '/service-account.json';
            \Illuminate\Support\Facades\File::put($targetPath, $rawContent);
            @chmod($targetPath, 0600);

            // Ensure .gitignore exists in firebase directory to prevent credential leaks
            $gitignorePath = $firebaseDir . '/.gitignore';
            if (!\Illuminate\Support\Facades\File::exists($gitignorePath)) {
                \Illuminate\Support\Facades\File::put($gitignorePath, "*\n!.gitignore\n");
            }

            RestaurantSetting::set('firebase_project_id', trim((string) $parsed['project_id']), 'notifications');
            RestaurantSetting::set('firebase_client_email', trim((string) $parsed['client_email']), 'notifications');
            RestaurantSetting::set('firebase_private_key', $fs->normalizePrivateKey($parsed['private_key']), 'notifications');

            \Illuminate\Support\Facades\Cache::forget(\App\Services\FirebaseService::OAUTH_STATUS_CACHE_KEY);
            \Illuminate\Support\Facades\Cache::forget(\App\Services\FirebaseService::TOKEN_CACHE_KEY);

            $serviceAccountUploaded = true;
        }

        // Handle Event Multi-select Checkboxes for Notifications
        if ($section === 'notifications') {
            RestaurantSetting::set('firebase_customer_events', $request->input('firebase_customer_events', []), 'notifications', 'json');
            RestaurantSetting::set('firebase_rider_events', $request->input('firebase_rider_events', []), 'notifications', 'json');
            RestaurantSetting::set('firebase_admin_events', $request->input('firebase_admin_events', []), 'notifications', 'json');
        }

        // 4. Handle Opening Hours JSON
        if ($request->has('opening_hours') && is_array($request->opening_hours)) {
            $hours = [];
            foreach ($request->opening_hours as $day => $vals) {
                $hours[$day] = [
                    'is_open' => isset($vals['is_open']) && $vals['is_open'] == '1',
                    'open_time' => $vals['open_time'] ?? '11:00',
                    'close_time' => $vals['close_time'] ?? '23:30',
                    'slot2_open' => $vals['slot2_open'] ?? '',
                    'slot2_close' => $vals['slot2_close'] ?? '',
                ];
            }
            RestaurantSetting::set('opening_hours_json', $hours, 'hours', 'json');
        }

        // 5. Handle Checkboxes / Booleans for this section
        $sectionBooleans = [
            'general' => ['is_restaurant_open', 'is_temporarily_closed', 'allow_guest_checkout'],
            'branding' => ['announcement_bar_enabled'],
            'google_maps' => ['google_maps_js_enabled', 'google_maps_places_enabled', 'google_maps_geocoding_enabled', 'google_maps_routes_enabled', 'map_display_enabled', 'customer_autocomplete_enabled'],
            'delivery' => ['delivery_enabled', 'takeaway_enabled', 'pickup_available'],
            'hours' => ['scheduled_orders_available'],
            'orders' => ['tax_enabled', 'customer_notes_enabled', 'delivery_instructions_enabled', 'order_auto_confirm'],
            'payment' => ['cod_enabled', 'razorpay_enabled'],
            'notifications' => ['customer_order_email_enabled', 'admin_order_notification_enabled', 'sms_enabled', 'whatsapp_notifications_enabled', 'firebase_notifications_enabled'],
            'footer' => [
                'footer_newsletter_enabled',
                'footer_app_download_enabled',
                'app_pwa_enabled',
                'footer_payment_methods_enabled',
                'footer_decorations_enabled',
                'facebook_enabled',
                'instagram_enabled',
                'twitter_enabled',
                'youtube_enabled',
                'linkedin_enabled',
                'whatsapp_enabled',
                'footer_quick_links_enabled',
                'footer_support_links_enabled',
            ],
            'receipt' => [
                'receipt_show_logo',
                'receipt_show_restaurant_name',
                'receipt_show_address',
                'receipt_show_phone',
                'receipt_show_order_number',
                'receipt_show_order_date',
                'receipt_show_customer_name',
                'receipt_show_customer_phone',
                'receipt_show_delivery_address',
                'receipt_show_unit_prices',
                'receipt_show_line_totals',
                'receipt_show_subtotal',
                'receipt_show_discounts',
                'receipt_show_delivery_charge',
                'receipt_show_packaging_charge',
                'receipt_show_tax',
                'receipt_show_grand_total',
                'receipt_show_payment_details',
                'receipt_show_notes',
                'receipt_show_footer_message',
            ],
        ];

        $allBooleans = array_merge(...array_values($sectionBooleans));

        // 6. Save all regular input fields (excluding booleans, files, secrets, opening hours, custom event arrays)
        $ignoredKeys = array_merge(
            ['_token', '_section', 'opening_hours', 'firebase_service_account_file', 'firebase_customer_events', 'firebase_rider_events', 'firebase_admin_events'],
            $fileFields,
            array_keys($secretFields),
            $allBooleans
        );
        $inputs = $request->except($ignoredKeys);

        // Prevent empty form text inputs from erasing parsed Service Account credentials
        if ($serviceAccountUploaded) {
            unset($inputs['firebase_project_id'], $inputs['firebase_client_email']);
        } else {
            if (empty($inputs['firebase_project_id']) && RestaurantSetting::get('firebase_project_id')) {
                unset($inputs['firebase_project_id']);
            }
            if (empty($inputs['firebase_client_email']) && RestaurantSetting::get('firebase_client_email')) {
                unset($inputs['firebase_client_email']);
            }
        }

        foreach ($inputs as $key => $val) {
            RestaurantSetting::set($key, $val, $section);
        }

        if (isset($sectionBooleans[$section])) {
            foreach ($sectionBooleans[$section] as $b) {
                RestaurantSetting::set($b, $request->boolean($b), $section, 'boolean');
            }
        } else {
            // General fallback: if full save submitted
            foreach ($allBooleans as $b) {
                if ($request->has($b)) {
                    RestaurantSetting::set($b, $request->boolean($b), 'general', 'boolean');
                }
            }
        }

        $successMessage = 'Restaurant settings saved successfully.';

        // Perform safe environment synchronization for payment, google_maps, and notifications
        $envSyncService = app(\App\Services\EnvSyncService::class);
        if ($section === 'payment') {
            $syncRes = $envSyncService->sync([
                'RAZORPAY_KEY_ID' => RestaurantSetting::get('razorpay_key_id'),
                'RAZORPAY_KEY_SECRET' => RestaurantSetting::get('razorpay_key_secret'),
                'RAZORPAY_WEBHOOK_SECRET' => RestaurantSetting::get('razorpay_webhook_secret'),
            ]);
            if ($syncRes['success'] && !empty($syncRes['synced'])) {
                $successMessage = 'Payment settings saved to database and synchronized to environment successfully.';
            } elseif (!$syncRes['success']) {
                $successMessage = 'Payment settings saved to database. Notice: ' . $syncRes['message'];
            }
        } elseif ($section === 'google_maps') {
            $syncRes = $envSyncService->sync([
                'GOOGLE_MAPS_API_KEY' => RestaurantSetting::get('google_maps_api_key'),
            ]);
            if ($syncRes['success'] && !empty($syncRes['synced'])) {
                $successMessage = 'Google Maps settings saved to database and synchronized to environment successfully.';
            } elseif (!$syncRes['success']) {
                $successMessage = 'Google Maps settings saved to database. Notice: ' . $syncRes['message'];
            }
        } elseif ($section === 'notifications') {
            $syncRes = $envSyncService->sync([
                'FIREBASE_PROJECT_ID' => RestaurantSetting::get('firebase_project_id'),
                'FIREBASE_CLIENT_EMAIL' => RestaurantSetting::get('firebase_client_email'),
            ]);
            if ($syncRes['success'] && !empty($syncRes['synced'])) {
                $successMessage = 'Notification and Firebase settings saved to database and synchronized to environment successfully.';
            } elseif (!$syncRes['success']) {
                $successMessage = 'Notification settings saved to database. Notice: ' . $syncRes['message'];
            }
        }

        AuditLog::record('settings_updated', 'RestaurantSetting', null, ['section' => $section]);

        if ($section === 'footer') {
            return redirect()->route('admin.footer.index')->with('success', 'Footer settings saved successfully.');
        }

        return back()->with('success', $successMessage)->with('active_tab', $section);
    }

    private function maskSecret(?string $val): string
    {
        if (empty($val)) {
            return '';
        }
        $len = strlen($val);
        if ($len <= 4) {
            return '••••';
        }
        return '••••••••' . substr($val, -4);
    }

    public function storeBanner(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'subtitle' => ['nullable', 'string', 'max:250'],
            'name' => ['nullable', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:150'],
            'subtitle' => ['nullable', 'string', 'max:250'],
            'badge_text' => ['nullable', 'string', 'max:80'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'secondary_text' => ['nullable', 'string', 'max:150'],
            'bg_gradient' => ['nullable', 'string', 'max:150'],
            'text_color' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $buttonUrl = $validated['button_url'] ?? '/menu';
        if (!empty($buttonUrl) && !str_starts_with($buttonUrl, '/') && !str_starts_with($buttonUrl, 'http://') && !str_starts_with($buttonUrl, 'https://')) {
            $buttonUrl = '/' . ltrim($buttonUrl, '/');
        }

        $imagePath = 'images/banners/banner-biryani.svg';
        if ($request->hasFile('image')) {
            $imagePath = 'storage/' . $request->file('image')->store('banners', 'public');
        }

        $mobileImagePath = null;
        if ($request->hasFile('mobile_image')) {
            $mobileImagePath = 'storage/' . $request->file('mobile_image')->store('banners', 'public');
        }

        HomepageBanner::create([
            'name' => $validated['name'] ?? null,
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'badge_text' => $validated['badge_text'] ?? null,
            'button_text' => $validated['button_text'] ?? 'Order Now',
            'button_url' => $buttonUrl,
            'secondary_text' => $validated['secondary_text'] ?? 'Delivered hot & fresh in 30 mins',
            'bg_gradient' => $validated['bg_gradient'] ?? 'mint',
            'text_color' => $validated['text_color'] ?? '#FFFFFF',
            'image_path' => $imagePath,
            'mobile_image_path' => $mobileImagePath,
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        AuditLog::record('banner_created', 'HomepageBanner', null, ['title' => $validated['title']]);

        return back()->with('success', 'Homepage promotional banner added successfully.')->with('active_tab', 'banners');
    }

    public function updateBanner(Request $request, HomepageBanner $banner): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:150'],
            'subtitle' => ['nullable', 'string', 'max:250'],
            'badge_text' => ['nullable', 'string', 'max:80'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'secondary_text' => ['nullable', 'string', 'max:150'],
            'bg_gradient' => ['nullable', 'string', 'max:150'],
            'text_color' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $buttonUrl = $validated['button_url'] ?? '/menu';
        if (!empty($buttonUrl) && !str_starts_with($buttonUrl, '/') && !str_starts_with($buttonUrl, 'http://') && !str_starts_with($buttonUrl, 'https://')) {
            $buttonUrl = '/' . ltrim($buttonUrl, '/');
        }

        if ($request->hasFile('image')) {
            $newPath = 'storage/' . $request->file('image')->store('banners', 'public');

            // Safely delete previous uploaded file if in storage/banners/ and not used elsewhere
            if (!empty($banner->image_path) && str_starts_with($banner->image_path, 'storage/banners/')) {
                $oldFile = str_replace('storage/', '', $banner->image_path);
                $isReferenced = HomepageBanner::where('id', '!=', $banner->id)
                    ->where(function ($q) use ($banner) {
                        $q->where('image_path', $banner->image_path)
                            ->orWhere('mobile_image_path', $banner->image_path);
                    })
                    ->exists();

                if (!$isReferenced && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            $banner->image_path = $newPath;
        }

        if ($request->hasFile('mobile_image')) {
            $newMobilePath = 'storage/' . $request->file('mobile_image')->store('banners', 'public');

            if (!empty($banner->mobile_image_path) && str_starts_with($banner->mobile_image_path, 'storage/banners/')) {
                $oldMobileFile = str_replace('storage/', '', $banner->mobile_image_path);
                $isReferenced = HomepageBanner::where('id', '!=', $banner->id)
                    ->where(function ($q) use ($banner) {
                        $q->where('mobile_image_path', $banner->mobile_image_path)
                            ->orWhere('image_path', $banner->mobile_image_path);
                    })
                    ->exists();

                if (!$isReferenced && Storage::disk('public')->exists($oldMobileFile)) {
                    Storage::disk('public')->delete($oldMobileFile);
                }
            }

            $banner->mobile_image_path = $newMobilePath;
        }

        $banner->name = $validated['name'] ?? $banner->name;
        $banner->title = $validated['title'];
        $banner->subtitle = $validated['subtitle'] ?? null;
        $banner->badge_text = $validated['badge_text'] ?? null;
        $banner->button_text = $validated['button_text'] ?? 'Order Now';
        $banner->button_url = $buttonUrl;
        $banner->secondary_text = $validated['secondary_text'] ?? $banner->secondary_text;
        $banner->bg_gradient = $validated['bg_gradient'] ?? $banner->bg_gradient;
        $banner->text_color = $validated['text_color'] ?? $banner->text_color ?? '#FFFFFF';
        $banner->display_order = $validated['display_order'] ?? $banner->display_order;
        $banner->is_active = $request->boolean('is_active', true);
        $banner->starts_at = $validated['starts_at'] ?? null;
        $banner->ends_at = $validated['ends_at'] ?? null;
        $banner->save();

        AuditLog::record('banner_updated', 'HomepageBanner', $banner->id, ['title' => $banner->title]);

        return back()->with('success', 'Banner updated successfully.')->with('active_tab', 'banners');
    }

    public function toggleBanner(HomepageBanner $banner): RedirectResponse
    {
        $banner->is_active = !$banner->is_active;
        $banner->save();

        AuditLog::record('banner_toggled', 'HomepageBanner', $banner->id, ['is_active' => $banner->is_active]);

        $status = $banner->is_active ? 'enabled' : 'disabled';
        return back()->with('success', "Banner '{$banner->title}' has been {$status}.")->with('active_tab', 'banners');
    }

    public function destroyBanner(HomepageBanner $banner): RedirectResponse
    {
        if (!empty($banner->image_path) && str_starts_with($banner->image_path, 'storage/banners/')) {
            $relativePath = str_replace('storage/', '', $banner->image_path);
            $isReferenced = HomepageBanner::where('id', '!=', $banner->id)
                ->where('image_path', $banner->image_path)
                ->exists();

            if (!$isReferenced && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        AuditLog::record('banner_deleted', 'HomepageBanner', $banner->id, ['title' => $banner->title]);

        $banner->delete();

        return back()->with('success', 'Banner removed successfully.')->with('active_tab', 'banners');
    }

    public function testReceipt(): View
    {
        $order = Order::with(['items.addons', 'payment', 'deliveryPerson'])->latest()->first();

        if (!$order) {
            $order = new Order([
                'order_number' => 'M2W-SAMPLE-01',
                'order_type' => 'delivery',
                'customer_name' => 'John Doe (Sample)',
                'customer_phone' => '+91 98765 43210',
                'customer_email' => 'john.doe@example.com',
                'delivery_address_text' => 'Flat 4B, Park Street Towers, Kolkata - 700016',
                'delivery_instructions' => 'Ring bell twice, leave with security if unavailable',
                'order_status' => Order::STATUS_ACCEPTED,
                'subtotal' => 450.00,
                'discount_amount' => 50.00,
                'coupon_code' => 'WELCOME50',
                'delivery_charge' => 40.00,
                'packaging_charge' => 20.00,
                'tax_amount' => 23.00,
                'total_amount' => 483.00,
                'internal_notes' => 'Customer requested extra mint chutney & cutlery',
                'created_at' => now(),
            ]);
            $order->id = 99999;

            $item1 = new OrderItem([
                'product_name' => 'Butter Chicken Deluxe',
                'variation_name' => 'Regular',
                'quantity' => 1,
                'unit_price' => 299.00,
                'line_total' => 299.00,
            ]);
            $item2 = new OrderItem([
                'product_name' => 'Garlic Butter Naan',
                'variation_name' => null,
                'quantity' => 2,
                'unit_price' => 75.50,
                'line_total' => 151.00,
            ]);
            $order->setRelation('items', collect([$item1, $item2]));

            $payment = new Payment([
                'payment_method' => 'cash_on_delivery',
                'amount' => 483.00,
                'status' => 'pending',
            ]);
            $order->setRelation('payment', $payment);
        }

        $settings = RestaurantSetting::getAll();

        return view('admin.orders.receipt', compact('order', 'settings'));
    }

    /**
     * Parse and cryptographically validate uploaded Firebase Service Account JSON file via AJAX.
     * Never returns private key to the frontend.
     */
    public function parseServiceAccountJson(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimetypes:application/json,text/plain,application/octet-stream', 'max:2048'],
        ]);

        try {
            $rawContent = file_get_contents($request->file('file')->getRealPath());
            $parsed = json_decode($rawContent, true);

            if (!is_array($parsed)) {
                return response()->json([
                    'success' => false,
                    'error' => 'The selected file is not valid JSON.',
                ], 422);
            }

            $missing = [];
            if (empty($parsed['project_id'])) $missing[] = 'project_id';
            if (empty($parsed['client_email'])) $missing[] = 'client_email';
            if (empty($parsed['private_key'])) $missing[] = 'private_key';

            if (!empty($missing)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Service Account JSON is missing required fields: ' . implode(', ', $missing),
                ], 422);
            }

            $fs = app(\App\Services\FirebaseService::class);
            if (!$fs->validatePrivateKeyFormat($parsed['private_key'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'The file contains an invalid RSA private key format that cannot be parsed by OpenSSL.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'project_id' => trim((string) $parsed['project_id']),
                'client_email' => trim((string) $parsed['client_email']),
                'has_private_key' => true,
                'message' => "Service Account JSON valid for project: '{$parsed['project_id']}'.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed parsing file: ' . $e->getMessage(),
            ], 422);
        }
    }
}
