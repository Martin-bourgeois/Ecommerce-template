<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\Responses;

use App\Domains\Loyalty\DTOs\LoyaltyTransactionDTO;
use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyTransactionResponse extends JsonResource
{
    public function __construct(private LoyaltyTransactionDTO $transaction)
    {
        parent::__construct($transaction);
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->transaction->id,
            'accountId' => $this->transaction->accountId,
            'type' => $this->transaction->type->label(),
            'typeValue' => $this->transaction->type->value,
            'points' => $this->transaction->points,
            'pointsAbsolute' => abs($this->transaction->points),
            'sign' => $this->transaction->points > 0 ? '+' : '-',
            'description' => $this->transaction->description ?: $this->transaction->type->label(),
            'orderId' => $this->transaction->orderId,
            'createdAt' => $this->transaction->createdAt->toIso8601String(),
            'createdAtFormatted' => $this->transaction->createdAt->format('d/m/Y H:i'),
        ];
    }
}
