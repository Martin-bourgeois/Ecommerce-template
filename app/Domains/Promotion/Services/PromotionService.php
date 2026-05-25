<?php

declare(strict_types=1);

namespace App\Domains\Promotion\Services;

use App\Domains\Promotion\DTOs\DiscountResult;
use App\Domains\Promotion\Enums\PromotionTarget;
use App\Domains\Promotion\Enums\PromotionType;
use App\Domains\Promotion\Models\Coupon;
use App\Domains\Promotion\Models\Promotion;
use App\Domains\Promotion\Models\PromotionUsage;
use App\Models\User;
use InvalidArgumentException;

class PromotionService
{
    /**
     * Calculate discount for cart with optional coupon
     */
    public function calculateDiscount(
        array $cartItems,
        int $cartTotalCents,
        User $user,
        ?string $couponCode = null,
    ): DiscountResult {
        $coupon = null;
        $promotions = [];
        $totalDiscount = 0;
        $breakdown = [];

        // Validate and apply coupon if provided
        if ($couponCode) {
            $coupon = $this->validateCoupon($couponCode, $user);
            if ($coupon) {
                $promotion = $coupon->promotion;

                // Check if promotion can be applied
                if ($this->canApplyPromotion($promotion, $cartItems, $cartTotalCents, $user)) {
                    $discount = $this->calculatePromotionDiscount(
                        $promotion,
                        $cartItems,
                        $cartTotalCents,
                    );

                    if ($discount > 0) {
                        $promotions[] = $promotion;
                        $totalDiscount += $discount;
                        $breakdown[$promotion->name] = $discount;
                    }
                }
            }
        } else {
            // Get applicable promotions (without coupon)
            $applicablePromos = $this->getApplicablePromotions(
                $cartItems,
                $cartTotalCents,
                $user,
            );

            // Separate stackable and non-stackable
            $stackable = [];
            $nonStackable = [];

            foreach ($applicablePromos as $promo) {
                if ($promo->is_stackable) {
                    $stackable[] = $promo;
                } else {
                    $nonStackable[] = $promo;
                }
            }

            // Apply non-stackable: take the best one (highest discount)
            if (!empty($nonStackable)) {
                $best = $this->findBestPromotion($nonStackable, $cartItems, $cartTotalCents);
                if ($best) {
                    $discount = $this->calculatePromotionDiscount(
                        $best,
                        $cartItems,
                        $cartTotalCents,
                    );
                    $promotions[] = $best;
                    $totalDiscount += $discount;
                    $breakdown[$best->name] = $discount;
                }
            }

            // Apply all stackable promotions
            foreach ($stackable as $promo) {
                // Apply with reduced total for cumulative calculation
                $currentTotal = max(0, $cartTotalCents - $totalDiscount);
                $discount = $this->calculatePromotionDiscount(
                    $promo,
                    $cartItems,
                    $currentTotal,
                );

                if ($discount > 0) {
                    $promotions[] = $promo;
                    $totalDiscount += $discount;
                    $breakdown[$promo->name] = $discount;
                }
            }
        }

        // Ensure discount never exceeds total
        $totalDiscount = min($totalDiscount, $cartTotalCents);

        return new DiscountResult(
            discountCents: $totalDiscount,
            promotions: $promotions,
            coupon: $coupon,
            breakdown: $breakdown,
        );
    }

    /**
     * Validate coupon code for user
     */
    public function validateCoupon(string $code, User $user): ?Coupon
    {
        $normalizedCode = Coupon::normalizeCode($code);

        $coupon = Coupon::where('code', $normalizedCode)->first();

        if (!$coupon) {
            throw new InvalidArgumentException("Coupon code not found: {$code}");
        }

        // Check if coupon is valid
        if (!$coupon->isValid()) {
            throw new InvalidArgumentException("Coupon code expired or not yet valid");
        }

        // Check global usage limit
        if ($coupon->isUsageLimitReached()) {
            throw new InvalidArgumentException("Coupon usage limit reached");
        }

        // Check per-customer limit
        if ($coupon->isPerCustomerLimitReached($user->id)) {
            throw new InvalidArgumentException("You have reached the usage limit for this coupon");
        }

        // Check if promotion is valid
        if (!$coupon->promotion->isValid()) {
            throw new InvalidArgumentException("Promotion is no longer valid");
        }

        return $coupon;
    }

    /**
     * Get all applicable promotions for the cart
     */
    private function getApplicablePromotions(
        array $cartItems,
        int $cartTotalCents,
        User $user,
    ): array {
        $promotions = Promotion::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();

        $applicable = [];

        foreach ($promotions as $promotion) {
            if ($this->canApplyPromotion($promotion, $cartItems, $cartTotalCents, $user)) {
                $applicable[] = $promotion;
            }
        }

        return $applicable;
    }

    /**
     * Check if promotion can be applied to cart
     */
    private function canApplyPromotion(
        Promotion $promotion,
        array $cartItems,
        int $cartTotalCents,
        User $user,
    ): bool {
        // Check if promotion is valid
        if (!$promotion->isValid()) {
            return false;
        }

        // Check usage limit
        if ($promotion->isUsageLimitReached()) {
            return false;
        }

        // Check conditions
        $data = [
            'cart_total' => $cartTotalCents / 100,
            'cart_qty' => array_sum(array_column($cartItems, 'quantity')),
            'user_id' => $user->id,
            'user_group' => $user->customer_group ?? 'standard',
        ];

        if (!$promotion->meetsCondition($data)) {
            return false;
        }

        // Check target applicability
        if ($promotion->target === PromotionTarget::PRODUCT) {
            // Check if any cart item matches the target product
            $productIds = collect($cartItems)->pluck('product_id')->toArray();
            $targetProductIds = $promotion->conditions['product_ids'] ?? [];

            if (empty(array_intersect($productIds, $targetProductIds))) {
                return false;
            }
        } elseif ($promotion->target === PromotionTarget::CATEGORY) {
            // Check if any cart item is in the target category
            $categoryIds = collect($cartItems)->pluck('category_id')->toArray();
            $targetCategoryIds = $promotion->conditions['category_ids'] ?? [];

            if (empty(array_intersect($categoryIds, $targetCategoryIds))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate discount amount for a promotion
     */
    private function calculatePromotionDiscount(
        Promotion $promotion,
        array $cartItems,
        int $cartTotalCents,
    ): int {
        $discount = 0;

        match ($promotion->type) {
            PromotionType::PERCENTAGE => $discount = (int) ($cartTotalCents * $promotion->value / 100),
            PromotionType::FIXED_AMOUNT => $discount = (int) ($promotion->value * 100),
            PromotionType::FREE_SHIPPING => $discount = 0, // Handled elsewhere
        };

        // Ensure discount is not negative
        return max(0, $discount);
    }

    /**
     * Find the best (highest discount) promotion from a list
     */
    private function findBestPromotion(
        array $promotions,
        array $cartItems,
        int $cartTotalCents,
    ): ?Promotion {
        $best = null;
        $bestDiscount = 0;

        foreach ($promotions as $promotion) {
            $discount = $this->calculatePromotionDiscount($promotion, $cartItems, $cartTotalCents);

            if ($discount > $bestDiscount) {
                $best = $promotion;
                $bestDiscount = $discount;
            }
        }

        return $best;
    }

    /**
     * Record promotion usage when order is created
     */
    public function recordUsage(
        int $orderId,
        int $userId,
        array $promotionIds,
        ?int $couponId = null,
    ): void {
        foreach ($promotionIds as $promotionId) {
            PromotionUsage::create([
                'promotion_id' => $promotionId,
                'coupon_id' => $couponId,
                'order_id' => $orderId,
                'user_id' => $userId,
                'used_at' => now(),
            ]);

            // Increment usage counters
            $promotion = Promotion::find($promotionId);
            $promotion?->incrementUsage();

            if ($couponId) {
                $coupon = Coupon::find($couponId);
                $coupon?->incrementUsage();
            }
        }
    }
}
