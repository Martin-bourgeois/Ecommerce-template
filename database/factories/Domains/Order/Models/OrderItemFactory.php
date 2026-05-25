<?php

namespace Database\Factories\Domains\Order\Models;

use App\Domains\Order\Models\OrderItem;
use App\Domains\Order\Models\Order;
use App\Domains\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory();
        $quantity = fake()->numberBetween(1, 5);
        $price = fake()->numberBetween(999, 99999);

        return [
            'order_id' => Order::factory(),
            'product_id' => $product,
            'sku' => fake()->unique()->bothify('SKU-####'),
            'name' => fake()->sentence(3),
            'qty' => $quantity,
            'price_cents' => $price,
            'total_cents' => $price * $quantity,
            'options' => [],
        ];
    }
}
