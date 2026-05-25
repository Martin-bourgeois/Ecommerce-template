<?php

namespace Database\Factories\Domains\Catalog\Models;

use App\Domains\Catalog\Models\Review;
use App\Domains\Catalog\Models\Product;
use App\Domains\Order\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(),
            'comment' => fake()->paragraph(),
            'is_approved' => fake()->boolean(70),
            'verified_purchase' => true,
            'helpful_count' => fake()->numberBetween(0, 50),
        ];
    }
}
