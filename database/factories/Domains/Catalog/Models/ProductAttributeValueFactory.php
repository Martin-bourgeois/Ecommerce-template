<?php

namespace Database\Factories\Domains\Catalog\Models;

use App\Domains\Catalog\Models\ProductAttributeValue;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\AttributeOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductAttributeValue>
 */
class ProductAttributeValueFactory extends Factory
{
    protected $model = ProductAttributeValue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'attribute_option_id' => AttributeOption::factory(),
        ];
    }
}
