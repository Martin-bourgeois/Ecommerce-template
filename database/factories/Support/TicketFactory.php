<?php

declare(strict_types=1);

namespace Database\Factories\Support;

use App\Domains\Support\Models\Ticket;
use App\Domains\Support\Models\TicketMessage;
use App\Models\User;
use App\Domains\Order\Models\Order;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'assigned_to' => null,
            'order_id' => Order::factory(),
            'category' => $this->faker->randomElement(TicketCategory::cases()),
            'priority' => $this->faker->randomElement(TicketPriority::cases()),
            'status' => TicketStatus::OPEN,
            'subject' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
        ];
    }

    public function open(): static
    {
        return $this->state(['status' => TicketStatus::OPEN]);
    }

    public function inProgress(): static
    {
        return $this->state(['status' => TicketStatus::IN_PROGRESS]);
    }

    public function waitingCustomer(): static
    {
        return $this->state(['status' => TicketStatus::WAITING_CUSTOMER]);
    }

    public function resolved(): static
    {
        return $this->state(['status' => TicketStatus::RESOLVED]);
    }

    public function closed(): static
    {
        return $this->state(['status' => TicketStatus::CLOSED]);
    }

    public function critical(): static
    {
        return $this->state(['priority' => TicketPriority::CRITICAL]);
    }

    public function high(): static
    {
        return $this->state(['priority' => TicketPriority::HIGH]);
    }

    public function withAssignee(): static
    {
        return $this->state(['assigned_to' => User::factory()]);
    }
}
