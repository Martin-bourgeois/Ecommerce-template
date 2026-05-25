<?php

declare(strict_types=1);

namespace Database\Factories;

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

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'title' => $this->faker->sentence(4),
            'comment' => $this->faker->paragraph(3),
            'is_approved' => $this->faker->boolean(80),
            'verified_purchase' => true,
            'helpful_count' => $this->faker->numberBetween(0, 50),
        ];
    }

    public function approved(): static
    {
        return $this->state(['is_approved' => true]);
    }

    public function pending(): static
    {
        return $this->state(['is_approved' => false]);
    }

    public function withHighRating(): static
    {
        return $this->state(['rating' => $this->faker->numberBetween(4, 5)]);
    }

    public function withLowRating(): static
    {
        return $this->state(['rating' => $this->faker->numberBetween(1, 3)]);
    }
}
