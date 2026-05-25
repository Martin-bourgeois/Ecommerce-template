<?php

namespace Tests\Feature\Loyalty;

use App\Domains\Loyalty\Models\LoyaltyAccount;
use App\Domains\Loyalty\Models\LoyaltyTransaction;
use App\Domains\Loyalty\Services\LoyaltyService;
use App\Models\User;
use Tests\TestCase;

class LoyaltyServiceTest extends TestCase
{
    private LoyaltyService $loyaltyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loyaltyService = app(LoyaltyService::class);
    }

    public function test_award_points_for_purchase(): void
    {
        $user = User::factory()->create();

        $transaction = $this->loyaltyService->awardPointsForPurchase($user, 100.0);

        $this->assertNotNull($transaction);
        $account = LoyaltyAccount::where('user_id', $user->id)->first();
        $this->assertEquals(100, $account->points_balance);
        $this->assertEquals(100, $account->total_earned);
    }

    public function test_spend_points(): void
    {
        $user = User::factory()->create();
        $this->loyaltyService->awardPointsForPurchase($user, 100.0);

        $transaction = $this->loyaltyService->spendPoints($user, 50);

        $this->assertEquals(-50, $transaction->points);
        $account = LoyaltyAccount::where('user_id', $user->id)->first();
        $this->assertEquals(50, $account->points_balance);
    }

    public function test_spend_points_insufficient_balance(): void
    {
        $user = User::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->loyaltyService->spendPoints($user, 100);
    }

    public function test_add_bonus_points(): void
    {
        $user = User::factory()->create();

        $transaction = $this->loyaltyService->addBonusPoints($user, 50, 'Referral bonus');

        $this->assertEquals(50, $transaction->points);
        $account = LoyaltyAccount::where('user_id', $user->id)->first();
        $this->assertEquals(50, $account->points_balance);
        $this->assertEquals(50, $account->total_earned);
    }

    public function test_refund_points(): void
    {
        $user = User::factory()->create();
        $this->loyaltyService->awardPointsForPurchase($user, 100.0);
        $this->loyaltyService->spendPoints($user, 50);

        $transaction = $this->loyaltyService->refundPoints($user, 50, 'Order cancellation');

        $this->assertEquals(50, $transaction->points);
        $account = LoyaltyAccount::where('user_id', $user->id)->first();
        $this->assertEquals(100, $account->points_balance);
    }

    public function test_get_loyalty_summary(): void
    {
        $user = User::factory()->create();
        $this->loyaltyService->awardPointsForPurchase($user, 500.0);

        $summary = $this->loyaltyService->getSummary($user);

        $this->assertEquals(500, $summary['balance']);
        $this->assertEquals(500, $summary['total_earned']);
        $this->assertEquals('silver', $summary['current_tier']->value);
        $this->assertEquals(5, $summary['discount_percent']);
    }

    public function test_tier_progression(): void
    {
        $user = User::factory()->create();

        // Bronze tier (0 points)
        $this->loyaltyService->awardPointsForPurchase($user, 100.0);
        $account = LoyaltyAccount::where('user_id', $user->id)->first();
        $this->assertEquals('bronze', $account->current_tier->value);

        // Silver tier (500+ points)
        $this->loyaltyService->awardPointsForPurchase($user, 400.0);
        $account->refresh();
        $this->assertEquals('silver', $account->current_tier->value);

        // Gold tier (2000+ points) - should be 1500 based on enum
        $this->loyaltyService->awardPointsForPurchase($user, 1100.0);
        $account->refresh();
        $this->assertEquals('gold', $account->current_tier->value);
    }

    public function test_update_tier(): void
    {
        $user = User::factory()->create();
        $this->loyaltyService->awardPointsForPurchase($user, 600.0);

        $newTier = $this->loyaltyService->updateTier($user);

        $this->assertEquals('silver', $newTier);
    }

    public function test_transaction_history(): void
    {
        $user = User::factory()->create();
        $this->loyaltyService->awardPointsForPurchase($user, 100.0);
        $this->loyaltyService->addBonusPoints($user, 50, 'Bonus');

        $account = LoyaltyAccount::where('user_id', $user->id)->first();
        $transactions = $account->transactions()->get();

        $this->assertCount(2, $transactions);
    }
}
