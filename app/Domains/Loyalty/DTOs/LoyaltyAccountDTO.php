<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\DTOs;

use App\Domains\Loyalty\Enums\LoyaltyTier;

readonly class LoyaltyAccountDTO
{
    public function __construct(
        public int $id,
        public int $userId,
        public int $pointsBalance,
        public int $totalEarned,
        public LoyaltyTier $currentTier,
        public int $discountPercent,
        public float $pointsInEuros,
        public int $pointsToNextTier,
    ) {}

    public static function fromModel(\App\Domains\Loyalty\Models\LoyaltyAccount $account): self
    {
        return new self(
            id: $account->id,
            userId: $account->user_id,
            pointsBalance: $account->points_balance,
            totalEarned: $account->total_earned,
            currentTier: $account->current_tier,
            discountPercent: $account->getDiscountPercent(),
            pointsInEuros: $account->getPointsInEuros($account->points_balance),
            pointsToNextTier: $account->getPointsToNextTier(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'pointsBalance' => $this->pointsBalance,
            'totalEarned' => $this->totalEarned,
            'currentTier' => $this->currentTier->label(),
            'discountPercent' => $this->discountPercent,
            'pointsInEuros' => round($this->pointsInEuros, 2),
            'pointsToNextTier' => $this->pointsToNextTier,
        ];
    }
}
