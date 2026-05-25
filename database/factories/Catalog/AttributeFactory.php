<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Catalog\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attribute>
 */
class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['select', 'multiselect', 'text', 'color']),
            'is_filterable' => $this->faker->boolean(80),
        ];
    }
}
