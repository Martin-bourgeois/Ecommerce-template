<?php

namespace Database\Factories\Domains\Checkout\Models;

use App\Domains\Checkout\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['shipping', 'billing']),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'company' => fake()->company(),
            'street_address' => fake()->streetAddress(),
            'apartment' => fake()->secondaryAddress(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'CA',
            'phone' => fake()->phoneNumber(),
            'is_default' => false,
        ];
    }
}
