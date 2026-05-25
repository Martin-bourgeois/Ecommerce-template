<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\Repositories;

use App\Domains\Loyalty\Models\LoyaltyAccount;
use App\Domains\Loyalty\Models\LoyaltyTransaction;
use App\Domains\Loyalty\Enums\TransactionType;

class LoyaltyTransactionRepository
{
    /**
     * Record a loyalty transaction
     */
    public function record(
        LoyaltyAccount $account,
        TransactionType $type,
        int $points,
        string $description = '',
        ?int $orderId = null
    ): LoyaltyTransaction {
        return LoyaltyTransaction::create([
            'account_id' => $account->id,
            'type' => $type,
            'points' => $type->isDebit() ? -abs($points) : abs($points),
            'description' => $description,
            'order_id' => $orderId,
            'created_at' => now(),
        ]);
    }

    /**
     * Get recent transactions for account
     */
    public function getRecentForAccount(LoyaltyAccount $account, int $limit = 25)
    {
        return $account->transactions()
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get transactions by type for account
     */
    public function getByTypeForAccount(LoyaltyAccount $account, TransactionType $type)
    {
        return $account->transactions()
            ->where('type', $type)
            ->latest('created_at')
            ->get();
    }

    /**
     * Get transactions between dates
     */
    public function getForAccountBetweenDates(LoyaltyAccount $account, $startDate, $endDate)
    {
        return $account->transactions()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest('created_at')
            ->get();
    }

    /**
     * Get total points earned by account
     */
    public function getTotalPointsEarned(LoyaltyAccount $account): int
    {
        return (int) $account->transactions()
            ->where('type', TransactionType::EARNED)
            ->sum('points');
    }

    /**
     * Get total points spent by account
     */
    public function getTotalPointsSpent(LoyaltyAccount $account): int
    {
        return (int) abs($account->transactions()
            ->where('type', TransactionType::SPENT)
            ->sum('points'));
    }

    /**
     * Get transaction for order
     */
    public function getForOrder(?int $orderId)
    {
        if (!$orderId) {
            return null;
        }

        return LoyaltyTransaction::where('order_id', $orderId)->first();
    }
}
