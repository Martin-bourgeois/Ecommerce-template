<?php

namespace Database\Factories\Domains\Loyalty\Models;

use App\Domains\Loyalty\Models\LoyaltyTransaction;
use App\Domains\Loyalty\Models\LoyaltyAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyTransaction>
 */
class LoyaltyTransactionFactory extends Factory
{
    protected $model = LoyaltyTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'loyalty_account_id' => LoyaltyAccount::factory(),
            'type' => fake()->randomElement(['earn', 'redeem', 'adjustment']),
            'points' => fake()->numberBetween(-1000, 1000),
            'description' => fake()->sentence(),
            'reference_id' => fake()->uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
