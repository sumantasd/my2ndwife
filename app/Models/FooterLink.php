<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FooterLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'section',
        'title',
        'url',
        'page_id',
        'icon',
        'open_in_new_tab',
        'is_enabled',
        'display_order',
    ];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
        'is_enabled' => 'boolean',
        'display_order' => 'integer',
        'page_id' => 'integer',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    public function scopeSection(Builder $query, string $section): Builder
    {
        return $query->where('section', $section);
    }

    public function getFinalUrlAttribute(): string
    {
        if ($this->page_id && $this->relationLoaded('page') && $this->page) {
            return $this->page->public_url;
        }

        if ($this->page_id && ! $this->relationLoaded('page')) {
            $page = $this->page;
            if ($page) {
                return $page->public_url;
            }
        }

        if (! empty($this->url)) {
            if (str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://') || str_starts_with($this->url, 'mailto:') || str_starts_with($this->url, 'tel:')) {
                return $this->url;
            }

            if (str_starts_with($this->url, '/')) {
                return url($this->url);
            }

            return url('/' . ltrim($this->url, '/'));
        }

        return '#';
    }

    /**
     * Pre-populate default footer links if empty so existing functionality is preserved.
     */
    public static function ensureDefaults(): void
    {
        if (self::count() > 0) {
            return;
        }

        $pagesBySlug = Page::all()->keyBy('slug');

        $defaultQuickLinks = [
            ['title' => 'Home Page', 'url' => '/', 'icon' => 'home', 'display_order' => 1],
            ['title' => 'Our Full Menu', 'url' => '/menu', 'icon' => 'utensils-crossed', 'display_order' => 2],
            ['title' => 'Promotional Offers', 'url' => '/offers', 'icon' => 'tag', 'display_order' => 3],
            ['title' => 'Search Dishes', 'url' => '/search', 'icon' => 'search', 'display_order' => 4],
            ['title' => 'Order Tracking', 'url' => '/account/orders', 'icon' => 'truck', 'display_order' => 5],
            ['title' => 'About Us', 'url' => '/about', 'slug' => 'about', 'icon' => 'info', 'display_order' => 6],
        ];

        foreach ($defaultQuickLinks as $link) {
            $pageId = null;
            if (isset($link['slug']) && isset($pagesBySlug[$link['slug']])) {
                $pageId = $pagesBySlug[$link['slug']]->id;
            }

            self::create([
                'section' => 'quick_links',
                'title' => $link['title'],
                'url' => $link['url'],
                'page_id' => $pageId,
                'icon' => $link['icon'] ?? null,
                'open_in_new_tab' => false,
                'is_enabled' => true,
                'display_order' => $link['display_order'],
            ]);
        }

        $defaultSupportLinks = [
            ['title' => 'Help Center', 'url' => '/page/help-center', 'slug' => 'faq', 'icon' => 'help-circle', 'display_order' => 1],
            ['title' => 'FAQ', 'url' => '/faq', 'slug' => 'faq', 'icon' => 'file-question', 'display_order' => 2],
            ['title' => 'Terms & Conditions', 'url' => '/terms', 'slug' => 'terms', 'icon' => 'file-text', 'display_order' => 3],
            ['title' => 'Privacy Policy', 'url' => '/privacy', 'slug' => 'privacy', 'icon' => 'shield-check', 'display_order' => 4],
            ['title' => 'Refund & Cancellation', 'url' => '/refund-policy', 'slug' => 'refund-policy', 'icon' => 'rotate-ccw', 'display_order' => 5],
            ['title' => 'Contact Us', 'url' => '/contact', 'slug' => 'contact', 'icon' => 'phone-call', 'display_order' => 6],
        ];

        foreach ($defaultSupportLinks as $link) {
            $pageId = null;
            if (isset($link['slug']) && isset($pagesBySlug[$link['slug']])) {
                $pageId = $pagesBySlug[$link['slug']]->id;
            }

            self::create([
                'section' => 'support_links',
                'title' => $link['title'],
                'url' => $link['url'],
                'page_id' => $pageId,
                'icon' => $link['icon'] ?? null,
                'open_in_new_tab' => false,
                'is_enabled' => true,
                'display_order' => $link['display_order'],
            ]);
        }

        $defaultPolicyLinks = [
            ['title' => 'About Us', 'url' => '/about', 'slug' => 'about', 'display_order' => 1],
            ['title' => 'Privacy Policy', 'url' => '/privacy', 'slug' => 'privacy', 'display_order' => 2],
            ['title' => 'Terms & Conditions', 'url' => '/terms', 'slug' => 'terms', 'display_order' => 3],
            ['title' => 'Refund Policy', 'url' => '/refund-policy', 'slug' => 'refund-policy', 'display_order' => 4],
            ['title' => 'Contact Us', 'url' => '/contact', 'slug' => 'contact', 'display_order' => 5],
        ];

        foreach ($defaultPolicyLinks as $link) {
            $pageId = null;
            if (isset($link['slug']) && isset($pagesBySlug[$link['slug']])) {
                $pageId = $pagesBySlug[$link['slug']]->id;
            }

            self::create([
                'section' => 'policy_links',
                'title' => $link['title'],
                'url' => $link['url'],
                'page_id' => $pageId,
                'icon' => null,
                'open_in_new_tab' => false,
                'is_enabled' => true,
                'display_order' => $link['display_order'],
            ]);
        }
    }
}
