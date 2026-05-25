<?php

namespace Database\Factories\Domains\Cart\Models;

use App\Domains\Cart\Models\CartItemModel;
use App\Domains\Cart\Models\Cart;
use App\Domains\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItemModel>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItemModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 10),
            'price_cents' => fake()->numberBetween(999, 99999),
            'options' => [],
        ];
    }
}
