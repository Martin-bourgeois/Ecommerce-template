<?php

namespace Database\Factories\Loyalty;

use App\Domains\Loyalty\Models\LoyaltyAccount;
use App\Domains\Loyalty\Models\LoyaltyTransaction;
use App\Domains\Loyalty\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Loyalty\Models\LoyaltyTransaction>
 */
class LoyaltyTransactionFactory extends Factory
{
    protected $model = LoyaltyTransaction::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(TransactionType::cases());

        return [
            'account_id' => LoyaltyAccount::factory(),
            'type' => $type,
            'points' => $type->isDebit() 
                ? -$this->faker->numberBetween(10, 100)
                : $this->faker->numberBetween(10, 100),
            'description' => $this->faker->sentence(),
            'order_id' => null,
            'created_at' => $this->faker->dateTimeBetweenStart('-1 year', '-1 second'),
        ];
    }

    /**
     * Create an earned transaction
     */
    public function earned(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TransactionType::EARNED,
                'points' => $this->faker->numberBetween(50, 200),
                'description' => "Purchase: {$this->faker->numberBetween(50, 200)}€",
            ];
        });
    }

    /**
     * Create a spent transaction
     */
    public function spent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TransactionType::SPENT,
                'points' => -$this->faker->numberBetween(20, 100),
                'description' => 'Discount redemption',
            ];
        });
    }

    /**
     * Create a bonus transaction
     */
    public function bonus(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TransactionType::BONUS,
                'points' => $this->faker->numberBetween(50, 200),
                'description' => 'Bonus points',
            ];
        });
    }

    /**
     * Create a refund transaction
     */
    public function refunded(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TransactionType::REFUNDED,
                'points' => $this->faker->numberBetween(20, 100),
                'description' => 'Order refund',
            ];
        });
    }

    /**
     * Link to specific account
     */
    public function forAccount(LoyaltyAccount $account): static
    {
        return $this->state([
            'account_id' => $account->id,
        ]);
    }

    /**
     * Link to specific order
     */
    public function forOrder(int $orderId): static
    {
        return $this->state([
            'order_id' => $orderId,
        ]);
    }
}
