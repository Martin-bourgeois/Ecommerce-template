<?php

/**
 * Loyalty Integration Examples
 * 
 * This file demonstrates how to integrate the Loyalty module with other parts of the application.
 */

// Example 1: Award points when order is completed
class OrderCompletedListener
{
    public function __construct(
        private \App\Domains\Loyalty\Services\LoyaltyService $loyaltyService,
    ) {}

    public function handle(\App\Events\OrderCompleted $event)
    {
        // Award loyalty points for the purchase
        $transaction = $this->loyaltyService->awardPointsForPurchase(
            $event->order->user,
            $event->order->total,
            $event->order->id
        );

        // Optionally update tier if user advanced
        $newTier = $this->loyaltyService->updateTier($event->order->user);
        if ($newTier) {
            // Send tier promotion notification
            $event->order->user->notify(new TierPromotionNotification($newTier));
        }
    }
}

// Example 2: Refund points when order is cancelled
class OrderCancelledListener
{
    public function __construct(
        private \App\Domains\Loyalty\Services\LoyaltyService $loyaltyService,
        private \App\Domains\Loyalty\Repositories\LoyaltyTransactionRepository $transactionRepository,
    ) {}

    public function handle(\App\Events\OrderCancelled $event)
    {
        $transaction = $this->transactionRepository->getForOrder($event->order->id);
        
        if ($transaction) {
            $pointsToRefund = abs($transaction->points);
            $this->loyaltyService->refundPoints(
                $event->order->user,
                $pointsToRefund,
                "Refund for cancelled order #{$event->order->id}"
            );
        }
    }
}

// Example 3: Apply loyalty discount to order total
class ApplyLoyaltyDiscount
{
    public function __construct(
        private \App\Domains\Loyalty\Repositories\LoyaltyAccountRepository $accountRepository,
    ) {}

    public function apply(\App\Models\Order $order): float
    {
        $account = $this->accountRepository->getForUser($order->user);
        
        if (!$account) {
            return 0;
        }

        $discountPercent = $account->getDiscountPercent();
        $discountAmount = ($order->subtotal * $discountPercent) / 100;

        return $discountAmount;
    }
}

// Example 4: Let user redeem points in checkout
class RedeemLoyaltyPointsService
{
    public function __construct(
        private \App\Domains\Loyalty\Services\LoyaltyService $loyaltyService,
    ) {}

    public function redeemInOrder(\App\Models\User $user, int $points, \App\Models\Order $order)
    {
        try {
            $transaction = $this->loyaltyService->spendPoints(
                $user,
                $points,
                "Redeemed for order #{$order->id}"
            );

            // Calculate discount amount: 100 points = 5€
            $discountAmount = ($points / 20) * 1; // 1€ per 20 points

            return [
                'success' => true,
                'transaction' => $transaction,
                'discountAmount' => $discountAmount,
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

// Example 5: Award bonus points for referrals
class ReferralBonusService
{
    public function __construct(
        private \App\Domains\Loyalty\Services\LoyaltyService $loyaltyService,
    ) {}

    public function awardReferralBonus(\App\Models\User $referrer, \App\Models\User $newCustomer)
    {
        // Award bonus to referrer
        $this->loyaltyService->addBonusPoints(
            $referrer,
            50,
            "Referral bonus for {$newCustomer->email}"
        );

        // Award welcome bonus to new customer
        $this->loyaltyService->addBonusPoints(
            $newCustomer,
            100,
            "Welcome bonus from referral"
        );
    }
}

// Example 6: Display loyalty info in user profile API
class UserProfileController extends \App\Http\Controllers\Controller
{
    public function __construct(
        private \App\Domains\Loyalty\Repositories\LoyaltyAccountRepository $accountRepository,
    ) {}

    public function show(\Illuminate\Http\Request $request)
    {
        $account = $this->accountRepository->getOrCreateForUser($request->user());

        return response()->json([
            'user' => $request->user(),
            'loyalty' => [
                'pointsBalance' => $account->points_balance,
                'currentTier' => $account->current_tier->label(),
                'discountPercent' => $account->getDiscountPercent(),
                'nextTierPoints' => $account->getPointsToNextTier(),
            ],
        ]);
    }
}

// Example 7: Scheduled task to expire old points (optional)
class ExpireLoyaltyPoints
{
    public function __construct(
        private \App\Domains\Loyalty\Services\LoyaltyService $loyaltyService,
    ) {}

    public function handle()
    {
        $expiredCount = $this->loyaltyService->expireOldPoints(365); // Expire points older than 1 year
        
        \Log::info("Expired loyalty points for {$expiredCount} accounts");
    }
}

// Example 8: Event listener for cart updates
class UpdateLoyaltyDiscount
{
    public function __construct(
        private ApplyLoyaltyDiscount $discountService,
    ) {}

    public function handle(\App\Events\CartUpdated $event)
    {
        $discount = $this->discountService->apply($event->cart->order);
        $event->cart->update(['loyalty_discount' => $discount]);
    }
}
