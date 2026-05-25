<?php

namespace Database\Factories\Domains\Promotion\Models;

use App\Domains\Promotion\Models\Coupon;
use App\Domains\Promotion\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promotion_id' => Promotion::factory(),
            'code' => fake()->unique()->bothify('COUPON-#####'),
            'usage_limit' => fake()->numberBetween(10, 100),
            'usage_count' => 0,
            'expires_at' => now()->addDays(30),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
