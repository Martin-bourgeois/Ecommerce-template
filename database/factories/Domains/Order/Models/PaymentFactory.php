<?php

namespace Database\Factories\Domains\Order\Models;

use App\Domains\Order\Models\Payment;
use App\Domains\Order\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => fake()->randomElement(['paypal', 'stripe', 'card']),
            'status' => fake()->randomElement(['pending', 'completed', 'failed']),
            'amount_cents' => fake()->numberBetween(999, 999999),
            'currency' => 'CAD',
            'transaction_id' => fake()->uuid(),
            'raw_response' => [],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
