<?php

declare(strict_types=1);

namespace App\Domains\Promotion\Services;

use App\Models\User;
use App\Domains\Promotion\Models\Coupon;

/**
 * Facade pour les opérations promotions depuis les composants Livewire.
 */
class PromotionFacade
{
    public function __construct(
        private PromotionService $promotionService,
    ) {}

    /**
     * Valide et applique un coupon à une commande.
     */
    public function validateAndApplyCoupon(
        User $user,
        string $couponCode,
        float $subtotal
    ): Coupon {
        $coupon = Coupon::where('code', $couponCode)
            ->where('is_active', true)
            ->firstOrFail();

        // Vérifier la validité du coupon
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw new \InvalidArgumentException('Ce coupon a expiré');
        }

        if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
            throw new \InvalidArgumentException('Ce coupon a atteint sa limite d\'utilisation');
        }

        // Vérifier la promotion associée
        $promotion = $coupon->promotion;
        if (!$promotion || !$promotion->is_active) {
            throw new \InvalidArgumentException('La promotion associée n\'est plus disponible');
        }

        // Vérifier les conditions minimales
        $cartTotalCents = (int)($subtotal * 100);
        if ($promotion->minimum_order_value && $cartTotalCents < $promotion->minimum_order_value) {
            throw new \InvalidArgumentException(
                'Panier insuffisant. Minimum: €' . number_format($promotion->minimum_order_value / 100, 2)
            );
        }

        return $coupon;
    }
}
