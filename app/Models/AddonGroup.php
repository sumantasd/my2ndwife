<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AddonGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_selection',
        'max_selection',
        'is_required',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_selection' => 'integer',
            'max_selection' => 'integer',
            'is_required' => 'boolean',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function addons(): HasMany
    {
        return $this->hasMany(Addon::class)->orderBy('display_order');
    }

    public function activeAddons(): HasMany
    {
        return $this->hasMany(Addon::class)->where('is_active', true)->orderBy('display_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_addon_groups');
    }
}
