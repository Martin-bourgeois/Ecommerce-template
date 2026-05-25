<?php

declare(strict_types=1);

namespace App\Domains\Promotion\DTOs;

use App\Domains\Promotion\Models\Coupon;
use App\Domains\Promotion\Models\Promotion;

class DiscountResult
{
    /**
     * @param int $discountCents Total discount amount in cents
     * @param Promotion[] $promotions Array of applied promotions
     * @param Coupon|null $coupon Applied coupon (if any)
     * @param array<string, int> $breakdown Breakdown by promotion: ['promo_name' => cents]
     */
    public function __construct(
        public readonly int $discountCents,
        public readonly array $promotions,
        public readonly ?Coupon $coupon = null,
        public readonly array $breakdown = [],
    ) {}

    public function getTotalDiscount(): float
    {
        return $this->discountCents / 100;
    }

    public function getDiscountFormatted(): string
    {
        return number_format($this->getTotalDiscount(), 2, ',', ' ') . '€';
    }

    public function hasDiscount(): bool
    {
        return $this->discountCents > 0;
    }

    public function getPromotionNames(): array
    {
        return array_map(fn (Promotion $p) => $p->name, $this->promotions);
    }

    public function toArray(): array
    {
        return [
            'discount_cents' => $this->discountCents,
            'discount_formatted' => $this->getDiscountFormatted(),
            'promotions' => $this->getPromotionNames(),
            'coupon_code' => $this->coupon?->code,
            'breakdown' => $this->breakdown,
        ];
    }
}
