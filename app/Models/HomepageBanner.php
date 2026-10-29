<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageBanner extends Model
{
    use HasFactory;

    public const SLOT_HERO = 'hero';
    public const SLOT_OFFER_1 = 'offer_1';
    public const SLOT_OFFER_2 = 'offer_2';

    protected $fillable = [
        'slot',
        'name',
        'title',
        'subtitle',
        'badge_text',
        'button_text',
        'button_url',
        'secondary_text',
        'image_path',
        'alt_text',
        'mobile_image_path',
        'bg_gradient',
        'text_color',
        'display_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeHero(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('slot', self::SLOT_HERO)
              ->orWhereNull('slot')
              ->orWhere('slot', '');
        });
    }

    public function scopeOffer1(Builder $query): Builder
    {
        return $query->where('slot', self::SLOT_OFFER_1);
    }

    public function scopeOffer2(Builder $query): Builder
    {
        return $query->where('slot', self::SLOT_OFFER_2);
    }

    public function scopeActive(Builder $query): Builder
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('display_order')
            ->orderBy('id');
    }

    public static function getHeroBanners()
    {
        return static::hero()->active()->get();
    }

    public static function getOffer1(): ?self
    {
        return static::offer1()->first();
    }

    public static function getOffer2(): ?self
    {
        return static::offer2()->first();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->title;
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image_path) {
            return asset('images/defaults/banner-placeholder.jpg');
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        // 1. Direct path in public folder (e.g. images/banners/banner-biryani.svg or storage/banners/xyz.jpg)
        if (file_exists(public_path($this->image_path))) {
            return asset($this->image_path);
        }

        // 2. Storage relative path (e.g. banners/xyz.jpg)
        if (file_exists(public_path('storage/' . $this->image_path))) {
            return asset('storage/' . $this->image_path);
        }

        // 3. Storage disk check
        $cleanStoragePath = ltrim(str_replace('storage/', '', $this->image_path), '/');
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanStoragePath)) {
            return asset('storage/' . $cleanStoragePath);
        }

        return asset('images/defaults/banner-placeholder.jpg');
    }

    public function getMobileImageUrlAttribute(): ?string
    {
        if (!$this->mobile_image_path) {
            return null;
        }

        if (str_starts_with($this->mobile_image_path, 'http://') || str_starts_with($this->mobile_image_path, 'https://')) {
            return $this->mobile_image_path;
        }

        if (file_exists(public_path($this->mobile_image_path))) {
            return asset($this->mobile_image_path);
        }

        if (file_exists(public_path('storage/' . $this->mobile_image_path))) {
            return asset('storage/' . $this->mobile_image_path);
        }

        $cleanStoragePath = ltrim(str_replace('storage/', '', $this->mobile_image_path), '/');
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanStoragePath)) {
            return asset('storage/' . $cleanStoragePath);
        }

        return null;
    }
}
