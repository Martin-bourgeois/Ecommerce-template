<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\DTOs;

use App\Domains\Loyalty\Enums\TransactionType;
use Carbon\Carbon;

readonly class LoyaltyTransactionDTO
{
    public function __construct(
        public int $id,
        public int $accountId,
        public TransactionType $type,
        public int $points,
        public string $description,
        public ?int $orderId,
        public Carbon $createdAt,
    ) {}

    public static function fromModel(\App\Domains\Loyalty\Models\LoyaltyTransaction $transaction): self
    {
        return new self(
            id: $transaction->id,
            accountId: $transaction->account_id,
            type: $transaction->type,
            points: $transaction->points,
            description: $transaction->description,
            orderId: $transaction->order_id,
            createdAt: $transaction->created_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'accountId' => $this->accountId,
            'type' => $this->type->label(),
            'points' => $this->points,
            'description' => $this->description,
            'createdAt' => $this->createdAt->toIso8601String(),
        ];
    }

    /**
     * Get formatted display for UI
     */
    public function toDisplay(): array
    {
        return [
            'id' => $this->id,
            'sign' => $this->points > 0 ? '+' : '',
            'points' => abs($this->points),
            'type' => $this->type->label(),
            'description' => $this->description ?: $this->type->label(),
            'date' => $this->createdAt->format('d/m/Y'),
            'time' => $this->createdAt->format('H:i'),
        ];
    }
}
