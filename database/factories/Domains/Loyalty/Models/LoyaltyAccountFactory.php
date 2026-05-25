<?php

namespace Database\Factories\Domains\Loyalty\Models;

use App\Domains\Loyalty\Models\LoyaltyAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyAccount>
 */
class LoyaltyAccountFactory extends Factory
{
    protected $model = LoyaltyAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'points_balance' => fake()->numberBetween(0, 10000),
            'tier' => fake()->randomElement(['bronze', 'silver', 'gold', 'platinum']),
            'member_since' => now(),
        ];
    }
}
