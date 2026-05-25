<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\Responses;

use App\Domains\Loyalty\DTOs\LoyaltyAccountDTO;
use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyAccountResponse extends JsonResource
{
    public function __construct(private LoyaltyAccountDTO $loyaltyAccount)
    {
        parent::__construct($loyaltyAccount);
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->loyaltyAccount->id,
            'userId' => $this->loyaltyAccount->userId,
            'pointsBalance' => $this->loyaltyAccount->pointsBalance,
            'totalEarned' => $this->loyaltyAccount->totalEarned,
            'currentTier' => [
                'name' => $this->loyaltyAccount->currentTier->label(),
                'value' => $this->loyaltyAccount->currentTier->value,
                'discountPercent' => $this->loyaltyAccount->discountPercent,
            ],
            'balance' => [
                'points' => $this->loyaltyAccount->pointsBalance,
                'euros' => round($this->loyaltyAccount->pointsInEuros, 2),
            ],
            'progression' => [
                'pointsToNextTier' => $this->loyaltyAccount->pointsToNextTier,
                'nextTierName' => $this->getNextTierName(),
            ],
        ];
    }

    private function getNextTierName(): string
    {
        $nextTier = $this->loyaltyAccount->currentTier->value === 'platinum'
            ? $this->loyaltyAccount->currentTier
            : $this->loyaltyAccount->currentTier;

        if ($this->loyaltyAccount->currentTier->value === 'bronze') {
            return 'Silver';
        } elseif ($this->loyaltyAccount->currentTier->value === 'silver') {
            return 'Gold';
        } elseif ($this->loyaltyAccount->currentTier->value === 'gold') {
            return 'Platinum';
        }

        return 'Platinum';
    }
}
