<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\Services;

use App\Domains\Loyalty\Models\LoyaltyAccount;
use App\Domains\Loyalty\Models\LoyaltyTransaction;
use App\Domains\Loyalty\Repositories\LoyaltyAccountRepository;
use App\Domains\Loyalty\Repositories\LoyaltyTransactionRepository;
use App\Domains\Loyalty\Enums\LoyaltyTier;
use App\Domains\Loyalty\Enums\TransactionType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    public function __construct(
        private LoyaltyAccountRepository $accountRepository,
        private LoyaltyTransactionRepository $transactionRepository,
    ) {}

    /**
     * Award points to user for purchase
     */
    public function awardPointsForPurchase(User $user, float $orderAmount, ?int $orderId = null): LoyaltyTransaction
    {
        $account = $this->accountRepository->getOrCreateForUser($user);
        $pointsToAward = (int) floor($orderAmount);

        $transaction = $this->transactionRepository->record(
            $account,
            TransactionType::EARNED,
            $pointsToAward,
            "Purchase: {$orderAmount}€",
            $orderId
        );

        // Update account balance and tier
        $newTotalEarned = $account->total_earned + $pointsToAward;
        $account->update([
            'points_balance' => $account->points_balance + $pointsToAward,
            'total_earned' => $newTotalEarned,
            'current_tier' => LoyaltyTier::getTierForPoints($newTotalEarned),
        ]);

        return $transaction;
    }

    /**
     * Spend points from loyalty account
     */
    public function spendPoints(User $user, int $pointsToSpend, string $reason = 'Purchase'): LoyaltyTransaction
    {
        $account = $this->accountRepository->getOrCreateForUser($user);

        if ($account->points_balance < $pointsToSpend) {
            throw new \InvalidArgumentException('Insufficient loyalty points');
        }

        $transaction = $this->transactionRepository->record(
            $account,
            TransactionType::SPENT,
            $pointsToSpend,
            $reason
        );

        $account->update([
            'points_balance' => $account->points_balance - $pointsToSpend,
        ]);

        return $transaction;
    }

    /**
     * Add bonus points (e.g., for referrals, promotions)
     */
    public function addBonusPoints(User $user, int $points, string $reason = 'Bonus'): LoyaltyTransaction
    {
        $account = $this->accountRepository->getOrCreateForUser($user);

        $transaction = $this->transactionRepository->record(
            $account,
            TransactionType::BONUS,
            $points,
            $reason
        );

        $newTotalEarned = $account->total_earned + $points;
        $account->update([
            'points_balance' => $account->points_balance + $points,
            'total_earned' => $newTotalEarned,
            'current_tier' => LoyaltyTier::getTierForPoints($newTotalEarned),
        ]);

        return $transaction;
    }

    /**
     * Refund points (e.g., for order cancellation)
     */
    public function refundPoints(User $user, int $points, string $reason = 'Refund'): LoyaltyTransaction
    {
        $account = $this->accountRepository->getOrCreateForUser($user);

        $transaction = $this->transactionRepository->record(
            $account,
            TransactionType::REFUNDED,
            $points,
            $reason
        );

        $account->update([
            'points_balance' => $account->points_balance + $points,
        ]);

        return $transaction;
    }

    /**
     * Expire old points
     */
    public function expireOldPoints(int $daysOld = 365): int
    {
        $expiryDate = now()->subDays($daysOld);
        $expiredCount = 0;

        $accounts = LoyaltyAccount::whereHas('transactions', function ($q) use ($expiryDate) {
            $q->where('type', TransactionType::EARNED)
                ->where('created_at', '<', $expiryDate);
        })->get();

        foreach ($accounts as $account) {
            $oldTransactions = $account->transactions()
                ->where('type', TransactionType::EARNED)
                ->where('created_at', '<', $expiryDate)
                ->get();

            foreach ($oldTransactions as $transaction) {
                $pointsToExpire = abs($transaction->points);

                if ($account->points_balance >= $pointsToExpire) {
                    $this->transactionRepository->record(
                        $account,
                        TransactionType::EXPIRED,
                        $pointsToExpire,
                        'Points expiration: ' . $transaction->description
                    );

                    $account->update([
                        'points_balance' => $account->points_balance - $pointsToExpire,
                    ]);

                    $expiredCount++;
                }
            }
        }

        return $expiredCount;
    }

    /**
     * Update tier and notify user if upgraded
     */
    public function updateTier(User $user): ?string
    {
        $account = $this->accountRepository->getForUser($user);
        if (!$account) {
            return null;
        }

        $newTier = LoyaltyTier::getTierForPoints($account->total_earned);

        if ($newTier !== $account->current_tier) {
            $account->update(['current_tier' => $newTier]);
            return $newTier->value;
        }

        return null;
    }

    /**
     * Get loyalty summary for user
     */
    public function getSummary(User $user): array
    {
        $account = $this->accountRepository->getOrCreateForUser($user);

        return [
            'balance' => $account->points_balance,
            'total_earned' => $account->total_earned,
            'current_tier' => $account->current_tier,
            'tier_name' => $account->current_tier->label(),
            'discount_percent' => $account->getDiscountPercent(),
            'points_to_next_tier' => $account->getPointsToNextTier(),
            'points_in_euros' => $account->getPointsInEuros($account->points_balance),
        ];
    }
}
