<?php

namespace Database\Factories\Domains\Catalog\Models;

use App\Domains\Catalog\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attribute>
 */
class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'slug' => fake()->slug(),
            'description' => fake()->sentence(),
            'input_type' => fake()->randomElement(['text', 'select', 'multiselect']),
            'is_filterable' => fake()->boolean(),
            'position' => fake()->numberBetween(0, 100),
        ];
    }
}
