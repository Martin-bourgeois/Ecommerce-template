<?php

namespace Tests\Feature\Loyalty;

use App\Domains\Loyalty\Services\LoyaltyService;
use App\Models\User;
use Tests\TestCase;

class LoyaltyControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_get_account(): void
    {
        $user = User::factory()->create();
        $loyaltyService = app(LoyaltyService::class);
        $loyaltyService->awardPointsForPurchase($user, 100.0);

        $response = $this->actingAs($user)->getJson('/loyalty/account');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'userId',
                'pointsBalance',
                'totalEarned',
                'currentTier',
                'balance',
                'progression',
            ],
        ]);
    }

    public function test_get_transactions(): void
    {
        $user = User::factory()->create();
        $loyaltyService = app(LoyaltyService::class);
        $loyaltyService->awardPointsForPurchase($user, 100.0);

        $response = $this->actingAs($user)->getJson('/loyalty/transactions');

        $response->assertStatus(200);
        $response->assertJsonIsArray();
    }

    public function test_spend_points(): void
    {
        $user = User::factory()->create();
        $loyaltyService = app(LoyaltyService::class);
        $loyaltyService->awardPointsForPurchase($user, 100.0);

        $response = $this->actingAs($user)->postJson('/loyalty/spend', [
            'points' => 50,
            'reason' => 'Discount',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_spend_points_insufficient(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/loyalty/spend', [
            'points' => 100,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_get_summary(): void
    {
        $user = User::factory()->create();
        $loyaltyService = app(LoyaltyService::class);
        $loyaltyService->awardPointsForPurchase($user, 500.0);

        $response = $this->actingAs($user)->getJson('/loyalty/summary');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'balance' => 500,
                'total_earned' => 500,
            ],
        ]);
    }

    public function test_get_account_unauthorized(): void
    {
        $response = $this->getJson('/loyalty/account');
        $response->assertStatus(401);
    }
}
