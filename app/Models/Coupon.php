<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'discount_amount',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'per_customer_limit',
        'start_date',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'per_customer_limit' => 'integer',
            'start_date' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValidFor(float $subtotal, ?int $userId = null): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'This coupon is no longer active.'];
        }

        $now = Carbon::now();
        if ($this->start_date && $now->lt($this->start_date)) {
            return ['valid' => false, 'message' => 'This coupon offer has not started yet.'];
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return ['valid' => false, 'message' => 'This coupon has expired.'];
        }

        if ($this->min_order_amount > 0 && $subtotal < $this->min_order_amount) {
            return [
                'valid' => false,
                'message' => "Minimum order amount of ₹" . number_format($this->min_order_amount, 2) . " required for this coupon."
            ];
        }

        if ($this->usage_limit !== null) {
            $totalUsage = $this->usages()->count();
            if ($totalUsage >= $this->usage_limit) {
                return ['valid' => false, 'message' => 'This coupon usage limit has been reached.'];
            }
        }

        if ($userId && $this->per_customer_limit > 0) {
            $userUsage = $this->usages()->where('user_id', $userId)->count();
            if ($userUsage >= $this->per_customer_limit) {
                return ['valid' => false, 'message' => 'You have already reached the maximum usage for this coupon.'];
            }
        }

        return ['valid' => true, 'message' => 'Coupon applied successfully!'];
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            $discount = ($subtotal * $this->discount_amount) / 100;
            if ($this->max_discount_amount !== null && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }
        } else {
            $discount = min($this->discount_amount, $subtotal);
        }

        return round($discount, 2);
    }
}
