<?php

declare(strict_types=1);

namespace App\Domains\Promotion\Models;

use App\Domains\Promotion\Enums\PromotionTarget;
use App\Domains\Promotion\Enums\PromotionType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    protected $table = 'promotions';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'target',
        'value',
        'conditions',
        'is_stackable',
        'is_active',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_count',
        'priority',
    ];

    protected $casts = [
        'type' => PromotionType::class,
        'target' => PromotionTarget::class,
        'value' => 'decimal:2',
        'conditions' => 'array',
        'is_stackable' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    /**
     * Check if promotion is currently valid (dates & active)
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->starts_at && $now->isBefore($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->isAfter($this->ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if usage limit reached
     */
    public function isUsageLimitReached(): bool
    {
        if ($this->usage_limit === null) {
            return false;
        }

        return $this->usage_count >= $this->usage_limit;
    }

    /**
     * Check if condition is met for user/cart
     */
    public function meetsCondition(array $data): bool
    {
        $conditions = $this->conditions ?? [];

        // Check min_amount
        if (isset($conditions['min_amount']) && $data['cart_total'] < $conditions['min_amount']) {
            return false;
        }

        // Check min_qty
        if (isset($conditions['min_qty']) && $data['cart_qty'] < $conditions['min_qty']) {
            return false;
        }

        // Check customer_group
        if (isset($conditions['customer_group']) && $data['user_group'] !== $conditions['customer_group']) {
            return false;
        }

        // Check first_order
        if (isset($conditions['first_order']) && $conditions['first_order'] === true) {
            $hasOrders = User::find($data['user_id'])
                ?->orders()
                ->where('status', '!=', 'pending_payment')
                ->exists();

            if ($hasOrders) {
                return false;
            }
        }

        return true;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
