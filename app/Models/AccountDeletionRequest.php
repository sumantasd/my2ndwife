<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AccountDeletionRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';

    public const REASONS = [
        'no_longer_using' => 'No longer using the service',
        'privacy_concerns' => 'Privacy concerns',
        'technical_issues' => 'Technical issues',
        'duplicate_account' => 'Duplicate account',
        'other' => 'Other',
    ];

    public const VERIFICATION_METHODS = [
        'phone_call' => 'Verbal Confirmation via Phone Call to Registered Mobile',
        'sms_otp' => 'OTP Code Verified via Registered Mobile',
        'email_confirmation' => 'Written Confirmation from Registered Email',
        'order_history_check' => 'Verification of Historical Order & Address Details',
    ];

    protected $fillable = [
        'reference_number',
        'user_id',
        'full_name',
        'mobile_number',
        'email',
        'reason',
        'message',
        'status',
        'admin_notes',
        'identity_verified_at',
        'identity_verification_method',
        'reviewed_by',
        'reviewed_at',
        'completed_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'identity_verified_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Generate unique reference number in format: MWD-2026-XXXXXXXX
     */
    public static function generateReferenceNumber(): string
    {
        $year = date('Y');
        do {
            $random = strtoupper(Str::random(8));
            $reference = "MWD-{$year}-{$random}";
        } while (static::where('reference_number', $reference)->exists());

        return $reference;
    }

    public function isIdentityVerified(): bool
    {
        return !is_null($this->identity_verified_at);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? ucfirst(str_replace('_', ' ', $this->reason));
    }

    public function getVerificationMethodLabelAttribute(): ?string
    {
        if (empty($this->identity_verification_method)) {
            return null;
        }

        return self::VERIFICATION_METHODS[$this->identity_verification_method] ?? ucfirst(str_replace('_', ' ', $this->identity_verification_method));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_COMPLETED => 'Completed',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-amber-100 text-amber-800 border-amber-200',
            self::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-800 border-blue-200',
            self::STATUS_APPROVED => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::STATUS_REJECTED => 'bg-rose-100 text-rose-800 border-rose-200',
            self::STATUS_COMPLETED => 'bg-gray-100 text-gray-700 border-gray-300',
            default => 'bg-gray-100 text-gray-600 border-gray-200',
        };
    }
}
