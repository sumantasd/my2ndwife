<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory;

    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PENDING = 'pending';

    protected $fillable = [
        'user_id',
        'role',
        'event_type',
        'title',
        'body',
        'data',
        'device_token',
        'status',
        'error_message',
        'fcm_message_id',
        'retry_count',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'sent_at' => 'datetime',
            'retry_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SENT => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::STATUS_FAILED => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-amber-100 text-amber-800 border-amber-200',
        };
    }
}
