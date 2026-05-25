<?php

namespace Database\Factories\Domains\Catalog\Models;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->numberBetween(999, 99999); // in cents
        $cost = $this->faker->numberBetween(500, $price - 100);

        return [
            'product_id' => Product::factory(),
            'sku' => $this->faker->unique()->bothify('VARIANT-####'),
            'name' => $this->faker->sentence(2),
            'price' => $price,
            'cost' => $cost,
            'stock' => $this->faker->numberBetween(10, 1000),
            'reserved_stock' => $this->faker->numberBetween(0, 100),
            'weight' => $this->faker->numberBetween(100, 10000),
            'barcode' => $this->faker->unique()->ean13(),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}
