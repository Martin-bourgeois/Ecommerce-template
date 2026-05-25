<?php

namespace Database\Factories\Domains\Promotion\Models;

use App\Domains\Promotion\Models\Promotion;
use App\Domains\Promotion\Enums\PromotionType;
use App\Domains\Promotion\Enums\PromotionTarget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(),
            'slug' => fake()->slug(),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(PromotionType::cases()),
            'target' => PromotionTarget::ORDER,
            'value' => fake()->numberBetween(100, 5000),
            'conditions' => [],
            'is_stackable' => fake()->boolean(),
            'is_active' => true,
            'starts_at' => now()->subDays(7),
            'ends_at' => now()->addDays(30),
            'usage_limit' => fake()->numberBetween(100, 1000),
            'usage_count' => 0,
            'priority' => fake()->numberBetween(1, 10),
        ];
    }
}
