<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Catalog\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
            'description' => $this->faker->optional()->sentence,
            'parent_id' => null,
            'is_active' => true,
            'seo_title' => $this->faker->optional()->sentence,
            'seo_description' => $this->faker->optional()->sentence,
            'seo_keywords' => $this->faker->optional()->words(5, true),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withParent(Category $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }
}
