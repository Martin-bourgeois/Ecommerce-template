<?php

namespace Database\Factories\Domains\Cart\Models;

use App\Domains\Cart\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_id' => fake()->uuid(),
            'total_items' => 0,
            'total_price_cents' => 0,
            'data' => [],
        ];
    }
}
