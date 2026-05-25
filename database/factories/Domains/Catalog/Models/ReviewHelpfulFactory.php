<?php

namespace Database\Factories\Domains\Catalog\Models;

use App\Domains\Catalog\Models\ReviewHelpful;
use App\Domains\Catalog\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewHelpful>
 */
class ReviewHelpfulFactory extends Factory
{
    protected $model = ReviewHelpful::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'review_id' => Review::factory(),
            'user_id' => User::factory(),
            'is_helpful' => true,
        ];
    }
}
