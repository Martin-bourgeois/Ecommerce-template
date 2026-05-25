<?php

declare(strict_types=1);

namespace Database\Factories\Domains\Returns;

use App\Domains\Returns\Models\RmaItem;
use App\Enums\ItemCondition;
use App\Domains\Returns\Models\Rma;
use App\Domains\Order\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class RmaItemFactory extends Factory
{
    protected $model = RmaItem::class;

    public function definition(): array
    {
        return [
            'rma_id' => Rma::factory(),
            'order_item_id' => OrderItem::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
            'condition' => null,
            'refund_amount' => $this->faker->randomFloat(2, 10, 200),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function unopened(): static
    {
        return $this->state([
            'condition' => ItemCondition::UNOPENED,
        ]);
    }

    public function opened(): static
    {
        return $this->state([
            'condition' => ItemCondition::OPENED,
        ]);
    }

    public function damaged(): static
    {
        return $this->state([
            'condition' => ItemCondition::DAMAGED,
        ]);
    }
}
