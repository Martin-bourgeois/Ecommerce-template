<?php

declare(strict_types=1);

namespace App\Domains\Promotion\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $table = 'coupons';

    protected $fillable = [
        'promotion_id',
        'code',
        'usage_limit',
        'usage_count',
        'per_customer_limit',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Get normalized code (uppercase, trimmed)
     */
    public static function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    /**
     * Check if coupon is valid (dates & active)
     */
    public function isValid(): bool
    {
        $now = Carbon::now();

        if ($this->valid_from && $now->isBefore($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->isAfter($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if global usage limit reached
     */
    public function isUsageLimitReached(): bool
    {
        if ($this->usage_limit === null) {
            return false;
        }

        return $this->usage_count >= $this->usage_limit;
    }

    /**
     * Check if per-customer limit reached for user
     */
    public function isPerCustomerLimitReached(int $userId): bool
    {
        if ($this->per_customer_limit === null) {
            return false;
        }

        $usageCount = PromotionUsage::where('coupon_id', $this->id)
            ->where('user_id', $userId)
            ->count();

        return $usageCount >= $this->per_customer_limit;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
