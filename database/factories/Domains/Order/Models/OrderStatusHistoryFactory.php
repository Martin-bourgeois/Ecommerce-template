<?php

namespace Database\Factories\Domains\Order\Models;

use App\Domains\Order\Models\OrderStatusHistory;
use App\Domains\Order\Models\Order;
use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderStatusHistory>
 */
class OrderStatusHistoryFactory extends Factory
{
    protected $model = OrderStatusHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'status' => fake()->randomElement(OrderStatus::cases()),
            'notes' => fake()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
