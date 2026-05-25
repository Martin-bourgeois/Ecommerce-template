<?php

declare(strict_types=1);

namespace Tests\Feature\Promotion;

use App\Domains\Promotion\Models\Coupon;
use App\Domains\Promotion\Models\Promotion;
use App\Domains\Promotion\Services\PromotionService;
use App\Models\User;
use InvalidArgumentException;
use Tests\TestCase;

class CouponValidationTest extends TestCase
{
    private PromotionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PromotionService::class);
    }

    public function test_coupon_code_case_insensitive(): void
    {
        $promotion = Promotion::create([
            'name' => 'Test Promo',
            'slug' => 'test-promo',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 20,
            'conditions' => [],
            'is_stackable' => true,
            'is_active' => true,
        ]);

        Coupon::create([
            'promotion_id' => $promotion->id,
            'code' => 'SAVE20',
            'usage_limit' => 100,
        ]);

        $user = User::factory()->create();

        // Test lowercase
        $coupon = $this->service->validateCoupon('save20', $user);
        $this->assertNotNull($coupon);

        // Test mixed case
        $coupon = $this->service->validateCoupon('SaVe20', $user);
        $this->assertNotNull($coupon);
    }

    public function test_coupon_code_whitespace_trimmed(): void
    {
        $promotion = Promotion::create([
            'name' => 'Test Promo',
            'slug' => 'test-promo',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 20,
            'conditions' => [],
            'is_stackable' => true,
            'is_active' => true,
        ]);

        Coupon::create([
            'promotion_id' => $promotion->id,
            'code' => 'SAVE20',
        ]);

        $user = User::factory()->create();

        // Test with spaces
        $coupon = $this->service->validateCoupon('  SAVE20  ', $user);
        $this->assertNotNull($coupon);
    }

    public function test_invalid_coupon_throws_exception(): void
    {
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coupon code not found');

        $this->service->validateCoupon('INVALID', $user);
    }

    public function test_expired_coupon_rejected(): void
    {
        $promotion = Promotion::create([
            'name' => 'Test Promo',
            'slug' => 'test-promo',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 20,
            'conditions' => [],
            'is_stackable' => true,
            'is_active' => true,
        ]);

        Coupon::create([
            'promotion_id' => $promotion->id,
            'code' => 'EXPIRED',
            'valid_until' => now()->subDay(),
        ]);

        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coupon code expired');

        $this->service->validateCoupon('EXPIRED', $user);
    }

    public function test_not_yet_valid_coupon_rejected(): void
    {
        $promotion = Promotion::create([
            'name' => 'Test Promo',
            'slug' => 'test-promo',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 20,
            'conditions' => [],
            'is_stackable' => true,
            'is_active' => true,
        ]);

        Coupon::create([
            'promotion_id' => $promotion->id,
            'code' => 'FUTURE',
            'valid_from' => now()->addDay(),
        ]);

        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->service->validateCoupon('FUTURE', $user);
    }

    public function test_usage_limit_enforced(): void
    {
        $promotion = Promotion::create([
            'name' => 'Test Promo',
            'slug' => 'test-promo',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 20,
            'conditions' => [],
            'is_stackable' => true,
            'is_active' => true,
        ]);

        $coupon = Coupon::create([
            'promotion_id' => $promotion->id,
            'code' => 'LIMITED',
            'usage_limit' => 1,
            'usage_count' => 1, // Already used once
        ]);

        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coupon usage limit reached');

        $this->service->validateCoupon('LIMITED', $user);
    }

    public function test_per_customer_limit_enforced(): void
    {
        $promotion = Promotion::create([
            'name' => 'Test Promo',
            'slug' => 'test-promo',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 20,
            'conditions' => [],
            'is_stackable' => true,
            'is_active' => true,
        ]);

        $coupon = Coupon::create([
            'promotion_id' => $promotion->id,
            'code' => 'LIMITED_PER_CUSTOMER',
            'per_customer_limit' => 1,
        ]);

        $user = User::factory()->create();

        // Use coupon once
        $this->service->recordUsage(1, $user->id, [$promotion->id], $coupon->id);

        // Try to use again
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have reached the usage limit');

        $this->service->validateCoupon('LIMITED_PER_CUSTOMER', $user);
    }
}
