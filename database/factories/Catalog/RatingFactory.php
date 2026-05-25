<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Catalog\Models\Rating;
use App\Domains\Catalog\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rating>
 */
class RatingFactory extends Factory
{
    protected $model = Rating::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'review' => $this->faker->sentence(),
            'is_verified' => $this->faker->boolean(80),
            'is_approved' => $this->faker->boolean(90),
        ];
    }
}
