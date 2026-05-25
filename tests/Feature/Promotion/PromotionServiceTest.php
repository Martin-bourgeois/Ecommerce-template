<?php

declare(strict_types=1);

namespace Tests\Feature\Promotion;

use App\Domains\Promotion\Models\Coupon;
use App\Domains\Promotion\Models\Promotion;
use App\Domains\Promotion\Services\PromotionService;
use App\Models\User;
use InvalidArgumentException;
use Tests\TestCase;

class PromotionServiceTest extends TestCase
{
    private PromotionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PromotionService::class);
    }

    public function test_percentage_discount_calculated_correctly(): void
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

        $user = User::factory()->create();
        $cartItems = [['product_id' => 1, 'quantity' => 1]];

        // 100€ with 20% discount = 20€ discount
        $result = $this->service->calculateDiscount(
            $cartItems,
            10000, // 100€ in cents
            $user,
        );

        $this->assertGreaterThan(0, $result->discountCents);
        $this->assertContains($promotion->id, array_map(fn ($p) => $p->id, $result->promotions));
    }

    public function test_fixed_amount_discount_applied(): void
    {
        Promotion::create([
            'name' => 'Fixed Discount',
            'slug' => 'fixed-discount',
            'type' => 'fixed_amount',
            'target' => 'order',
            'value' => 10, // 10€
            'conditions' => [],
            'is_stackable' => true,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $cartItems = [['product_id' => 1, 'quantity' => 1]];

        $result = $this->service->calculateDiscount(
            $cartItems,
            5000, // 50€
            $user,
        );

        // Should get 10€ discount
        $this->assertGreaterThan(0, $result->discountCents);
    }

    public function test_min_amount_condition_enforced(): void
    {
        Promotion::create([
            'name' => 'Min Amount Promo',
            'slug' => 'min-amount',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 10,
            'conditions' => ['min_amount' => 100], // Requires 100€ minimum
            'is_stackable' => true,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $cartItems = [['product_id' => 1, 'quantity' => 1]];

        // Cart under 100€ - promotion should not apply
        $result = $this->service->calculateDiscount(
            $cartItems,
            5000, // 50€
            $user,
        );

        $this->assertEquals(0, $result->discountCents);
    }

    public function test_min_qty_condition_enforced(): void
    {
        Promotion::create([
            'name' => 'Min Qty Promo',
            'slug' => 'min-qty',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 10,
            'conditions' => ['min_qty' => 3], // Requires 3+ items
            'is_stackable' => true,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        // Only 1 item - promotion should not apply
        $result = $this->service->calculateDiscount(
            [['product_id' => 1, 'quantity' => 1]],
            5000,
            $user,
        );

        $this->assertEquals(0, $result->discountCents);

        // 3 items - promotion should apply
        $result = $this->service->calculateDiscount(
            [['product_id' => 1, 'quantity' => 3]],
            5000,
            $user,
        );

        $this->assertGreaterThan(0, $result->discountCents);
    }

    public function test_stackable_promotions_cumulate(): void
    {
        // Create two stackable promotions
        Promotion::create([
            'name' => 'Promo 1',
            'slug' => 'promo-1',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 10,
            'conditions' => [],
            'is_stackable' => true,
            'is_active' => true,
        ]);

        Promotion::create([
            'name' => 'Promo 2',
            'slug' => 'promo-2',
            'type' => 'fixed_amount',
            'target' => 'order',
            'value' => 5,
            'conditions' => [],
            'is_stackable' => true,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $cartItems = [['product_id' => 1, 'quantity' => 1]];

        $result = $this->service->calculateDiscount(
            $cartItems,
            10000, // 100€
            $user,
        );

        // Should have both promotions applied
        $this->assertCount(2, $result->promotions);
        $this->assertGreaterThan(0, $result->discountCents);
    }

    public function test_non_stackable_takes_best_discount(): void
    {
        // Create two non-stackable promotions with different values
        Promotion::create([
            'name' => 'Discount 5',
            'slug' => 'discount-5',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 5,
            'conditions' => [],
            'is_stackable' => false,
            'is_active' => true,
            'priority' => 1,
        ]);

        Promotion::create([
            'name' => 'Discount 15',
            'slug' => 'discount-15',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 15,
            'conditions' => [],
            'is_stackable' => false,
            'is_active' => true,
            'priority' => 2,
        ]);

        $user = User::factory()->create();
        $cartItems = [['product_id' => 1, 'quantity' => 1]];

        $result = $this->service->calculateDiscount(
            $cartItems,
            10000, // 100€
            $user,
        );

        // Should only have one promotion (the best one: 15%)
        $this->assertCount(1, $result->promotions);
        $this->assertStringContainsString('Discount 15', $result->promotions[0]->name);
    }

    public function test_discount_never_exceeds_cart_total(): void
    {
        Promotion::create([
            'name' => 'Huge Discount',
            'slug' => 'huge-discount',
            'type' => 'percentage',
            'target' => 'order',
            'value' => 200, // 200% (impossible!)
            'conditions' => [],
            'is_stackable' => true,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $cartItems = [['product_id' => 1, 'quantity' => 1]];

        $result = $this->service->calculateDiscount(
            $cartItems,
            10000, // 100€
            $user,
        );

        // Discount should not exceed total
        $this->assertLessThanOrEqual(10000, $result->discountCents);
    }
}
