<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\Models;

use App\Domains\Loyalty\Enums\LoyaltyTier;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyAccount extends Model
{
    protected $table = 'loyalty_accounts';

    protected $fillable = [
        'user_id',
        'points_balance',
        'total_earned',
        'current_tier',
    ];

    protected $casts = [
        'current_tier' => LoyaltyTier::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'account_id');
    }

    /**
     * Get current loyalty tier
     */
    public function getTier(): LoyaltyTier
    {
        return $this->current_tier;
    }

    /**
     * Get discount percentage for current tier
     */
    public function getDiscountPercent(): int
    {
        return $this->current_tier->getDiscountPercent();
    }

    /**
     * Get points remaining to next tier
     */
    public function getPointsToNextTier(): int
    {
        $nextTier = $this->getNextTier();
        if ($nextTier === $this->current_tier) {
            return 0; // Already at highest tier
        }

        return $nextTier->getThreshold() - $this->total_earned;
    }

    /**
     * Get next tier
     */
    public function getNextTier(): LoyaltyTier
    {
        return match ($this->current_tier) {
            LoyaltyTier::BRONZE => LoyaltyTier::SILVER,
            LoyaltyTier::SILVER => LoyaltyTier::GOLD,
            LoyaltyTier::GOLD => LoyaltyTier::PLATINUM,
            LoyaltyTier::PLATINUM => LoyaltyTier::PLATINUM,
        };
    }

    /**
     * Calculate euros equivalent for points
     */
    public function getPointsInEuros(int $points): float
    {
        return $points / 20; // 100 points = 5€ → 1 point = 0.05€
    }

    /**
     * Get full transaction history
     */
    public function getTransactionHistory(int $limit = 50)
    {
        return $this->transactions()
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
