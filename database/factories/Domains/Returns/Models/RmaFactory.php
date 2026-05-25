<?php

declare(strict_types=1);

namespace Database\Factories\Domains\Returns;

use App\Domains\Returns\Models\Rma;
use App\Enums\RmaStatus;
use App\Enums\ReturnReason;
use App\Models\User;
use App\Domains\Order\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class RmaFactory extends Factory
{
    protected $model = Rma::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'rma_number' => 'RMA-' . now()->year . '-' . str_pad((string) $this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'status' => RmaStatus::REQUESTED,
            'reason' => $this->faker->randomElement(ReturnReason::cases()),
            'customer_notes' => $this->faker->optional()->paragraph(),
            'admin_notes' => null,
            'approved_by' => null,
            'refund_amount' => null,
            'refunded_at' => null,
        ];
    }

    public function requested(): static
    {
        return $this->state([
            'status' => RmaStatus::REQUESTED,
            'approved_by' => null,
            'refund_amount' => null,
            'refunded_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => RmaStatus::APPROVED,
            'approved_by' => User::factory(),
        ]);
    }

    public function received(): static
    {
        return $this->approved()->state([
            'status' => RmaStatus::RECEIVED,
        ]);
    }

    public function inspected(): static
    {
        return $this->received()->state([
            'status' => RmaStatus::INSPECTED,
        ]);
    }

    public function refunded(): static
    {
        return $this->inspected()->state([
            'status' => RmaStatus::REFUNDED,
            'refund_amount' => $this->faker->randomFloat(2, 10, 500),
            'refunded_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => RmaStatus::REJECTED,
        ]);
    }

    public function withItems($count = 3): static
    {
        return $this->afterCreating(function (Rma $rma) use ($count) {
            \App\Domains\Returns\Models\RmaItem::factory($count)->create([
                'rma_id' => $rma->id,
            ]);
        });
    }
}
