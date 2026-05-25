<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Catalog\Models\AttributeOption;
use App\Domains\Catalog\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttributeOption>
 */
class AttributeOptionFactory extends Factory
{
    protected $model = AttributeOption::class;

    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'value' => $this->faker->word(),
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
