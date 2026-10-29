<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PLACED = 'placed';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY = 'ready';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_DELIVERY = 'delivery';
    public const TYPE_PICKUP = 'pickup';

    protected $fillable = [
        'order_number',
        'tracking_token',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'order_type',
        'order_schedule',
        'scheduled_at',
        'delivery_address_id',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_distance_km',
        'delivery_address_text',
        'delivery_instructions',
        'order_status',
        'cancellation_reason',
        'internal_notes',
        'special_instructions',
        'subtotal',
        'discount_amount',
        'packaging_charge',
        'delivery_charge',
        'tax_amount',
        'total_amount',
        'coupon_id',
        'coupon_code',
        'delivery_person_id',
        'delivered_at',
        'delivery_proof_notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'delivered_at' => 'datetime',
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
            'delivery_distance_km' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'packaging_charge' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'M2W-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            }
            if (empty($order->tracking_token)) {
                $order->tracking_token = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'asc');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function deliveryPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function isPaid(): bool
    {
        return $this->payment && $this->payment->status === 'completed';
    }

    public function isTakeaway(): bool
    {
        return $this->order_type === 'pickup';
    }

    public function isDelivery(): bool
    {
        return $this->order_type === 'delivery';
    }

    public function isPendingPayment(): bool
    {
        return $this->order_status === self::STATUS_PENDING_PAYMENT;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $current = $this->order_status;

        // Terminal statuses cannot transition further
        if ($current === self::STATUS_DELIVERED || $current === self::STATUS_CANCELLED) {
            return false;
        }

        if ($this->isTakeaway()) {
            $validTransitions = [
                self::STATUS_PENDING_PAYMENT => [self::STATUS_PLACED, self::STATUS_CANCELLED],
                self::STATUS_PLACED => [self::STATUS_ACCEPTED, self::STATUS_CANCELLED],
                self::STATUS_ACCEPTED => [self::STATUS_PREPARING, self::STATUS_CANCELLED],
                self::STATUS_PREPARING => [self::STATUS_READY, self::STATUS_CANCELLED],
                self::STATUS_READY => [self::STATUS_DELIVERED, self::STATUS_CANCELLED],
            ];
        } else {
            $validTransitions = [
                self::STATUS_PENDING_PAYMENT => [self::STATUS_PLACED, self::STATUS_CANCELLED],
                self::STATUS_PLACED => [self::STATUS_ACCEPTED, self::STATUS_CANCELLED],
                self::STATUS_ACCEPTED => [self::STATUS_PREPARING, self::STATUS_CANCELLED],
                self::STATUS_PREPARING => [self::STATUS_READY, self::STATUS_CANCELLED],
                self::STATUS_READY => [self::STATUS_OUT_FOR_DELIVERY, self::STATUS_CANCELLED],
                self::STATUS_OUT_FOR_DELIVERY => [self::STATUS_DELIVERED, self::STATUS_CANCELLED],
            ];
        }

        return in_array($newStatus, $validTransitions[$current] ?? []);
    }

    public function getNextAvailableStatuses(): array
    {
        if ($this->order_status === self::STATUS_DELIVERED || $this->order_status === self::STATUS_CANCELLED) {
            return [];
        }

        if ($this->isTakeaway()) {
            return match ($this->order_status) {
                self::STATUS_PLACED => [self::STATUS_ACCEPTED],
                self::STATUS_ACCEPTED => [self::STATUS_PREPARING],
                self::STATUS_PREPARING => [self::STATUS_READY],
                self::STATUS_READY => [self::STATUS_DELIVERED],
                default => [],
            };
        }

        return match ($this->order_status) {
            self::STATUS_PLACED => [self::STATUS_ACCEPTED],
            self::STATUS_ACCEPTED => [self::STATUS_PREPARING],
            self::STATUS_PREPARING => [self::STATUS_READY],
            self::STATUS_READY => [self::STATUS_OUT_FOR_DELIVERY],
            self::STATUS_OUT_FOR_DELIVERY => [self::STATUS_DELIVERED],
            default => [],
        };
    }

    public function getActionLabelForStatus(string $status): string
    {
        if ($this->isTakeaway()) {
            return match ($status) {
                self::STATUS_ACCEPTED => 'Accept Order',
                self::STATUS_PREPARING => 'Mark Preparing',
                self::STATUS_READY => 'Mark Ready for Pickup',
                self::STATUS_DELIVERED => 'Mark as Picked Up / Completed',
                self::STATUS_CANCELLED => 'Cancel Order',
                default => ucfirst(str_replace('_', ' ', $status)),
            };
        }

        return match ($status) {
            self::STATUS_ACCEPTED => 'Accept Order',
            self::STATUS_PREPARING => 'Mark Preparing',
            self::STATUS_READY => 'Mark Ready for Dispatch',
            self::STATUS_OUT_FOR_DELIVERY => 'Dispatch Out for Delivery',
            self::STATUS_DELIVERED => 'Confirm Delivered',
            self::STATUS_CANCELLED => 'Cancel Order',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    public function getActionColorForStatus(string $status): string
    {
        return match ($status) {
            self::STATUS_ACCEPTED => 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm',
            self::STATUS_PREPARING => 'bg-purple-600 hover:bg-purple-700 text-white shadow-sm',
            self::STATUS_READY => 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm',
            self::STATUS_OUT_FOR_DELIVERY => 'bg-[#F50070] hover:bg-[#D80063] text-white shadow-sm',
            self::STATUS_DELIVERED => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm',
            self::STATUS_CANCELLED => 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm',
            default => 'bg-gray-800 hover:bg-gray-900 text-white shadow-sm',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isTakeaway()) {
            return match ($this->order_status) {
                self::STATUS_PENDING_PAYMENT => 'Pending Payment',
                self::STATUS_PLACED => 'Order Placed',
                self::STATUS_ACCEPTED => 'Order Accepted',
                self::STATUS_PREPARING => 'Preparing Food',
                self::STATUS_READY => 'Ready for Pickup',
                self::STATUS_DELIVERED => 'Picked Up / Completed',
                self::STATUS_CANCELLED => 'Cancelled',
                default => ucfirst(str_replace('_', ' ', $this->order_status)),
            };
        }

        return match ($this->order_status) {
            self::STATUS_PENDING_PAYMENT => 'Pending Payment',
            self::STATUS_PLACED => 'Order Placed',
            self::STATUS_ACCEPTED => 'Order Accepted',
            self::STATUS_PREPARING => 'Preparing Food',
            self::STATUS_READY => 'Ready for Dispatch',
            self::STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->order_status)),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->order_status) {
            self::STATUS_PENDING_PAYMENT => 'bg-amber-50 text-amber-800 border-amber-300',
            self::STATUS_PLACED => 'bg-amber-100 text-amber-800 border-amber-200',
            self::STATUS_ACCEPTED => 'bg-blue-100 text-blue-800 border-blue-200',
            self::STATUS_PREPARING => 'bg-purple-100 text-purple-800 border-purple-200',
            self::STATUS_READY => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            self::STATUS_OUT_FOR_DELIVERY => 'bg-pink-100 text-pink-800 border-pink-200',
            self::STATUS_DELIVERED => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::STATUS_CANCELLED => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getProgressStepAttribute(): int
    {
        if ($this->isTakeaway()) {
            return match ($this->order_status) {
                self::STATUS_PENDING_PAYMENT => 1,
                self::STATUS_PLACED => 1,
                self::STATUS_ACCEPTED => 2,
                self::STATUS_PREPARING => 3,
                self::STATUS_READY => 5, // Food ready, waiting for counter pickup
                self::STATUS_DELIVERED => 6, // Picked up / Completed
                self::STATUS_CANCELLED => 0,
                default => 1,
            };
        }

        return match ($this->order_status) {
            self::STATUS_PENDING_PAYMENT => 1,
            self::STATUS_PLACED => 1,
            self::STATUS_ACCEPTED => 2,
            self::STATUS_PREPARING => 3,
            self::STATUS_READY => 4,
            self::STATUS_OUT_FOR_DELIVERY => 5,
            self::STATUS_DELIVERED => 6,
            self::STATUS_CANCELLED => 0,
            default => 1,
        };
    }

    public function getDeliveryEarningsAttribute(): float
    {
        $commission = (float) RestaurantSetting::get('delivery_boy_commission_amount', 35.00);
        return round($commission, 2);
    }
}
