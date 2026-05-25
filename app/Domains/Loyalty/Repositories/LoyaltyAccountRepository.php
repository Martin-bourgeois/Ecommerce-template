<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\Repositories;

use App\Domains\Loyalty\Models\LoyaltyAccount;
use App\Models\User;

class LoyaltyAccountRepository
{
    /**
     * Get loyalty account for user, creating if it doesn't exist
     */
    public function getOrCreateForUser(User $user): LoyaltyAccount
    {
        return LoyaltyAccount::firstOrCreate(
            ['user_id' => $user->id],
            [
                'points_balance' => 0,
                'total_earned' => 0,
                'current_tier' => 'bronze',
            ]
        );
    }

    /**
     * Get loyalty account for user
     */
    public function getForUser(User $user): ?LoyaltyAccount
    {
        return LoyaltyAccount::where('user_id', $user->id)->first();
    }

    /**
     * Get all accounts with balance over threshold
     */
    public function getByMinimumBalance(int $minimumBalance)
    {
        return LoyaltyAccount::where('points_balance', '>=', $minimumBalance)->get();
    }

    /**
     * Get all accounts by tier
     */
    public function getByTier(string $tier)
    {
        return LoyaltyAccount::where('current_tier', $tier)->get();
    }

    /**
     * Get top accounts by total earned
     */
    public function getTopEarners(int $limit = 10)
    {
        return LoyaltyAccount::orderBy('total_earned', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Bulk update tier for accounts
     */
    public function updateTiers(array $accountIds, string $newTier): void
    {
        LoyaltyAccount::whereIn('id', $accountIds)
            ->update(['current_tier' => $newTier]);
    }
}
