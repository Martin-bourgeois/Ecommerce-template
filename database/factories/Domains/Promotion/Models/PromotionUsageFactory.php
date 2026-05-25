<?php

namespace Database\Factories\Domains\Promotion\Models;

use App\Domains\Promotion\Models\PromotionUsage;
use App\Domains\Promotion\Models\Promotion;
use App\Domains\Promotion\Models\Coupon;
use App\Domains\Order\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromotionUsage>
 */
class PromotionUsageFactory extends Factory
{
    protected $model = PromotionUsage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promotion_id' => Promotion::factory(),
            'coupon_id' => Coupon::factory(),
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'discount_amount_cents' => fake()->numberBetween(100, 5000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
