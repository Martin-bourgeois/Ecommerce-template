<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => $this->faker->productName(),
            'short_description' => $this->faker->sentence,
            'description' => $this->faker->paragraphs(3, true),
            'type' => ProductType::SIMPLE,
            'status' => ProductStatus::ACTIVE,
            'price' => $this->faker->numberBetween(1000, 500000), // In cents
            'cost' => $this->faker->numberBetween(500, 250000),
            'weight' => $this->faker->optional()->numberBetween(100, 5000),
            'manufacturer' => $this->faker->optional()->company,
            'brand' => $this->faker->optional()->company,
            'is_featured' => $this->faker->boolean(10),
        ];
    }

    public function configurable(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProductType::CONFIGURABLE,
            'price' => null,
            'cost' => null,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::DRAFT,
        ]);
    }

    public function discontinued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::DISCONTINUED,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
