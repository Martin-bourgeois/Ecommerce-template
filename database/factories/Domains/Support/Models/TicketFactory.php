<?php

namespace Database\Factories\Domains\Support\Models;

use App\Domains\Support\Models\Ticket;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'assigned_to' => null,
            'order_id' => null,
            'category' => fake()->randomElement(TicketCategory::cases()),
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'status' => TicketStatus::OPEN,
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
