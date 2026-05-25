<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => $this->faker->optional()->word,
            'price' => $this->faker->numberBetween(1000, 500000),
            'cost' => $this->faker->numberBetween(500, 250000),
            'stock' => $this->faker->numberBetween(0, 100),
            'reserved_stock' => 0,
            'weight' => $this->faker->optional()->numberBetween(100, 5000),
            'barcode' => $this->faker->optional()->ean13(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
            'reserved_stock' => 0,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $this->faker->numberBetween(1, 5),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
