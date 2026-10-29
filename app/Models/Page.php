<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'is_published',
        'display_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'display_order' => 'integer',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('title');
    }

    public function getPublicUrlAttribute(): string
    {
        return match ($this->slug) {
            'about', 'about-us' => route('page.about'),
            'terms', 'terms-and-conditions' => route('page.terms'),
            'privacy', 'privacy-policy' => route('page.privacy'),
            'refund', 'refund-policy', 'refund-and-cancellation' => route('page.refund'),
            'faq', 'frequently-asked-questions' => route('page.faq'),
            'contact', 'contact-us' => route('page.contact'),
            default => url('/page/' . $this->slug),
        };
    }

    public static function slugify(string $title): string
    {
        return Str::slug($title);
    }
}
