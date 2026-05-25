<?php

namespace Database\Factories\Loyalty;

use App\Domains\Loyalty\Models\LoyaltyAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Loyalty\Models\LoyaltyAccount>
 */
class LoyaltyAccountFactory extends Factory
{
    protected $model = LoyaltyAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'points_balance' => $this->faker->numberBetween(0, 500),
            'total_earned' => $this->faker->numberBetween(0, 1000),
            'current_tier' => 'bronze',
        ];
    }

    /**
     * Create a silver tier account
     */
    public function silver(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'total_earned' => $this->faker->numberBetween(500, 1499),
                'current_tier' => 'silver',
            ];
        });
    }

    /**
     * Create a gold tier account
     */
    public function gold(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'total_earned' => $this->faker->numberBetween(1500, 4999),
                'current_tier' => 'gold',
            ];
        });
    }

    /**
     * Create a platinum tier account
     */
    public function platinum(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'total_earned' => $this->faker->numberBetween(5000, 10000),
                'current_tier' => 'platinum',
            ];
        });
    }

    /**
     * Create with specific points balance
     */
    public function withBalance(int $balance): static
    {
        return $this->state([
            'points_balance' => $balance,
        ]);
    }

    /**
     * Create with specific total earned
     */
    public function withEarned(int $earned): static
    {
        return $this->state([
            'total_earned' => $earned,
        ]);
    }
}
