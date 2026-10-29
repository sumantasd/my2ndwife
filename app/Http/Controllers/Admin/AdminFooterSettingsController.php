<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterLink;
use App\Models\Page;
use App\Models\RestaurantSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFooterSettingsController extends Controller
{
    /**
     * Display the Footer Management overview.
     */
    public function index(): View
    {
        FooterLink::ensureDefaults();

        $settings = RestaurantSetting::getAll();
        $pages = Page::ordered()->get();

        $quickLinks = FooterLink::with('page')->section('quick_links')->ordered()->get();
        $supportLinks = FooterLink::with('page')->section('support_links')->ordered()->get();
        $policyLinks = FooterLink::with('page')->section('policy_links')->ordered()->get();

        // Default official logo for preview
        $officialLogo = $settings['restaurant_logo_dark'] ?? ($settings['restaurant_logo'] ?? 'images/defaults/logo.svg');

        return view('admin.footer.index', compact(
            'settings',
            'pages',
            'quickLinks',
            'supportLinks',
            'policyLinks',
            'officialLogo'
        ));
    }

    /**
     * Update scalar settings and section toggles for the footer.
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Branding & Description
            'footer_description' => ['nullable', 'string', 'max:1000'],

            // Contact Info
            'restaurant_phone' => ['nullable', 'string', 'max:50'],
            'restaurant_email' => ['nullable', 'email', 'max:100'],
            'restaurant_address' => ['nullable', 'string', 'max:500'],
            'opening_time' => ['nullable', 'string', 'max:50'],
            'closing_time' => ['nullable', 'string', 'max:50'],

            // Social Media
            'facebook_url' => ['nullable', 'string', 'max:255'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
            'twitter_url' => ['nullable', 'string', 'max:255'],
            'youtube_url' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'string', 'max:255'],
            'restaurant_whatsapp' => ['nullable', 'string', 'max:50'],

            // Newsletter
            'footer_newsletter_heading' => ['nullable', 'string', 'max:150'],
            'footer_newsletter_description' => ['nullable', 'string', 'max:500'],
            'footer_newsletter_placeholder' => ['nullable', 'string', 'max:150'],
            'footer_newsletter_button_text' => ['nullable', 'string', 'max:100'],

            // App Download
            'footer_app_heading' => ['nullable', 'string', 'max:150'],
            'footer_app_description' => ['nullable', 'string', 'max:500'],
            'app_android_url' => ['nullable', 'string', 'max:255'],
            'app_android_button_label' => ['nullable', 'string', 'max:100'],
            'app_pwa_button_label' => ['nullable', 'string', 'max:100'],

            // Copyright & Credit
            'footer_copyright_text' => ['nullable', 'string', 'max:500'],
            'footer_dev_credit_url' => ['nullable', 'string', 'max:255'],
            'footer_dev_credit_text' => ['nullable', 'string', 'max:100'],
        ]);

        $sectionBooleans = [
            'branding' => [],
            'social' => [
                'facebook_enabled',
                'instagram_enabled',
                'twitter_enabled',
                'youtube_enabled',
                'linkedin_enabled',
                'whatsapp_enabled',
            ],
            'contact' => [],
            'apps' => [
                'footer_app_download_enabled',
                'app_android_enabled',
                'app_pwa_enabled',
            ],
            'newsletter' => [
                'footer_newsletter_enabled',
            ],
            'copyright' => [
                'footer_dev_credit_enabled',
                'footer_payment_methods_enabled',
                'footer_decorations_enabled',
            ],
            'visibility' => [
                'footer_quick_links_enabled',
                'footer_support_links_enabled',
                'footer_policy_links_enabled',
                'footer_newsletter_enabled',
                'footer_app_download_enabled',
                'footer_payment_methods_enabled',
                'footer_decorations_enabled',
            ],
        ];

        $allBooleanKeys = [
            'footer_newsletter_enabled',
            'footer_app_download_enabled',
            'app_android_enabled',
            'app_pwa_enabled',
            'footer_payment_methods_enabled',
            'footer_decorations_enabled',
            'footer_quick_links_enabled',
            'footer_support_links_enabled',
            'footer_policy_links_enabled',
            'footer_dev_credit_enabled',
            'facebook_enabled',
            'instagram_enabled',
            'twitter_enabled',
            'youtube_enabled',
            'linkedin_enabled',
            'whatsapp_enabled',
        ];

        $tab = $request->input('_tab');
        if ($tab && isset($sectionBooleans[$tab])) {
            $targetBooleans = $sectionBooleans[$tab];
        } else {
            $targetBooleans = array_values(array_filter($allBooleanKeys, fn($k) => $request->has($k)));
        }

        foreach ($targetBooleans as $key) {
            $val = $request->boolean($key);
            RestaurantSetting::set($key, $val ? '1' : '0', 'footer', 'boolean');
        }

        // Save scalar values that were present in input
        foreach ($validated as $key => $val) {
            if ($request->has($key)) {
                RestaurantSetting::set($key, $val ?? '', 'footer', 'string');
            }
        }

        return redirect()->route('admin.footer.index', ['tab' => $tab ?: 'branding'])
            ->with('success', 'Footer settings updated successfully.');
    }

    /**
     * Store a newly created footer link.
     */
    public function storeLink(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'section' => ['required', 'string', 'in:quick_links,support_links,policy_links'],
            'title' => ['required', 'string', 'max:150'],
            'link_type' => ['required', 'string', 'in:page,url'],
            'page_id' => ['nullable', 'required_if:link_type,page', 'exists:pages,id'],
            'url' => ['nullable', 'required_if:link_type,url', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'is_enabled' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $section = $validated['section'];
        $displayOrder = $validated['display_order'] ?? null;
        if ($displayOrder === null) {
            $displayOrder = (FooterLink::where('section', $section)->max('display_order') ?? 0) + 1;
        }

        $pageId = ($validated['link_type'] === 'page') ? $validated['page_id'] : null;
        $url = ($validated['link_type'] === 'url') ? $validated['url'] : null;

        $link = FooterLink::create([
            'section' => $section,
            'title' => $validated['title'],
            'page_id' => $pageId,
            'url' => $url,
            'icon' => $validated['icon'] ?? null,
            'open_in_new_tab' => $request->boolean('open_in_new_tab'),
            'is_enabled' => $request->has('is_enabled') ? $request->boolean('is_enabled') : true,
            'display_order' => $displayOrder,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Footer link created successfully.',
                'link' => $link->load('page'),
            ]);
        }

        return redirect()->route('admin.footer.index', ['tab' => $section])
            ->with('success', "Footer link '{$link->title}' added successfully.");
    }

    /**
     * Update an existing footer link.
     */
    public function updateLink(Request $request, FooterLink $link): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'link_type' => ['required', 'string', 'in:page,url'],
            'page_id' => ['nullable', 'required_if:link_type,page', 'exists:pages,id'],
            'url' => ['nullable', 'required_if:link_type,url', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'is_enabled' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $pageId = ($validated['link_type'] === 'page') ? $validated['page_id'] : null;
        $url = ($validated['link_type'] === 'url') ? $validated['url'] : null;

        $link->update([
            'title' => $validated['title'],
            'page_id' => $pageId,
            'url' => $url,
            'icon' => $validated['icon'] ?? $link->icon,
            'open_in_new_tab' => $request->boolean('open_in_new_tab'),
            'is_enabled' => $request->has('is_enabled') ? $request->boolean('is_enabled') : $link->is_enabled,
            'display_order' => $validated['display_order'] ?? $link->display_order,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Footer link updated successfully.',
                'link' => $link->fresh('page'),
            ]);
        }

        return redirect()->route('admin.footer.index', ['tab' => $link->section])
            ->with('success', "Footer link '{$link->title}' updated successfully.");
    }

    /**
     * Toggle active status of a link.
     */
    public function toggleLink(Request $request, FooterLink $link): RedirectResponse|JsonResponse
    {
        $link->is_enabled = ! $link->is_enabled;
        $link->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_enabled' => $link->is_enabled,
                'message' => "Link '{$link->title}' is now " . ($link->is_enabled ? 'active' : 'disabled') . '.',
            ]);
        }

        return redirect()->route('admin.footer.index', ['tab' => $link->section])
            ->with('success', "Link '{$link->title}' status changed to " . ($link->is_enabled ? 'enabled' : 'disabled') . '.');
    }

    /**
     * Reorder links within a section.
     */
    public function reorderLinks(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'orders' => ['required', 'array'],
            'orders.*.id' => ['required', 'integer', 'exists:footer_links,id'],
            'orders.*.display_order' => ['required', 'integer'],
        ]);

        foreach ($validated['orders'] as $orderItem) {
            FooterLink::where('id', $orderItem['id'])->update(['display_order' => $orderItem['display_order']]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Links reordered successfully.',
            ]);
        }

        return back()->with('success', 'Links reordered successfully.');
    }

    /**
     * Delete a footer link.
     */
    public function destroyLink(Request $request, FooterLink $link): RedirectResponse|JsonResponse
    {
        $title = $link->title;
        $section = $link->section;
        $link->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Link '{$title}' removed successfully.",
            ]);
        }

        return redirect()->route('admin.footer.index', ['tab' => $section])
            ->with('success', "Link '{$title}' deleted successfully.");
    }
}
