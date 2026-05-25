<?php

namespace Database\Factories\Domains\Pwa\Models;

use App\Domains\Pwa\Models\PushSubscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PushSubscription>
 */
class PushSubscriptionFactory extends Factory
{
    protected $model = PushSubscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'endpoint' => fake()->url() . '/push/' . fake()->uuid(),
            'auth' => fake()->sha256(),
            'p256dh' => fake()->sha256(),
            'data' => [],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
