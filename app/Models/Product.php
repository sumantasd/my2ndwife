<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'sku',
        'regular_price',
        'discount_price',
        'is_veg',
        'is_featured',
        'is_popular',
        'is_best_seller',
        'prep_time_minutes',
        'is_available',
        'display_order',
        'main_image',
    ];

    protected function casts(): array
    {
        return [
            'regular_price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'is_veg' => 'boolean',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_available' => 'boolean',
            'prep_time_minutes' => 'integer',
            'display_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = 'M2W-' . strtoupper(Str::random(6));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('display_order');
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class)->orderBy('display_order');
    }

    public function activeVariations(): HasMany
    {
        return $this->hasMany(ProductVariation::class)->where('is_active', true)->orderBy('display_order');
    }

    public function addonGroups(): BelongsToMany
    {
        return $this->belongsToMany(AddonGroup::class, 'product_addon_groups')
            ->where('is_active', true)
            ->with('addons')
            ->orderBy('display_order');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->where('is_popular', true);
    }

    public function scopeBestSeller(Builder $query): Builder
    {
        return $query->where('is_best_seller', true);
    }

    public function scopeVeg(Builder $query): Builder
    {
        return $query->where('is_veg', true);
    }

    public function scopeNonVeg(Builder $query): Builder
    {
        return $query->where('is_veg', false);
    }

    public function getCurrentPriceAttribute(): float
    {
        return (float) ($this->discount_price && $this->discount_price > 0 && $this->discount_price < $this->regular_price
            ? $this->discount_price
            : $this->regular_price);
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->discount_price && $this->discount_price > 0 && $this->discount_price < $this->regular_price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (!$this->has_discount || $this->regular_price <= 0) {
            return 0;
        }

        return (int) round((($this->regular_price - $this->discount_price) / $this->regular_price) * 100);
    }

    public function getMainImageUrlAttribute(): string
    {
        if ($this->main_image && file_exists(public_path('storage/' . $this->main_image))) {
            return asset('storage/' . $this->main_image);
        }
        if ($this->main_image && str_starts_with($this->main_image, 'http')) {
            return $this->main_image;
        }
        if ($this->main_image && file_exists(public_path($this->main_image))) {
            return asset($this->main_image);
        }
        return asset('images/defaults/food-placeholder.jpg');
    }
}
