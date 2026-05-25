<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Order\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => OrderStatus::PENDING_PAYMENT->value,
            'subtotal_cents' => fake()->numberBetween(1000, 100000),
            'shipping_cents' => fake()->numberBetween(500, 5000),
            'tax_cents' => fake()->numberBetween(200, 20000),
            'total_cents' => fake()->numberBetween(2000, 150000),
            'shipping_address_id' => null,
            'billing_address_id' => null,
            'notes' => null,
        ];
    }

    public function pending(): self
    {
        return $this->state([
            'status' => OrderStatus::PENDING_PAYMENT->value,
        ]);
    }

    public function processing(): self
    {
        return $this->state([
            'status' => OrderStatus::PROCESSING->value,
        ]);
    }

    public function shipped(): self
    {
        return $this->state([
            'status' => OrderStatus::SHIPPED->value,
            'shipped_at' => now(),
        ]);
    }

    public function delivered(): self
    {
        return $this->state([
            'status' => OrderStatus::DELIVERED->value,
            'delivered_at' => now(),
        ]);
    }

    public function completed(): self
    {
        return $this->state([
            'status' => OrderStatus::COMPLETED->value,
            'delivered_at' => now(),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state([
            'status' => OrderStatus::CANCELLED->value,
            'cancelled_at' => now(),
        ]);
    }
}
