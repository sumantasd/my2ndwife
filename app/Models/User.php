<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_ORDER_MANAGER = 'order_manager';
    public const ROLE_KITCHEN_STAFF = 'kitchen_staff';
    public const ROLE_DELIVERY_MANAGER = 'delivery_manager';
    public const ROLE_DELIVERY_PERSON = 'delivery_person';
    public const ROLE_CUSTOMER = 'customer';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'avatar',
        'vehicle_type',
        'vehicle_number',
        'is_active',
        'is_online',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_online' => 'boolean',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function assignedDeliveries(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_person_id');
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function activeDeviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class)->where('is_active', true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'order_manager', 'kitchen_staff', 'delivery_manager']);
    }

    public function isDeliveryBoy(): bool
    {
        return $this->role === 'delivery_person';
    }

    public function hasRole(string|array $roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }

        return $this->role === $roles;
    }

    /**
     * Extract 10-digit Indian mobile number digits from any format:
     * e.g. "+91 98300 99887" -> "9830099887"
     *      "+919830099887"   -> "9830099887"
     *      "09830099887"     -> "9830099887"
     *      "9830099887"      -> "9830099887"
     */
    public static function extractIndianDigits(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return strlen($digits) === 10 ? $digits : null;
    }

    /**
     * Standardize Indian phone number to "+91 XXXXXXXXXX"
     */
    public static function normalizePhoneNumber(?string $phone): ?string
    {
        $digits = self::extractIndianDigits($phone);
        if (!$digits) {
            return $phone ? trim($phone) : null;
        }

        return '+91 ' . $digits;
    }

    /**
     * Find user by phone number using exact normalized matching across all standard phone formats
     * (Handles: "+91 98765 43210", "+919876543210", "9876543210", "09876543210", etc.)
     */
    public static function findByPhone(?string $phone): ?self
    {
        $digits = self::extractIndianDigits($phone);
        if (!$digits) {
            $trimmed = trim($phone ?? '');
            return empty($trimmed) ? null : self::where('phone', $trimmed)->first();
        }

        $variants = [
            $digits,
            '+91 ' . $digits,
            '+91' . $digits,
            '0' . $digits,
            '+91 ' . substr($digits, 0, 5) . ' ' . substr($digits, 5),
            substr($digits, 0, 5) . ' ' . substr($digits, 5),
            substr($digits, 0, 5) . '-' . substr($digits, 5),
        ];

        return self::where(function ($query) use ($variants, $digits) {
            $query->whereIn('phone', $variants)
                ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '(', '') IN (?, ?, ?)", [
                    $digits,
                    '91' . $digits,
                    '0' . $digits,
                ]);
        })->first();
    }

    /**
     * Find user by email or phone
     */
    public static function findByEmailOrPhone(?string $identifier): ?self
    {
        if (empty($identifier)) {
            return null;
        }

        $identifier = trim($identifier);

        // If identifier contains @, treat as email
        if (str_contains($identifier, '@')) {
            return self::where('email', strtolower($identifier))->first();
        }

        // Otherwise, search by phone
        $userByPhone = self::findByPhone($identifier);
        if ($userByPhone) {
            return $userByPhone;
        }

        // Fallback: check if email equals identifier
        return self::where('email', strtolower($identifier))->first();
    }
}
