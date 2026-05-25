<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderService;
use App\Models\User;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);
    }

    public function test_can_cancel_pending_payment_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->pending()->create();

        $result = $this->orderService->cancel($order, $user, 'Test cancellation');

        $result->refresh();
        $this->assertEquals(OrderStatus::CANCELLED->value, $result->status->value);
        $this->assertNotNull($result->cancelled_at);
    }

    public function test_can_cancel_processing_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->processing()->create();

        $result = $this->orderService->cancel($order, $user, 'Out of stock');

        $result->refresh();
        $this->assertEquals(OrderStatus::CANCELLED->value, $result->status->value);
    }

    public function test_cannot_cancel_shipped_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->shipped()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->orderService->cancel($order, $user);
    }

    public function test_cannot_cancel_delivered_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->delivered()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->orderService->cancel($order, $user);
    }

    public function test_cannot_cancel_already_cancelled_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->cancelled()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->orderService->cancel($order, $user);
    }

    public function test_cancellation_records_history(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->pending()->create();

        $this->orderService->cancel($order, $user, 'Customer request');

        $history = $order->statusHistories()
            ->where('to_status', OrderStatus::CANCELLED->value)
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals($user->id, $history->user_id);
        $this->assertEquals('Customer request', $history->reason);
    }

    public function test_cancellation_cancels_associated_payment(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->pending()->create();

        // Create a pending payment
        $order->payments()->create([
            'method' => 'paypal',
            'status' => 'pending',
            'amount_cents' => $order->total_cents,
            'currency' => 'EUR',
            'reference' => 'PAY-' . $order->id . '-' . time(),
        ]);

        $this->orderService->cancel($order, $user, 'Cancellation');

        $order->payment->refresh();
        $this->assertEquals('cancelled', $order->payment->status);
    }

    public function test_cancellation_of_processing_order_refunds_stock(): void
    {
        // TODO: Test stock refund when order cancelled from PROCESSING
        // This requires product relationship setup
    }
}
