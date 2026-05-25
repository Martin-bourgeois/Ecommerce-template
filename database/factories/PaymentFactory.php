<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Order\Enums\PaymentMethod;
use App\Domains\Order\Enums\PaymentStatus;
use App\Domains\Order\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Order\Models\Payment>
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
        $orderId = fake()->randomNumber(5);

        return [
            'payable_id' => $orderId,
            'payable_type' => 'App\Domains\Order\Models\Order',
            'method' => PaymentMethod::PAYPAL->value,
            'status' => PaymentStatus::PENDING->value,
            'amount_cents' => fake()->numberBetween(1000, 100000),
            'currency' => 'EUR',
            'reference' => Payment::generateReference($orderId),
            'metadata' => [
                'paypal_email' => 'payment@example.com',
                'expires_at' => now()->addHours(48)->toIso8601String(),
            ],
            'confirmed_by' => null,
            'paid_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function pending(): self
    {
        return $this->state([
            'status' => PaymentStatus::PENDING->value,
        ]);
    }

    public function completed(): self
    {
        return $this->state([
            'status' => PaymentStatus::COMPLETED->value,
            'paid_at' => now(),
        ]);
    }

    public function failed(): self
    {
        return $this->state([
            'status' => PaymentStatus::FAILED->value,
        ]);
    }

    public function cancelled(): self
    {
        return $this->state([
            'status' => PaymentStatus::CANCELLED->value,
            'cancelled_at' => now(),
        ]);
    }

    public function refunded(): self
    {
        return $this->state([
            'status' => PaymentStatus::REFUNDED->value,
        ]);
    }
}
