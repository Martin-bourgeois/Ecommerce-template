<?php

declare(strict_types=1);

namespace Database\Factories\Support;

use App\Domains\Support\Models\Ticket;
use App\Domains\Support\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketMessage>
 */
class TicketMessageFactory extends Factory
{
    protected $model = TicketMessage::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'content' => $this->faker->paragraphs(2, true),
            'is_internal' => false,
        ];
    }

    public function internal(): static
    {
        return $this->state(['is_internal' => true]);
    }

    public function fromClient(): static
    {
        return $this->afterCreating(function (TicketMessage $message) {
            $message->update(['user_id' => $message->ticket->user_id]);
        });
    }

    public function fromStaff(): static
    {
        return $this->state(['user_id' => User::factory()]);
    }
}
