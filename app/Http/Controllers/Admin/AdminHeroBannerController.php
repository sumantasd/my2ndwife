<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\HomepageBanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminHeroBannerController extends Controller
{
    public function index(): View
    {
        $heroBanners = HomepageBanner::hero()->orderBy('display_order')->orderBy('id')->get();

        $offer1 = HomepageBanner::firstOrCreate(
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
                'is_active' => true,
                'display_order' => 1,
            ]
        );

        $offer2 = HomepageBanner::firstOrCreate(
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
                'is_active' => true,
                'display_order' => 2,
            ]
        );

        return view('admin.hero-banners.index', [
            'heroBanners' => $heroBanners,
            'banners' => $heroBanners, // backward compatibility
            'offer1' => $offer1,
            'offer2' => $offer2,
        ]);
    }

    public function create(): View
    {
        $nextOrder = (HomepageBanner::hero()->max('display_order') ?? 0) + 1;

        return view('admin.hero-banners.create', [
            'nextOrder' => $nextOrder,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'title' => ['nullable', 'string', 'max:150'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'badge_text' => ['nullable', 'string', 'max:80'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'secondary_text' => ['nullable', 'string', 'max:150'],
            'bg_gradient' => ['nullable', 'string', 'max:150'],
            'text_color' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'hero_banner_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $title = $validated['title'] ?? ($validated['name'] ?? 'Homepage Promotional Banner');
        $name = $validated['name'] ?? $title;

        $buttonUrl = $validated['button_url'] ?? null;
        if (!empty($buttonUrl) && !str_starts_with($buttonUrl, '/') && !str_starts_with($buttonUrl, 'http://') && !str_starts_with($buttonUrl, 'https://')) {
            $buttonUrl = '/' . ltrim($buttonUrl, '/');
        }

        $imageFile = $request->file('hero_banner_image') ?? $request->file('image');
        $imagePath = 'images/banners/banner-biryani.svg';
        if ($imageFile) {
            $imagePath = 'storage/' . $imageFile->store('banners', 'public');
        }

        $mobileImagePath = null;
        if ($request->hasFile('mobile_image')) {
            $mobileImagePath = 'storage/' . $request->file('mobile_image')->store('banners', 'public');
        }

        $banner = HomepageBanner::create([
            'slot' => HomepageBanner::SLOT_HERO,
            'name' => $name,
            'title' => $title,
            'alt_text' => $validated['alt_text'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'badge_text' => $validated['badge_text'] ?? null,
            'button_text' => $validated['button_text'] ?? 'Order Now',
            'button_url' => $buttonUrl,
            'secondary_text' => $validated['secondary_text'] ?? 'Delivered hot & fresh in 30 mins',
            'bg_gradient' => $validated['bg_gradient'] ?? 'from-[#FF006B] via-[#E60060] to-[#1F1F39]',
            'text_color' => $validated['text_color'] ?? '#FFFFFF',
            'image_path' => $imagePath,
            'mobile_image_path' => $mobileImagePath,
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        AuditLog::record('hero_banner_created', 'HomepageBanner', $banner->id, [
            'title' => $banner->title,
            'order' => $banner->display_order,
        ]);

        return redirect()->route('admin.hero-banners.index')
            ->with('success', "Homepage hero banner '{$banner->title}' created successfully.")
            ->with('active_tab', 'hero');
    }

    public function edit(HomepageBanner $heroBanner): View
    {
        return view('admin.hero-banners.edit', [
            'banner' => $heroBanner,
        ]);
    }

    public function update(Request $request, HomepageBanner $heroBanner): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'title' => ['nullable', 'string', 'max:150'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'badge_text' => ['nullable', 'string', 'max:80'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'secondary_text' => ['nullable', 'string', 'max:150'],
            'bg_gradient' => ['nullable', 'string', 'max:150'],
            'text_color' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $title = $validated['title'] ?? ($validated['name'] ?? $heroBanner->title);
        $name = $validated['name'] ?? ($validated['title'] ?? $heroBanner->name);

        $buttonUrl = $validated['button_url'] ?? null;
        if (!empty($buttonUrl) && !str_starts_with($buttonUrl, '/') && !str_starts_with($buttonUrl, 'http://') && !str_starts_with($buttonUrl, 'https://')) {
            $buttonUrl = '/' . ltrim($buttonUrl, '/');
        }

        // Desktop Image Replacement
        if ($request->hasFile('image')) {
            $newPath = 'storage/' . $request->file('image')->store('banners', 'public');

            if (!empty($heroBanner->image_path) && str_starts_with($heroBanner->image_path, 'storage/banners/')) {
                $oldFile = str_replace('storage/', '', $heroBanner->image_path);
                $isReferenced = HomepageBanner::where('id', '!=', $heroBanner->id)
                    ->where(function ($q) use ($heroBanner) {
                        $q->where('image_path', $heroBanner->image_path)
                            ->orWhere('mobile_image_path', $heroBanner->image_path);
                    })
                    ->exists();

                if (!$isReferenced && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            $heroBanner->image_path = $newPath;
        }

        // Mobile Image Replacement
        if ($request->hasFile('mobile_image')) {
            $newMobilePath = 'storage/' . $request->file('mobile_image')->store('banners', 'public');

            if (!empty($heroBanner->mobile_image_path) && str_starts_with($heroBanner->mobile_image_path, 'storage/banners/')) {
                $oldMobileFile = str_replace('storage/', '', $heroBanner->mobile_image_path);
                $isReferenced = HomepageBanner::where('id', '!=', $heroBanner->id)
                    ->where(function ($q) use ($heroBanner) {
                        $q->where('mobile_image_path', $heroBanner->mobile_image_path)
                            ->orWhere('image_path', $heroBanner->mobile_image_path);
                    })
                    ->exists();

                if (!$isReferenced && Storage::disk('public')->exists($oldMobileFile)) {
                    Storage::disk('public')->delete($oldMobileFile);
                }
            }

            $heroBanner->mobile_image_path = $newMobilePath;
        }

        $heroBanner->name = $name;
        $heroBanner->title = $title;
        $heroBanner->alt_text = $validated['alt_text'] ?? null;
        $heroBanner->badge_text = $validated['badge_text'] ?? null;
        $heroBanner->subtitle = $validated['subtitle'] ?? null;
        $heroBanner->button_text = $validated['button_text'] ?? 'Order Now';
        $heroBanner->button_url = $buttonUrl;
        $heroBanner->secondary_text = $validated['secondary_text'] ?? $heroBanner->secondary_text;
        $heroBanner->bg_gradient = $validated['bg_gradient'] ?? $heroBanner->bg_gradient;
        $heroBanner->text_color = $validated['text_color'] ?? $heroBanner->text_color ?? '#FFFFFF';
        $heroBanner->display_order = $validated['display_order'] ?? $heroBanner->display_order;
        $heroBanner->is_active = $request->boolean('is_active', true);
        $heroBanner->starts_at = $validated['starts_at'] ?? null;
        $heroBanner->ends_at = $validated['ends_at'] ?? null;
        $heroBanner->save();

        AuditLog::record('hero_banner_updated', 'HomepageBanner', $heroBanner->id, [
            'title' => $heroBanner->title,
        ]);

        return redirect()->route('admin.hero-banners.index')
            ->with('success', "Hero banner '{$heroBanner->title}' updated successfully.");
    }

    public function toggle(HomepageBanner $heroBanner): RedirectResponse
    {
        $heroBanner->is_active = !$heroBanner->is_active;
        $heroBanner->save();

        AuditLog::record('hero_banner_toggled', 'HomepageBanner', $heroBanner->id, [
            'is_active' => $heroBanner->is_active,
        ]);

        $status = $heroBanner->is_active ? 'enabled' : 'disabled';

        return back()->with('success', "Hero banner '{$heroBanner->title}' has been {$status}.");
    }

    public function destroy(HomepageBanner $heroBanner): RedirectResponse
    {
        // Clean desktop image
        if (!empty($heroBanner->image_path) && str_starts_with($heroBanner->image_path, 'storage/banners/')) {
            $relativePath = str_replace('storage/', '', $heroBanner->image_path);
            $isReferenced = HomepageBanner::where('id', '!=', $heroBanner->id)
                ->where(function ($q) use ($heroBanner) {
                    $q->where('image_path', $heroBanner->image_path)
                        ->orWhere('mobile_image_path', $heroBanner->image_path);
                })
                ->exists();

            if (!$isReferenced && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        // Clean mobile image
        if (!empty($heroBanner->mobile_image_path) && str_starts_with($heroBanner->mobile_image_path, 'storage/banners/')) {
            $relativeMobilePath = str_replace('storage/', '', $heroBanner->mobile_image_path);
            $isReferenced = HomepageBanner::where('id', '!=', $heroBanner->id)
                ->where(function ($q) use ($heroBanner) {
                    $q->where('mobile_image_path', $heroBanner->mobile_image_path)
                        ->orWhere('image_path', $heroBanner->mobile_image_path);
                })
                ->exists();

            if (!$isReferenced && Storage::disk('public')->exists($relativeMobilePath)) {
                Storage::disk('public')->delete($relativeMobilePath);
            }
        }

        AuditLog::record('hero_banner_deleted', 'HomepageBanner', $heroBanner->id, [
            'title' => $heroBanner->title,
        ]);

        $heroBanner->delete();

        return redirect()->route('admin.hero-banners.index')
            ->with('success', 'Hero banner removed successfully.')
            ->with('active_tab', 'hero');
    }

    /*
    |--------------------------------------------------------------------------
    | Offer Banner 1 Management (Left Promo Banner)
    |--------------------------------------------------------------------------
    */
    public function updateOffer1(Request $request): RedirectResponse
    {
        $offer1 = HomepageBanner::firstOrCreate(
            ['slot' => HomepageBanner::SLOT_OFFER_1],
            ['name' => 'Offer Banner 1', 'title' => 'Offer Banner 1', 'display_order' => 1]
        );

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'badge_text' => ['nullable', 'string', 'max:80'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'bg_gradient' => ['nullable', 'string', 'max:50'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'offer_banner_1_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
        ]);

        $buttonUrl = $validated['button_url'] ?? null;
        if (!empty($buttonUrl) && !str_starts_with($buttonUrl, '/') && !str_starts_with($buttonUrl, 'http://') && !str_starts_with($buttonUrl, 'https://')) {
            $buttonUrl = '/' . ltrim($buttonUrl, '/');
        }

        // Upload new image if provided
        if ($request->hasFile('offer_banner_1_image')) {
            $newPath = 'storage/' . $request->file('offer_banner_1_image')->store('banners', 'public');

            if (!empty($offer1->image_path) && str_starts_with($offer1->image_path, 'storage/banners/')) {
                $oldFile = str_replace('storage/', '', $offer1->image_path);
                $isReferenced = HomepageBanner::where('id', '!=', $offer1->id)
                    ->where(function ($q) use ($offer1) {
                        $q->where('image_path', $offer1->image_path)
                          ->orWhere('mobile_image_path', $offer1->image_path);
                    })
                    ->exists();

                if (!$isReferenced && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            $offer1->image_path = $newPath;
        }

        $offer1->name = 'Offer Banner 1';
        $offer1->title = $validated['title'];
        $offer1->badge_text = $validated['badge_text'] ?? null;
        $offer1->subtitle = $validated['subtitle'] ?? null;
        $offer1->button_text = $validated['button_text'] ?? 'Order Now';
        $offer1->button_url = $buttonUrl;
        $offer1->bg_gradient = $validated['bg_gradient'] ?? 'mint';
        $offer1->alt_text = $validated['alt_text'] ?? null;
        $offer1->display_order = $validated['display_order'] ?? 1;
        $offer1->is_active = $request->boolean('is_active', true);
        $offer1->save();

        AuditLog::record('offer_banner_1_updated', 'HomepageBanner', $offer1->id, ['title' => $offer1->title]);

        return redirect()->route('admin.hero-banners.index')
            ->with('success', 'Offer Banner 1 updated successfully.')
            ->with('active_tab', 'offer1');
    }

    public function toggleOffer1(): RedirectResponse
    {
        $offer1 = HomepageBanner::firstOrCreate(
            ['slot' => HomepageBanner::SLOT_OFFER_1],
            ['name' => 'Offer Banner 1', 'title' => 'Offer Banner 1', 'display_order' => 1]
        );
        $offer1->is_active = !$offer1->is_active;
        $offer1->save();

        $status = $offer1->is_active ? 'enabled' : 'disabled';
        return redirect()->route('admin.hero-banners.index')
            ->with('success', "Offer Banner 1 has been {$status}.")
            ->with('active_tab', 'offer1');
    }

    public function destroyOffer1(): RedirectResponse
    {
        $offer1 = HomepageBanner::where('slot', HomepageBanner::SLOT_OFFER_1)->first();
        if ($offer1) {
            if (!empty($offer1->image_path) && str_starts_with($offer1->image_path, 'storage/banners/')) {
                $oldFile = str_replace('storage/', '', $offer1->image_path);
                $isReferenced = HomepageBanner::where('id', '!=', $offer1->id)
                    ->where('image_path', $offer1->image_path)
                    ->exists();
                if (!$isReferenced && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }
            $offer1->delete();
        }

        return redirect()->route('admin.hero-banners.index')
            ->with('success', 'Offer Banner 1 removed successfully.')
            ->with('active_tab', 'offer1');
    }

    /*
    |--------------------------------------------------------------------------
    | Offer Banner 2 Management (Right Promo Banner)
    |--------------------------------------------------------------------------
    */
    public function updateOffer2(Request $request): RedirectResponse
    {
        $offer2 = HomepageBanner::firstOrCreate(
            ['slot' => HomepageBanner::SLOT_OFFER_2],
            ['name' => 'Offer Banner 2', 'title' => 'Offer Banner 2', 'display_order' => 2]
        );

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'badge_text' => ['nullable', 'string', 'max:80'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'bg_gradient' => ['nullable', 'string', 'max:50'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'offer_banner_2_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
        ]);

        $buttonUrl = $validated['button_url'] ?? null;
        if (!empty($buttonUrl) && !str_starts_with($buttonUrl, '/') && !str_starts_with($buttonUrl, 'http://') && !str_starts_with($buttonUrl, 'https://')) {
            $buttonUrl = '/' . ltrim($buttonUrl, '/');
        }

        // Upload new image if provided
        if ($request->hasFile('offer_banner_2_image')) {
            $newPath = 'storage/' . $request->file('offer_banner_2_image')->store('banners', 'public');

            if (!empty($offer2->image_path) && str_starts_with($offer2->image_path, 'storage/banners/')) {
                $oldFile = str_replace('storage/', '', $offer2->image_path);
                $isReferenced = HomepageBanner::where('id', '!=', $offer2->id)
                    ->where(function ($q) use ($offer2) {
                        $q->where('image_path', $offer2->image_path)
                          ->orWhere('mobile_image_path', $offer2->image_path);
                    })
                    ->exists();

                if (!$isReferenced && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            $offer2->image_path = $newPath;
        }

        $offer2->name = 'Offer Banner 2';
        $offer2->title = $validated['title'];
        $offer2->badge_text = $validated['badge_text'] ?? null;
        $offer2->subtitle = $validated['subtitle'] ?? null;
        $offer2->button_text = $validated['button_text'] ?? 'Order Now';
        $offer2->button_url = $buttonUrl;
        $offer2->bg_gradient = $validated['bg_gradient'] ?? 'peach';
        $offer2->alt_text = $validated['alt_text'] ?? null;
        $offer2->display_order = $validated['display_order'] ?? 2;
        $offer2->is_active = $request->boolean('is_active', true);
        $offer2->save();

        AuditLog::record('offer_banner_2_updated', 'HomepageBanner', $offer2->id, ['title' => $offer2->title]);

        return redirect()->route('admin.hero-banners.index')
            ->with('success', 'Offer Banner 2 updated successfully.')
            ->with('active_tab', 'offer2');
    }

    public function toggleOffer2(): RedirectResponse
    {
        $offer2 = HomepageBanner::firstOrCreate(
            ['slot' => HomepageBanner::SLOT_OFFER_2],
            ['name' => 'Offer Banner 2', 'title' => 'Offer Banner 2', 'display_order' => 2]
        );
        $offer2->is_active = !$offer2->is_active;
        $offer2->save();

        $status = $offer2->is_active ? 'enabled' : 'disabled';
        return redirect()->route('admin.hero-banners.index')
            ->with('success', "Offer Banner 2 has been {$status}.")
            ->with('active_tab', 'offer2');
    }

    public function destroyOffer2(): RedirectResponse
    {
        $offer2 = HomepageBanner::where('slot', HomepageBanner::SLOT_OFFER_2)->first();
        if ($offer2) {
            if (!empty($offer2->image_path) && str_starts_with($offer2->image_path, 'storage/banners/')) {
                $oldFile = str_replace('storage/', '', $offer2->image_path);
                $isReferenced = HomepageBanner::where('id', '!=', $offer2->id)
                    ->where('image_path', $offer2->image_path)
                    ->exists();
                if (!$isReferenced && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }
            $offer2->delete();
        }

        return redirect()->route('admin.hero-banners.index')
            ->with('success', 'Offer Banner 2 removed successfully.')
            ->with('active_tab', 'offer2');
    }
}
