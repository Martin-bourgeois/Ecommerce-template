<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderStatusHistory;
use App\Models\User;
use Tests\TestCase;

class OrderStatusHistoryTest extends TestCase
{
    public function test_status_history_records_transition(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create();

        $order->recordStatusChange(OrderStatus::PROCESSING, $user, 'Payment received');

        $history = $order->statusHistories()->first();

        $this->assertNotNull($history);
        $this->assertEquals('pending_payment', $history->from_status);
        $this->assertEquals('processing', $history->to_status);
        $this->assertEquals($user->id, $history->user_id);
        $this->assertEquals('Payment received', $history->reason);
    }

    public function test_status_history_without_user(): void
    {
        $order = Order::factory()->pending()->create();

        $order->recordStatusChange(OrderStatus::PROCESSING, reason: 'Automatic transition');

        $history = $order->statusHistories()->first();

        $this->assertNull($history->user_id);
        $this->assertEquals('Automatic transition', $history->reason);
    }

    public function test_status_histories_ordered_newest_first(): void
    {
        $order = Order::factory()->pending()->create();

        $order->recordStatusChange(OrderStatus::PROCESSING);
        usleep(100);
        $order->recordStatusChange(OrderStatus::SHIPPED);
        usleep(100);
        $order->recordStatusChange(OrderStatus::DELIVERED);

        $histories = $order->statusHistories()->get();

        $this->assertEquals('delivered', $histories[0]->to_status);
        $this->assertEquals('shipped', $histories[1]->to_status);
        $this->assertEquals('processing', $histories[2]->to_status);
    }

    public function test_get_description_with_reason(): void
    {
        $order = Order::factory()->pending()->create();
        $order->recordStatusChange(OrderStatus::PROCESSING, reason: 'Payment confirmed');

        $history = $order->statusHistories()->first();
        $description = $history->getDescription();

        $this->assertStringContainsString('payment_confirmed', $description);
        $this->assertStringContainsString('Payment confirmed', $description);
    }

    public function test_get_description_without_reason(): void
    {
        $order = Order::factory()->pending()->create();
        $order->recordStatusChange(OrderStatus::PROCESSING);

        $history = $order->statusHistories()->first();
        $description = $history->getDescription();

        $this->assertStringNotContainsString('()', $description);
    }
}
