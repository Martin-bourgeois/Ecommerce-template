<?php

namespace Database\Factories\Domains\Checkout\Models;

use App\Domains\Checkout\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingZone>
 */
class ShippingZoneFactory extends Factory
{
    protected $model = ShippingZone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(),
            'countries' => ['CA'],
            'provinces' => ['ON', 'QC', 'BC'],
            'rate_cents' => fake()->numberBetween(100, 5000),
            'free_shipping_threshold_cents' => fake()->numberBetween(10000, 50000),
            'is_active' => true,
        ];
    }
}
