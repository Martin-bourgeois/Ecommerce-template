<?php

declare(strict_types=1);

namespace App\Domains\Promotion\Services;

use App\Domains\Promotion\DTOs\DiscountResult;
use App\Models\User;

class PriceCalculator
{
    public function __construct(
        private readonly PromotionService $promotionService,
    ) {}

    /**
     * Calculate final price with promotions and loyalty
     */
    public function calculateOrderPrice(
        array $cartItems,
        int $subtotalCents,
        int $shippingCents,
        User $user,
        ?string $couponCode = null,
    ): array {
        // Calculate discount
        $discountResult = $this->promotionService->calculateDiscount(
            $cartItems,
            $subtotalCents,
            $user,
            $couponCode,
        );

        // Apply discount
        $discountedSubtotal = max(0, $subtotalCents - $discountResult->discountCents);

        // Check if free shipping promotion is active
        $isFreeShipping = false;
        foreach ($discountResult->promotions as $promotion) {
            if ($promotion->type->value === 'free_shipping') {
                $isFreeShipping = true;
                break;
            }
        }

        $finalShipping = $isFreeShipping ? 0 : $shippingCents;

        // Calculate tax on discounted subtotal (as per convention)
        $taxCents = $this->calculateTax($discountedSubtotal);

        // Calculate total
        $totalCents = $discountedSubtotal + $finalShipping + $taxCents;

        return [
            'subtotal_cents' => $subtotalCents,
            'discount_cents' => $discountResult->discountCents,
            'discounted_subtotal_cents' => $discountedSubtotal,
            'shipping_cents' => $finalShipping,
            'tax_cents' => $taxCents,
            'total_cents' => $totalCents,
            'discount_result' => $discountResult,
        ];
    }

    /**
     * Calculate tax (simplified - can be extended)
     */
    private function calculateTax(int $subtotalCents): int
    {
        // 20% VAT (configurable)
        $taxRate = 0.20;
        return (int) ($subtotalCents * $taxRate);
    }
}
