<?php

namespace Database\Factories\Domains\Catalog\Models;

use App\Domains\Catalog\Models\AttributeOption;
use App\Domains\Catalog\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttributeOption>
 */
class AttributeOptionFactory extends Factory
{
    protected $model = AttributeOption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'name' => fake()->word(),
            'slug' => fake()->slug(),
            'position' => fake()->numberBetween(0, 50),
        ];
    }
}
