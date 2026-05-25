<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Catalog\Models\ProductAttributeValue;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Attribute;
use App\Domains\Catalog\Models\AttributeOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductAttributeValue>
 */
class ProductAttributeValueFactory extends Factory
{
    protected $model = ProductAttributeValue::class;

    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'attribute_id' => Attribute::factory(),
            'attribute_option_id' => AttributeOption::factory(),
        ];
    }
}
