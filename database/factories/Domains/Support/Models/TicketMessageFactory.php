<?php

namespace Database\Factories\Domains\Support\Models;

use App\Domains\Support\Models\TicketMessage;
use App\Domains\Support\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketMessage>
 */
class TicketMessageFactory extends Factory
{
    protected $model = TicketMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'content' => fake()->paragraph(),
            'is_internal' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
