<?php

namespace Tests\Unit\Loyalty;

use App\Domains\Loyalty\Enums\LoyaltyTier;
use App\Domains\Loyalty\Enums\TransactionType;
use Tests\TestCase;

class LoyaltyEnumsTest extends TestCase
{
    public function test_loyalty_tier_thresholds(): void
    {
        $this->assertEquals(0, LoyaltyTier::BRONZE->getThreshold());
        $this->assertEquals(500, LoyaltyTier::SILVER->getThreshold());
        $this->assertEquals(1500, LoyaltyTier::GOLD->getThreshold());
        $this->assertEquals(5000, LoyaltyTier::PLATINUM->getThreshold());
    }

    public function test_loyalty_tier_discounts(): void
    {
        $this->assertEquals(0, LoyaltyTier::BRONZE->getDiscountPercent());
        $this->assertEquals(3, LoyaltyTier::SILVER->getDiscountPercent());
        $this->assertEquals(5, LoyaltyTier::GOLD->getDiscountPercent());
        $this->assertEquals(10, LoyaltyTier::PLATINUM->getDiscountPercent());
    }

    public function test_get_tier_for_points(): void
    {
        $this->assertEquals(LoyaltyTier::BRONZE, LoyaltyTier::getTierForPoints(0));
        $this->assertEquals(LoyaltyTier::BRONZE, LoyaltyTier::getTierForPoints(499));
        $this->assertEquals(LoyaltyTier::SILVER, LoyaltyTier::getTierForPoints(500));
        $this->assertEquals(LoyaltyTier::SILVER, LoyaltyTier::getTierForPoints(1499));
        $this->assertEquals(LoyaltyTier::GOLD, LoyaltyTier::getTierForPoints(1500));
        $this->assertEquals(LoyaltyTier::PLATINUM, LoyaltyTier::getTierForPoints(5000));
    }

    public function test_transaction_type_credit(): void
    {
        $this->assertTrue(TransactionType::EARNED->isCredit());
        $this->assertTrue(TransactionType::REFUNDED->isCredit());
        $this->assertTrue(TransactionType::BONUS->isCredit());
        $this->assertFalse(TransactionType::SPENT->isCredit());
        $this->assertFalse(TransactionType::EXPIRED->isCredit());
    }

    public function test_transaction_type_debit(): void
    {
        $this->assertTrue(TransactionType::SPENT->isDebit());
        $this->assertTrue(TransactionType::EXPIRED->isDebit());
        $this->assertFalse(TransactionType::EARNED->isDebit());
        $this->assertFalse(TransactionType::REFUNDED->isDebit());
        $this->assertFalse(TransactionType::BONUS->isDebit());
    }
}
